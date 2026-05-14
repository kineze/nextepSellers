<template>
  <section class="mx-3 mt-3 mb-8 space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-violet-600 dark:text-violet-300">Reports</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Order Count Report</h1>
      </div>
      <button
        type="button"
        class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-900"
        :disabled="loading"
        @click="fetchReport"
      >
        {{ loading ? 'Refreshing...' : 'Refresh' }}
      </button>
    </div>

    <admin-global-filter-bar
      context-key="admin-reports-order-count"
      default-date-preset="month"
      search-placeholder="Search order, customer, waybill or seller"
      @filters-changed="onFiltersChanged"
    />

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Filtered Orders</p>
          <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">{{ formatNumber(report.total_orders) }}</p>
        </div>
        <div class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-2 text-right dark:border-violet-500/30 dark:bg-violet-500/10">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-200">Delivery Success</p>
          <p class="mt-1 text-xl font-bold text-violet-800 dark:text-violet-100">{{ report.delivery.success_ratio }}%</p>
        </div>
      </div>

      <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px_minmax(0,1fr)]">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
          <status-card
            v-for="status in leftStatuses"
            :key="status.value"
            :label="status.label"
            :count="statusCount(status.value)"
            :tone="status.tone"
            :icon="status.icon"
          />
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Delivery Ratio</p>
              <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ deliveryLabel }}</p>
            </div>
            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600 shadow-sm dark:bg-slate-900 dark:text-slate-200">
              {{ formatNumber(deliveryTotal) }}
            </span>
          </div>

          <div class="relative mx-auto mt-4 h-56 w-56">
            <svg viewBox="0 0 120 120" class="h-full w-full -rotate-90">
              <circle cx="60" cy="60" r="44" fill="none" stroke="currentColor" stroke-width="14" class="text-slate-200 dark:text-slate-700" />
              <circle
                v-for="segment in chartSegments"
                :key="segment.key"
                cx="60"
                cy="60"
                r="44"
                fill="none"
                stroke-width="14"
                stroke-linecap="round"
                :stroke="segment.color"
                :stroke-dasharray="`${segment.length} ${circumference - segment.length}`"
                :stroke-dashoffset="segment.offset"
              />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
              <p class="text-4xl font-black text-slate-900 dark:text-white">{{ report.delivery.success_ratio }}%</p>
              <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Success</p>
            </div>
          </div>

          <div class="mt-4 grid grid-cols-3 gap-2 text-center">
            <legend-pill label="Completed" :value="report.delivery.completed" color-class="bg-emerald-500" />
            <legend-pill label="In Transit" :value="report.delivery.in_transit" color-class="bg-blue-500" />
            <legend-pill label="Failed" :value="report.delivery.unsuccessful" color-class="bg-rose-500" />
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
          <status-card
            v-for="status in rightStatuses"
            :key="status.value"
            :label="status.label"
            :count="statusCount(status.value)"
            :tone="status.tone"
            :icon="status.icon"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, h, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const StatusCard = {
  props: {
    label: { type: String, required: true },
    count: { type: Number, required: true },
    tone: { type: String, default: 'slate' },
    icon: { type: String, default: 'fa-circle' },
  },
  setup(props) {
    const tones = {
      slate: 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200',
      amber: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200',
      sky: 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200',
      blue: 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200',
      emerald: 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200',
      rose: 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200',
    }

    return () => h('article', { class: `rounded-2xl border p-4 ${tones[props.tone] || tones.slate}` }, [
      h('div', { class: 'flex items-center justify-between gap-3' }, [
        h('div', [
          h('p', { class: 'text-[11px] font-semibold uppercase tracking-wide opacity-75' }, props.label),
          h('p', { class: 'mt-2 text-3xl font-black' }, Number(props.count || 0).toLocaleString()),
        ]),
        h('span', { class: 'inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/70 text-sm shadow-sm dark:bg-slate-950/40' }, [
          h('i', { class: `fas ${props.icon}` }),
        ]),
      ]),
    ])
  },
}

