<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-cyan-600 dark:text-cyan-300">Finance</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Invoices</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-finance-invoices"
        @filters-changed="onGlobalFiltersChanged"
      />

      <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
        <div class="flex flex-wrap items-end gap-3">
          <label class="space-y-1">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Status</span>
            <select
              v-model="filters.status"
              class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @change="onFilterChanged"
            >
              <option value="">All</option>
              <option value="draft">Draft</option>
              <option value="paid">Paid</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </label>
        </div>
      </div>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
      <article class="rounded-xl border border-cyan-200 bg-cyan-50/70 p-3 dark:border-cyan-500/30 dark:bg-cyan-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Invoice Count</p>
        <p class="mt-1 text-lg font-bold text-cyan-800 dark:text-cyan-200">{{ summary.invoice_count }}</p>
      </article>
      <article class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Total Commission Value</p>
        <p class="mt-1 text-lg font-bold text-emerald-800 dark:text-emerald-200">LKR {{ toMoney(summary.total_commission_value) }}</p>
      </article>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} invoice records</p>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="rounded-xl border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-700 hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-300"
          :disabled="exporting || loading || selectedInvoiceIds.length === 0"
          @click="exportBankDocument"
        >
          {{ exporting ? 'Exporting...' : `Export Selected (${selectedInvoiceIds.length})` }}
        </button>
        <button
          type="button"
          class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
          @click="fetchInvoices"
        >
          Refresh
        </button>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">
              <input
                type="checkbox"
                class="h-4 w-4 rounded border border-slate-300"
                :checked="allVisibleSelected"
                :indeterminate.prop="someVisibleSelected"
                @change="toggleSelectAllVisible($event.target.checked)"
              />
            </th>
            <th class="px-3 py-3 text-left">Invoice</th>
            <th class="px-3 py-3 text-left">Seller</th>
            <th class="px-3 py-3 text-left">Date</th>
            <th class="px-3 py-3 text-left">Time</th>
            <th class="px-3 py-3 text-right">Orders</th>
            <th class="px-3 py-3 text-right">Commission</th>
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading invoices...</td>
          </tr>
          <tr v-else-if="!invoices.length">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No invoices found.</td>
          </tr>

          <tr v-for="invoice in invoices" :key="invoice.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3">
              <input
                type="checkbox"
                class="h-4 w-4 rounded border border-slate-300"
                :checked="isSelected(invoice.id)"
                @change="toggleInvoiceSelection(invoice.id, $event.target.checked)"
              />
            </td>
            <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">#{{ invoice.id }}</td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ sellerName(invoice.seller) }}</td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(invoice.invoice_date) }}</td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ invoice.invoice_time || '-' }}</td>
            <td class="px-3 py-3 text-right font-semibold text-cyan-700 dark:text-cyan-300">{{ Number(invoice.orders_count || 0) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(invoice.total_commission_value) }}</td>
            <td class="px-3 py-3">
              <span :class="statusClass(invoice.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">
                {{ invoice.status }}
              </span>
            </td>
            <td class="px-3 py-3 text-right">
              <button
                v-if="String(invoice.status).toLowerCase() === 'draft'"
                type="button"
                class="rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 hover:bg-emerald-100 disabled:opacity-50"
                :disabled="markingPaidId === invoice.id"
                @click="openMarkPaidConfirm(invoice)"
              >
                {{ markingPaidId === invoice.id ? 'Updating...' : 'Mark as Paid' }}
              </button>
              <span v-else class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">No Action</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="meta.current_page <= 1 || loading"
        @click="changePage(meta.current_page - 1)"
      >
        Previous
      </button>
      <p class="text-xs text-slate-500 dark:text-slate-400">Page {{ meta.current_page }} of {{ meta.last_page }}</p>
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="meta.current_page >= meta.last_page || loading"
        @click="changePage(meta.current_page + 1)"
      >
        Next
      </button>
    </div>

    <transition name="fade">
      <div v-if="showMarkPaidModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-2 text-lg font-semibold text-slate-900 dark:text-white">Mark Invoice as Paid</h3>
          <p class="mb-2 text-sm text-slate-600 dark:text-slate-300">
            Invoice <strong>#{{ selectedInvoiceForMarkPaid?.id }}</strong> will be marked as paid.
          </p>
          <p class="mb-2 text-sm text-slate-600 dark:text-slate-300">
            Seller: <strong>{{ sellerName(selectedInvoiceForMarkPaid?.seller) }}</strong>
          </p>
          <p class="mb-5 text-sm text-slate-600 dark:text-slate-300">
            Commission: <strong>LKR {{ toMoney(selectedInvoiceForMarkPaid?.total_commission_value) }}</strong>
          </p>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
              :disabled="markingPaidId !== null"
              @click="closeMarkPaidConfirm"
            >
              Cancel
            </button>
            <button
              type="button"
              class="rounded-xl bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700 disabled:opacity-60"
              :disabled="markingPaidId !== null"
              @click="confirmMarkPaid"
            >
              Confirm Mark as Paid
            </button>
          </div>
        </div>
      </div>
    </transition>
  </section>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const exporting = ref(false)
