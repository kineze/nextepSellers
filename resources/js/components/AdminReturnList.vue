<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-amber-600 dark:text-amber-300">Inventory</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Return List</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Scan cancelled parcels, inspect every barcode, and restock accepted items.</p>
      </div>

      <form class="flex min-w-[280px] flex-1 gap-2 sm:max-w-xl" @submit.prevent="scanWaybill">
        <input
          ref="waybillInput"
          v-model.trim="waybillCode"
          type="text"
          placeholder="Scan returned waybill"
          class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
        >
        <button
          type="submit"
          :disabled="scanningWaybill || !waybillCode"
          class="rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-amber-700 disabled:opacity-50"
        >
          {{ scanningWaybill ? 'Loading...' : 'Scan Waybill' }}
        </button>
      </form>
    </div>

    <admin-global-filter-bar
      class="mt-5"
      context-key="admin-return-list"
      search-placeholder="Search return, order, waybill, customer or seller"
      @filters-changed="onGlobalFiltersChanged"
    />

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
      <div class="flex gap-2">
        <button
          v-for="option in statusOptions"
          :key="option.value"
          type="button"
          class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide"
          :class="filters.status === option.value ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'"
          @click="setStatus(option.value)"
        >
          {{ option.label }}
        </button>
      </div>
      <div class="flex items-center gap-2">
        <select
          v-model="filters.date_basis"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
          @change="applyDateBasis"
        >
          <option value="return_date">Return Date</option>
          <option value="order_date">Order Date</option>
        </select>
        <button type="button" class="text-xs font-semibold uppercase tracking-wide text-blue-600 hover:underline dark:text-blue-300" @click="fetchReturns">
          Refresh
        </button>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">Return</th>
            <th class="px-3 py-3 text-left">Order</th>
            <th class="px-3 py-3 text-left">Customer</th>
            <th class="px-3 py-3 text-left">Seller</th>
            <th class="px-3 py-3 text-left">Waybill</th>
            <th class="px-3 py-3 text-left">Scanned Items</th>
            <th class="px-3 py-3 text-left">Received</th>
            <th class="px-3 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="8" class="px-3 py-8 text-center text-slate-500">Loading returns...</td>
          </tr>
          <tr v-else-if="!returns.length">
            <td colspan="8" class="px-3 py-8 text-center text-slate-500">No returns found.</td>
          </tr>
          <tr v-for="row in returns" :key="row.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">
              {{ row.return_number }}
              <span class="mt-1 block text-[10px] uppercase tracking-wide" :class="row.status === 'completed' ? 'text-emerald-600' : 'text-amber-600'">
                {{ row.status }}
              </span>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">#{{ row.order?.id || '-' }}</td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
              <p>{{ row.order?.customer_name || '-' }}</p>
              <p class="text-xs text-slate-500">{{ row.order?.phone || '-' }}</p>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ sellerName(row.order?.seller) }}</td>
            <td class="px-3 py-3 font-mono text-xs text-slate-700 dark:text-slate-200">{{ row.waybill_no || '-' }}</td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ row.items_count }}</td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(row.return_date) }}</td>
            <td class="px-3 py-3 text-right">
              <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="loadReturn(row.waybill_no)">
                {{ row.status === 'completed' ? 'View' : 'Resume' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
      <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold disabled:opacity-50 dark:border-slate-700 dark:text-slate-200" :disabled="meta.current_page <= 1 || loading" @click="changePage(meta.current_page - 1)">Previous</button>

      <div class="flex flex-wrap items-center justify-center gap-1.5">
        <button
          v-for="entry in pageEntries"
          :key="entry.key"
          type="button"
          class="min-w-9 rounded-lg border px-2 py-1.5 text-xs font-semibold transition"
          :class="entry.type === 'ellipsis'
            ? 'cursor-default border-transparent text-slate-400'
            : Number(entry.value) === Number(meta.current_page)
              ? 'border-blue-600 bg-blue-600 text-white'
              : 'border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800'"
          :disabled="loading || entry.type === 'ellipsis'"
          @click="entry.type === 'page' ? changePage(Number(entry.value)) : null"
        >
          {{ entry.label }}
        </button>
      </div>

      <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold disabled:opacity-50 dark:border-slate-700 dark:text-slate-200" :disabled="meta.current_page >= meta.last_page || loading" @click="changePage(meta.current_page + 1)">Next</button>
    </div>
    <p class="mt-2 text-center text-xs text-slate-500">Page {{ meta.current_page }} of {{ meta.last_page }} · {{ meta.total }} returns</p>
  </section>

  <div v-if="activeReturn" class="fixed inset-0 z-[1200] flex justify-end bg-black/50">
    <div class="h-full w-full max-w-2xl overflow-y-auto bg-white p-5 shadow-2xl dark:bg-slate-900">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Warehouse Return</p>
          <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ activeReturn.return_number }}</h2>
          <p class="mt-1 text-sm text-slate-500">Order #{{ activeReturn.order?.id }} · {{ activeReturn.order?.waybill_no }}</p>
        </div>
        <button type="button" class="text-xl text-slate-500" @click="closeDrawer">×</button>
      </div>

      <div class="mt-4 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800">
        <p class="font-semibold text-slate-900 dark:text-white">{{ activeReturn.order?.customer_name || '-' }}</p>
        <p class="text-slate-500">{{ activeReturn.order?.phone || '-' }}</p>
        <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
          {{ activeReturn.scanned_count }} / {{ activeReturn.items_count }} barcodes inspected
        </p>
      </div>

      <form v-if="activeReturn.status === 'pending'" class="mt-4 rounded-xl border border-slate-200 p-3 dark:border-slate-700" @submit.prevent>
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Scan product barcode</label>
        <input
          ref="barcodeInput"
          v-model.trim="barcode"
          type="text"
          placeholder="Scan item barcode"
          class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
        >
        <div class="mt-3 grid grid-cols-2 gap-2">
          <button type="button" :disabled="scanningItem || !barcode" class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white disabled:opacity-50" @click="scanItem('returned')">Accept Return</button>
          <button type="button" :disabled="scanningItem || !barcode" class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white disabled:opacity-50" @click="scanItem('damaged')">Mark Damaged</button>
        </div>
      </form>

      <div class="mt-4 space-y-2">
        <div v-for="item in activeReturn.items" :key="item.id" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
          <div class="min-w-0">
            <p class="truncate font-mono text-xs text-slate-800 dark:text-slate-100">{{ item.barcode }}</p>
            <p class="mt-1 text-xs text-slate-500">Lot {{ item.lot_number || item.lot_id }} · {{ item.sku || `Variant #${item.variant_id}` }}</p>
          </div>
          <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-wide" :class="itemTone(item)">
            {{ item.scanned ? item.disposition : 'Pending' }}
          </span>
        </div>
      </div>

      <button
        v-if="activeReturn.status === 'pending'"
        type="button"
        :disabled="finalizing || activeReturn.scanned_count !== activeReturn.items_count"
        class="mt-5 w-full rounded-xl bg-slate-900 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-white disabled:cursor-not-allowed disabled:opacity-40 dark:bg-white dark:text-slate-900"
        @click="finalizeReturn"
      >
        {{ finalizing ? 'Finalizing...' : 'Finalize Return And Restock' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const waybillInput = ref(null)
const barcodeInput = ref(null)
const waybillCode = ref('')
const barcode = ref('')
const returns = ref([])
const activeReturn = ref(null)
const loading = ref(false)
const scanningWaybill = ref(false)
const scanningItem = ref(false)
const finalizing = ref(false)
const filters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  seller_id: null,
  date_basis: 'return_date',
  status: 'all',
  page: 1,
  per_page: 20,
})
const meta = reactive({ current_page: 1, last_page: 1, total: 0 })
const storageKey = 'nextep-preferences-admin-return-list'
const statusOptions = [
  { value: 'all', label: 'All' },
  { value: 'pending', label: 'Pending' },
  { value: 'completed', label: 'Completed' },
]

