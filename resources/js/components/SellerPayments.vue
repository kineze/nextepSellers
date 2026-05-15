<template>
  <div class="space-y-4 rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-700">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        type="button"
        class="rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide transition"
        :class="activeTab === tab.value ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'"
        @click="activeTab = tab.value"
      >
        <i class="fas mr-2" :class="tab.icon" aria-hidden="true"></i>{{ tab.label }}
      </button>
    </div>

    <SellerInvoices v-if="activeTab === 'invoices'" />

    <template v-else>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-300">Payments</p>
        <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">Seller Payments</h2>
      </div>

      <div class="grid gap-2 sm:grid-cols-2">
        <select
          v-model="filters.status"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @change="onFilterChanged"
        >
          <option value="available">Available</option>
          <option value="paid">Paid</option>
          <option value="all">All</option>
        </select>

        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="Search by order, customer, phone"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @keyup.enter="onFilterChanged"
        />
      </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      <article class="rounded-xl border border-purple-200 bg-purple-50/70 p-3 dark:border-purple-500/30 dark:bg-purple-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-purple-700 dark:text-purple-300">Pending Payments Value</p>
        <p class="mt-1 text-lg font-bold text-purple-800 dark:text-purple-200">LKR {{ toMoney(summary.pending_payments_value) }}</p>
      </article>
      <article class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Available Payments Value</p>
        <p class="mt-1 text-lg font-bold text-emerald-800 dark:text-emerald-200">LKR {{ toMoney(summary.available_payments_value) }}</p>
      </article>
      <article class="rounded-xl border border-blue-200 bg-blue-50/70 p-3 dark:border-blue-500/30 dark:bg-blue-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">Paid Payments Value</p>
        <p class="mt-1 text-lg font-bold text-blue-800 dark:text-blue-200">LKR {{ toMoney(summary.paid_payments_value) }}</p>
      </article>
     
    </div>

    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
      <p>Total {{ meta.total }} payment records</p>
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        @click="fetchPayments"
      >
        Refresh
      </button>
    </div>

    <div v-if="loading" class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
      Loading payments...
    </div>

    <div v-else-if="!payments.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
      No payments found.
    </div>

    <div v-else class="space-y-2">
      <article
        v-for="payment in payments"
        :key="payment.id"
        class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-950/50"
      >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Payment #{{ payment.id }} · Order #{{ payment.order_id }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ payment.order?.customer_name || 'Customer' }} · {{ payment.order?.phone || '-' }} · {{ payment.order?.waybill_no || '-' }}
            </p>
          </div>

          <div class="flex items-center gap-2">
            <span :class="statusClass(payment.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ payment.status }}</span>
            <p class="text-sm font-bold text-slate-900 dark:text-white">Commission: LKR {{ toMoney(payment.amount) }}</p>
          </div>
        </div>

        <div class="mt-3 grid gap-2 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-3">
          <p><span class="font-semibold">Available At:</span> {{ formatDate(payment.available_at) }}</p>
          <p><span class="font-semibold">Paid At:</span> {{ formatDate(payment.paid_at) }}</p>
          <p><span class="font-semibold">Order Payment Status:</span> {{ payment.order?.payment_status || '-' }}</p>
        </div>
      </article>
    </div>

    <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
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
    </template>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import SellerInvoices from './SellerInvoices.vue'

const toast = useToast()

const loading = ref(false)
const payments = ref([])
const activeTab = ref('invoices')

const tabs = [
  { value: 'invoices', label: 'Invoices', icon: 'fa-file-invoice-dollar' },
  { value: 'payments', label: 'Payments', icon: 'fa-wallet' },
]

const filters = reactive({
  status: 'available',
  search: '',
  page: 1,
  per_page: 10,
})

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const summary = reactive({
  total_pending_order_value: 0,
  available_payments_value: 0,
  paid_payments_value: 0,
  pending_payments_value: 0,
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'paid') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
}

const fetchPayments = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/seller/payments', {
      params: {
        status: filters.status,
        search: filters.search || undefined,
        page: filters.page,
        per_page: filters.per_page,
      },
    })

    payments.value = Array.isArray(data?.payments) ? data.payments : []
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
    meta.total = Number(data?.meta?.total || 0)

    summary.total_pending_order_value = Number(data?.summary?.total_pending_order_value || 0)
    summary.available_payments_value = Number(data?.summary?.available_payments_value || 0)
    summary.paid_payments_value = Number(data?.summary?.paid_payments_value || 0)
    summary.pending_payments_value = Number(data?.summary?.pending_payments_value || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load payments.')
  } finally {
    loading.value = false
  }
}

const onFilterChanged = () => {
  filters.page = 1
  fetchPayments()
}

const changePage = (page) => {
  filters.page = page
  fetchPayments()
}

onMounted(() => {
  fetchPayments()
})
</script>