const invoices = ref([])
const selectedInvoiceIds = ref([])
const showMarkPaidModal = ref(false)
const selectedInvoiceForMarkPaid = ref(null)
const markingPaidId = ref(null)

const filters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  seller_id: null,
  status: '',
  page: 1,
  per_page: 20,
})

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const summary = reactive({
  invoice_count: 0,
  total_commission_value: 0,
})

const allVisibleSelected = computed(() => {
  if (!invoices.value.length) return false
  return invoices.value.every((invoice) => selectedInvoiceIds.value.includes(invoice.id))
})

const someVisibleSelected = computed(() => {
  if (!invoices.value.length) return false
  const selectedCount = invoices.value.filter((invoice) => selectedInvoiceIds.value.includes(invoice.id)).length
  return selectedCount > 0 && selectedCount < invoices.value.length
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString()
}

const sellerName = (seller) => {
  if (!seller) return '-'
  const name = `${seller.first_name || ''} ${seller.last_name || ''}`.trim()
  return name || seller.email || `Seller #${seller.id}`
}

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'paid') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'cancelled') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
}

const fetchInvoices = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/finance/invoices', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        status: filters.status || undefined,
        page: filters.page,
        per_page: filters.per_page,
      },
    })

    invoices.value = Array.isArray(data?.invoices) ? data.invoices : []
    selectedInvoiceIds.value = []
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
    meta.total = Number(data?.meta?.total || 0)

    summary.invoice_count = Number(data?.summary?.invoice_count || 0)
    summary.total_commission_value = Number(data?.summary?.total_commission_value || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load invoices.')
  } finally {
    loading.value = false
  }
}

const onFilterChanged = () => {
  filters.page = 1
  fetchInvoices()
}

const onGlobalFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.seller_id = payload?.seller_id || null
  onFilterChanged()
}

const changePage = (page) => {
  filters.page = page
  fetchInvoices()
}

const isSelected = (invoiceId) => selectedInvoiceIds.value.includes(invoiceId)

const toggleInvoiceSelection = (invoiceId, checked) => {
  const next = new Set(selectedInvoiceIds.value)
  if (checked) {
    next.add(invoiceId)
  } else {
    next.delete(invoiceId)
  }
  selectedInvoiceIds.value = Array.from(next)
}

const toggleSelectAllVisible = (checked) => {
  if (!checked) {
    selectedInvoiceIds.value = []
    return
  }
  selectedInvoiceIds.value = invoices.value.map((invoice) => invoice.id)
}

const exportBankDocument = async () => {
  if (!selectedInvoiceIds.value.length) {
    toast.error('Select at least one invoice to export.')
    return
  }

  exporting.value = true
  try {
    const response = await axios.get('/api/admin/finance/invoices/export-bank-document', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        status: filters.status || undefined,
        invoice_ids: selectedInvoiceIds.value,
      },
      responseType: 'blob',
    })

    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    const fileName =
      response.headers?.['content-disposition']
        ?.split('filename=')[1]
        ?.replace(/\"/g, '') || `invoice-bank-document-${new Date().toISOString().slice(0, 10)}.csv`

    link.href = url
    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    toast.success('Bank document exported successfully.')
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to export bank document.')
  } finally {
    exporting.value = false
  }
}

const openMarkPaidConfirm = (invoice) => {
  selectedInvoiceForMarkPaid.value = invoice
  showMarkPaidModal.value = true
}

const closeMarkPaidConfirm = () => {
  showMarkPaidModal.value = false
  selectedInvoiceForMarkPaid.value = null
}

const confirmMarkPaid = async () => {
  const invoice = selectedInvoiceForMarkPaid.value
  if (!invoice?.id) return

  markingPaidId.value = invoice.id
  try {
    const { data } = await axios.post(`/api/admin/finance/invoices/${invoice.id}/mark-paid`)
    toast.success(data?.message || 'Invoice marked as paid.')
    closeMarkPaidConfirm()
    fetchInvoices()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to mark invoice as paid.')
  } finally {
    markingPaidId.value = null
  }
}
</script>