const LegendPill = {
  props: {
    label: { type: String, required: true },
    value: { type: Number, required: true },
    colorClass: { type: String, required: true },
  },
  setup(props) {
    return () => h('div', { class: 'rounded-xl bg-white p-2 shadow-sm dark:bg-slate-900' }, [
      h('span', { class: `mx-auto block h-2 w-8 rounded-full ${props.colorClass}` }),
      h('p', { class: 'mt-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400' }, props.label),
      h('p', { class: 'mt-1 text-sm font-bold text-slate-900 dark:text-white' }, Number(props.value || 0).toLocaleString()),
    ])
  },
}

const toast = useToast()
const loading = ref(false)

const filters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  seller_id: null,
})

const report = reactive({
  total_orders: 0,
  status_counts: {},
  delivery: {
    completed: 0,
    unsuccessful: 0,
    in_transit: 0,
    success_ratio: 0,
  },
})

const leftStatuses = [
  { value: 'draft', label: 'Draft', tone: 'slate', icon: 'fa-pen-to-square' },
  { value: 'approved', label: 'Approved', tone: 'sky', icon: 'fa-thumbs-up' },
  { value: 'confirmed', label: 'Confirmed', tone: 'blue', icon: 'fa-circle-check' },
  { value: 'packed', label: 'Packed', tone: 'amber', icon: 'fa-box' },
]

const rightStatuses = [
  { value: 'shipped', label: 'Shipped', tone: 'blue', icon: 'fa-truck-fast' },
  { value: 'completed', label: 'Completed', tone: 'emerald', icon: 'fa-check' },
  { value: 'cancelled', label: 'Cancelled', tone: 'rose', icon: 'fa-ban' },
  { value: 'rejected', label: 'Rejected', tone: 'rose', icon: 'fa-circle-xmark' },
]

const circumference = 2 * Math.PI * 44
const deliveryTotal = computed(() => {
  return Number(report.delivery.completed || 0)
    + Number(report.delivery.unsuccessful || 0)
    + Number(report.delivery.in_transit || 0)
})

const chartSegments = computed(() => {
  const total = deliveryTotal.value
  if (total <= 0) return []

  let consumed = 0
  return [
    { key: 'completed', value: report.delivery.completed, color: '#10b981' },
    { key: 'in_transit', value: report.delivery.in_transit, color: '#3b82f6' },
    { key: 'unsuccessful', value: report.delivery.unsuccessful, color: '#f43f5e' },
  ].filter((segment) => Number(segment.value || 0) > 0)
    .map((segment) => {
      const length = (Number(segment.value || 0) / total) * circumference
      const item = {
        ...segment,
        length,
        offset: -consumed,
      }
      consumed += length
      return item
    })
})

const deliveryLabel = computed(() => {
  if (deliveryTotal.value <= 0) return 'No delivery activity in this filter.'
  return `${formatNumber(report.delivery.completed)} completed from ${formatNumber(deliveryTotal.value)} delivery-stage orders`
})

const statusCount = (status) => Number(report.status_counts?.[status] || 0)
const formatNumber = (value) => Number(value || 0).toLocaleString()

const onFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.seller_id = payload?.seller_id || null
  fetchReport()
}

const fetchReport = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/reports/order-count', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
      },
    })

    report.total_orders = Number(data?.total_orders || 0)
    report.status_counts = data?.status_counts || {}
    report.delivery.completed = Number(data?.delivery?.completed || 0)
    report.delivery.unsuccessful = Number(data?.delivery?.unsuccessful || 0)
    report.delivery.in_transit = Number(data?.delivery?.in_transit || 0)
    report.delivery.success_ratio = Number(data?.delivery?.success_ratio || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load order count report.')
  } finally {
    loading.value = false
  }
}
</script>