const pageEntries = computed(() => {
  const current = Number(meta.current_page || 1)
  const last = Number(meta.last_page || 1)
  if (last <= 1) return [{ key: 'page-1', type: 'page', value: 1, label: '1' }]

  const windowStart = Math.max(1, current - 2)
  const windowEnd = Math.min(last, current + 2)
  const entries = []

  const pushPage = (value) => entries.push({
    key: `page-${value}`,
    type: 'page',
    value,
    label: String(value),
  })
  const pushEllipsis = (key) => entries.push({
    key: `ellipsis-${key}`,
    type: 'ellipsis',
    value: null,
    label: '...',
  })

  pushPage(1)
  if (windowStart > 2) pushEllipsis('left')
  for (let page = windowStart; page <= windowEnd; page++) {
    if (page !== 1 && page !== last) pushPage(page)
  }
  if (windowEnd < last - 1) pushEllipsis('right')
  if (last !== 1) pushPage(last)

  return entries
})

const sellerName = (seller) => {
  if (!seller) return '-'
  return `${seller.first_name || ''} ${seller.last_name || ''}`.trim() || seller.email || `Seller #${seller.id}`
}

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? '-' : date.toLocaleString()
}

const itemTone = (item) => {
  if (!item.scanned) return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
  return item.disposition === 'damaged'
    ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300'
    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
}

