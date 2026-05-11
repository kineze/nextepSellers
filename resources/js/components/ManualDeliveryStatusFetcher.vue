<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-amber-600 dark:text-amber-300">System</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Delivery Status Fetcher</h1>
        <p class="mt-2 max-w-3xl text-sm text-slate-600 dark:text-slate-300">
          Manually fetch Royal Express tracking for shipped orders and let the order tracking sync service update delivered orders to completed.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="tracking-limit">
          Orders
        </label>
        <select
          id="tracking-limit"
          v-model.number="limit"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
          :disabled="fetching"
        >
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
          <option :value="200">200</option>
        </select>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-slate-900"
          :disabled="fetching || !summary.ready || summary.shipped_count < 1"
          @click="fetchTracking"
        >
          <i class="fas fa-rotate" :class="{ 'fa-spin': fetching }" aria-hidden="true"></i>
          Fetch Status
        </button>
      </div>
    </div>

    <div class="mt-5 grid gap-3 md:grid-cols-5">
      <div v-for="item in cards" :key="item.label" class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ item.label }}</p>
        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ item.value }}</p>
      </div>
    </div>

    <div
      v-if="summary.message"
      class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200"
    >
      {{ summary.message }}
    </div>

    <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">Order</th>
            <th class="px-3 py-3 text-left">Customer</th>
            <th class="px-3 py-3 text-left">Seller</th>
            <th class="px-3 py-3 text-left">Waybill</th>
            <th class="px-3 py-3 text-left">Courier Status</th>
            <th class="px-3 py-3 text-left">Local Result</th>
            <th class="px-3 py-3 text-left">Order Status</th>
            <th class="px-3 py-3 text-right">Points</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="fetching">
            <td colspan="8" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Fetching tracking statuses...</td>
          </tr>
          <tr v-else-if="!results.length">
            <td colspan="8" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Run a manual fetch to see order tracking results.</td>
          </tr>
          <tr v-for="row in results" :key="row.order_id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3 align-top font-semibold text-slate-900 dark:text-white">
              <a :href="`/admin/orders/${row.order_id}`" class="hover:underline">#{{ row.order_id }}</a>
              <p v-if="row.error" class="mt-1 text-xs font-medium text-red-600 dark:text-red-300">{{ row.error }}</p>
            </td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">{{ row.customer_name || '-' }}</td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">{{ row.seller_name || '-' }}</td>
            <td class="px-3 py-3 align-top font-mono text-xs text-slate-700 dark:text-slate-200">{{ row.waybill_no || '-' }}</td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">{{ row.courier_status || '-' }}</td>
            <td class="px-3 py-3 align-top">
              <span :class="badgeClass(row)" class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
                {{ resultLabel(row) }}
              </span>
              <p v-if="row.level_upgraded" class="mt-1 text-xs text-emerald-600 dark:text-emerald-300">Level upgraded</p>
            </td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">
              <p>{{ row.before_status || '-' }} -> {{ row.after_status || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ row.after_delivery_status || row.before_delivery_status || '-' }}</p>
            </td>
            <td class="px-3 py-3 align-top text-right font-semibold text-slate-900 dark:text-white">{{ row.points_awarded || 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const fetching = ref(false)
const limit = ref(50)
const results = ref([])

const summary = reactive({
  ready: false,
  message: '',
  shipped_count: 0,
  processed: 0,
  updated_count: 0,
  completed_count: 0,
  cancelled_count: 0,
  error_count: 0,
  remaining_shipped_count: 0,
})

const cards = computed(() => [
  { label: 'Ready Shipped', value: summary.shipped_count },
  { label: 'Processed', value: summary.processed },
  { label: 'Updated', value: summary.updated_count },
  { label: 'Completed', value: summary.completed_count },
  { label: 'Errors', value: summary.error_count },
])

const loadSummary = async () => {
  try {
    const { data } = await axios.get('/api/admin/tracking/manual-fetch/summary')
    summary.ready = Boolean(data?.ready)
    summary.message = data?.message || ''
    summary.shipped_count = Number(data?.shipped_count || 0)
    summary.remaining_shipped_count = summary.shipped_count
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load tracking summary.')
  }
}

const fetchTracking = async () => {
  fetching.value = true
  try {
    const { data } = await axios.post('/api/admin/tracking/manual-fetch', {
      limit: limit.value,
    })

    results.value = Array.isArray(data?.results) ? data.results : []
    summary.ready = Boolean(data?.ready)
    summary.message = ''
    summary.processed = Number(data?.processed || 0)
    summary.updated_count = Number(data?.updated_count || 0)
    summary.completed_count = Number(data?.completed_count || 0)
    summary.cancelled_count = Number(data?.cancelled_count || 0)
    summary.error_count = Number(data?.error_count || 0)
    summary.remaining_shipped_count = Number(data?.remaining_shipped_count || 0)
    summary.shipped_count = summary.remaining_shipped_count

    toast.success(`Fetched ${summary.processed} shipped order status${summary.processed === 1 ? '' : 'es'}.`)
  } catch (error) {
    summary.ready = false
    summary.message = error?.response?.data?.message || 'Failed to fetch tracking statuses.'
    toast.error(summary.message)
  } finally {
    fetching.value = false
  }
}

const resultLabel = (row) => {
  if (row.error) return 'Error'
  if (row.completed) return 'Completed'
  if (row.cancelled) return 'Cancelled'
  if (row.updated) return 'Updated'
  return row.local_status || 'No Change'
}

const badgeClass = (row) => {
  if (row.error) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
  if (row.completed) return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
  if (row.cancelled) return 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300'
  if (row.updated) return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
}

onMounted(loadSummary)
</script>