const fetchReturns = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/returns', { params: filters })
    returns.value = data?.returns || []
    Object.assign(meta, data?.meta || {})
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load returns.')
  } finally {
    loading.value = false
  }
}

const loadReturn = async (code) => {
  waybillCode.value = code || ''
  await scanWaybill()
}

const scanWaybill = async () => {
  if (!waybillCode.value) return
  scanningWaybill.value = true
  try {
    const { data } = await axios.post('/api/admin/returns/scan', { code: waybillCode.value })
    activeReturn.value = data
    waybillCode.value = ''
    barcode.value = ''
    await fetchReturns()
    await nextTick()
    barcodeInput.value?.focus()
  } catch (error) {
    toast.error(firstValidationError(error) || error?.response?.data?.message || 'Waybill scan failed.')
  } finally {
    scanningWaybill.value = false
  }
}

const scanItem = async (disposition) => {
  if (!barcode.value || !activeReturn.value) return
  scanningItem.value = true
  try {
    const { data } = await axios.post(`/api/admin/returns/${activeReturn.value.id}/scan-lot-item`, {
      barcode: barcode.value,
      disposition,
    })
    activeReturn.value = data.return
    barcode.value = ''
    toast.success(data.message)
    await fetchReturns()
  } catch (error) {
    toast.error(firstValidationError(error) || error?.response?.data?.message || 'Barcode scan failed.')
  } finally {
    scanningItem.value = false
    await nextTick()
    barcodeInput.value?.focus()
  }
}

const finalizeReturn = async () => {
  if (!activeReturn.value) return
  finalizing.value = true
  try {
    const { data } = await axios.post(`/api/admin/returns/${activeReturn.value.id}/finalize`)
    activeReturn.value = data.return
    toast.success(data.message)
    await fetchReturns()
  } catch (error) {
    toast.error(firstValidationError(error) || error?.response?.data?.message || 'Return finalization failed.')
  } finally {
    finalizing.value = false
  }
}

const firstValidationError = (error) => {
  const errors = error?.response?.data?.errors
  if (!errors) return ''
  return Object.values(errors).flat()[0] || ''
}

const closeDrawer = async () => {
  activeReturn.value = null
  await nextTick()
  waybillInput.value?.focus()
}

const setStatus = (status) => {
  filters.status = status
  filters.page = 1
  fetchReturns()
}

const savePreferences = () => {
  window.localStorage.setItem(storageKey, JSON.stringify({
    date_basis: filters.date_basis,
  }))
}

const loadPreferences = () => {
  try {
    const parsed = JSON.parse(window.localStorage.getItem(storageKey) || '{}')
    if (parsed?.date_basis === 'return_date' || parsed?.date_basis === 'order_date') {
      filters.date_basis = parsed.date_basis
    }
  } catch {
    // ignore preference load errors
  }
}

const applyDateBasis = () => {
  filters.page = 1
  savePreferences()
  fetchReturns()
}

const onGlobalFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.seller_id = payload?.seller_id || null
  filters.page = 1
  fetchReturns()
}

const changePage = (page) => {
  filters.page = page
  fetchReturns()
}

onMounted(() => {
  loadPreferences()
  waybillInput.value?.focus()
})
</script>
