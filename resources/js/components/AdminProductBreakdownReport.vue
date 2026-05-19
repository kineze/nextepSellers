<template>
  <section class="mx-3 mt-3 mb-8 space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-violet-600 dark:text-violet-300">Reports</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Product Breakdown</h1>
      </div>
      <button
        type="button"
        class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-900"
        :disabled="loading"
        @click="refreshReport"
      >
        {{ loading ? 'Refreshing...' : 'Refresh' }}
      </button>
    </div>

    <admin-global-filter-bar
      context-key="admin-reports-product-breakdown"
      default-date-preset="month"
      search-placeholder="Search order, customer, waybill, seller or product"
      @filters-changed="onFiltersChanged"
    />

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
      <summary-tile label="Product Orders" :value="formatNumber(report.total_orders)" icon="fa-cart-shopping" tone="slate" />
      <summary-tile label="Products Sold" :value="formatNumber(report.product_count)" icon="fa-box-open" tone="sky" />
      <summary-tile label="Units Ordered" :value="formatNumber(report.quantity_total)" icon="fa-layer-group" tone="emerald" />
      <summary-tile label="Product Total" :value="formatCurrency(report.product_total)" icon="fa-coins" tone="violet" />
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Top Products</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Orders by product</h2>
          </div>
          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-200">
            Highest first
          </span>
        </div>
        <div class="h-80">
          <canvas ref="topProductChartCanvas"></canvas>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status Mix</p>
          <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">All product orders</h2>
        </div>
        <div class="relative mx-auto h-64 w-64 max-w-full">
          <canvas ref="statusChartCanvas"></canvas>
          <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ report.delivery.success_ratio }}%</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Success</p>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Product Detail</p>
          <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Top product status breakdown</h2>
        </div>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="status in statuses"
            :key="status"
            class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase"
            :class="statusBadgeClass(status)"
          >
            {{ statusLabels[status] }} {{ formatNumber(statusTotal(status)) }}
          </span>
        </div>
      </div>
      <div class="h-96">
        <canvas ref="productStatusChartCanvas"></canvas>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="mb-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Quantity</p>
        <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Units ordered by top products</h2>
      </div>
      <div class="h-80">
        <canvas ref="quantityChartCanvas"></canvas>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4 dark:border-slate-800">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Product List</p>
          <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Ranked by total orders</h2>
        </div>
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
          Status counts use the global filter bar
        </p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-[1160px] w-full text-left text-sm">
          <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-slate-800/70 dark:text-slate-300">
            <tr>
              <th class="px-4 py-3">Rank</th>
              <th class="px-4 py-3">Product</th>
              <th v-for="status in statuses" :key="status" class="px-3 py-3 text-right">{{ statusLabels[status] }}</th>
              <th class="px-4 py-3 text-right">Units</th>
              <th class="px-4 py-3 text-right">Product Total</th>
              <th class="px-4 py-3 text-right">Total Orders</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="!loading && !report.products.length">
              <td :colspan="statuses.length + 5" class="px-4 py-8 text-center text-sm font-semibold text-slate-500 dark:text-slate-400">
                No product orders found for the selected filters.
              </td>
            </tr>
            <tr v-for="product in report.products" :key="product.product_id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
              <td class="px-4 py-3 align-top">
                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-violet-100 px-2 text-xs font-black text-violet-700 dark:bg-violet-500/20 dark:text-violet-200">
                  {{ product.rank }}
                </span>
              </td>
              <td class="px-4 py-3 align-top">
                <p class="font-bold text-slate-900 dark:text-white">{{ product.product_name }}</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ product.product_code || '-' }}</p>
              </td>
              <td v-for="status in statuses" :key="`${product.product_id}-${status}`" class="px-3 py-3 text-right align-top font-semibold text-slate-700 dark:text-slate-200">
                {{ formatNumber(product.status_counts?.[status] || 0) }}
              </td>
              <td class="px-4 py-3 text-right align-top font-semibold text-slate-700 dark:text-slate-200">{{ formatNumber(product.total_quantity) }}</td>
              <td class="px-4 py-3 text-right align-top font-semibold text-slate-700 dark:text-slate-200">{{ formatCurrency(product.product_total) }}</td>
              <td class="px-4 py-3 text-right align-top text-base font-black text-slate-900 dark:text-white">{{ formatNumber(product.total_orders) }}</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-100 text-xs font-black uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-100">
            <tr>
              <td class="px-4 py-3" colspan="2">Filtered Total</td>
              <td v-for="status in statuses" :key="`total-${status}`" class="px-3 py-3 text-right">{{ formatNumber(statusTotal(status)) }}</td>
              <td class="px-4 py-3 text-right">{{ formatNumber(report.quantity_total) }}</td>
              <td class="px-4 py-3 text-right">{{ formatCurrency(report.product_total) }}</td>
              <td class="px-4 py-3 text-right">{{ formatNumber(report.total_orders) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="border-t border-slate-200 p-4 text-center dark:border-slate-800">
        <button
          v-if="pagination.has_more"
          type="button"
          class="rounded-xl bg-violet-600 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="loadingMore"
          @click="loadMore"
        >
          {{ loadingMore ? 'Loading...' : `Show More (${formatNumber(report.products.length)} of ${formatNumber(report.product_count)})` }}
        </button>
        <p v-else class="text-xs font-semibold text-slate-500 dark:text-slate-400">
          Showing {{ formatNumber(report.products.length) }} of {{ formatNumber(report.product_count) }} products
        </p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, h, nextTick, onBeforeUnmount, reactive, ref, watch } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'
import { useToast } from 'vue-toastification'

const SummaryTile = {
  props: {
    label: { type: String, required: true },
    value: { type: String, required: true },
    icon: { type: String, required: true },
    tone: { type: String, default: 'slate' },
  },
  setup(props) {
    const tones = {
      slate: 'border-slate-200 bg-white text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200',
      sky: 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200',
      emerald: 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200',
      violet: 'border-violet-200 bg-violet-50 text-violet-800 dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-200',
    }

    return () => h('article', { class: `rounded-2xl border p-4 shadow-sm ${tones[props.tone] || tones.slate}` }, [
      h('div', { class: 'flex items-center justify-between gap-3' }, [
        h('div', { class: 'min-w-0' }, [
          h('p', { class: 'text-[11px] font-semibold uppercase tracking-wide opacity-70' }, props.label),
          h('p', { class: 'mt-2 truncate text-2xl font-black' }, props.value),
        ]),
        h('span', { class: 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/70 text-sm shadow-sm dark:bg-slate-950/40' }, [
          h('i', { class: `fas ${props.icon}` }),
        ]),
      ]),
    ])
  },
}

const toast = useToast()
const loading = ref(false)
const loadingMore = ref(false)
const topProductChartCanvas = ref(null)
const statusChartCanvas = ref(null)
const productStatusChartCanvas = ref(null)
const quantityChartCanvas = ref(null)
let topProductChart = null
let statusChart = null
let productStatusChart = null
let quantityChart = null

const statuses = ['draft', 'approved', 'confirmed', 'packed', 'shipped', 'completed', 'cancelled', 'rejected']
const statusLabels = {
  draft: 'Draft',
  approved: 'Approved',
  confirmed: 'Confirmed',
  packed: 'Packed',
  shipped: 'Shipped',
  completed: 'Completed',
  cancelled: 'Cancelled',
  rejected: 'Rejected',
}
const statusColors = {
  draft: '#64748b',
  approved: '#0ea5e9',
  confirmed: '#2563eb',
  packed: '#f59e0b',
  shipped: '#3b82f6',
  completed: '#10b981',
  cancelled: '#f43f5e',
  rejected: '#be123c',
}

const filters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  seller_id: null,
})

const report = reactive({
  total_orders: 0,
  product_count: 0,
  status_totals: {},
  quantity_total: 0,
  product_total: 0,
  delivery: {
    completed: 0,
    unsuccessful: 0,
    success_ratio: 0,
  },
  products: [],
  chart_products: [],
})

const perPage = 15
const pagination = reactive({
  offset: 0,
  has_more: false,
})

const topProducts = computed(() => report.chart_products.slice(0, 10))

const formatNumber = (value) => Number(value || 0).toLocaleString()
const formatCurrency = (value) => `LKR ${Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 2 })}`
const statusTotal = (status) => Number(report.status_totals?.[status] || 0)

const statusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-100',
    approved: 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
    confirmed: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200',
    packed: 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200',
    shipped: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200',
    completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
    cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-200',
  }
  return classes[status] || classes.draft
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        boxWidth: 10,
        font: { size: 11, weight: 'bold' },
      },
    },
    tooltip: {
      callbacks: {
        label: (context) => `${context.dataset?.label || context.label}: ${formatNumber(context.raw)}`,
      },
    },
  },
}

const renderCharts = async () => {
  await nextTick()
  renderTopProductChart()
  renderStatusChart()
  renderProductStatusChart()
  renderQuantityChart()
}

const topLabels = () => topProducts.value.length ? topProducts.value.map((product) => product.product_name) : ['No data']

const renderTopProductChart = () => {
  if (!topProductChartCanvas.value) return
  const labels = topLabels()
  const data = topProducts.value.length ? topProducts.value.map((product) => Number(product.total_orders || 0)) : [0]

  if (!topProductChart) {
    topProductChart = new Chart(topProductChartCanvas.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Total orders',
          data,
          backgroundColor: '#7c3aed',
          borderRadius: 8,
        }],
      },
      options: {
        ...chartOptions,
        indexAxis: 'y',
        scales: {
          x: { beginAtZero: true, ticks: { precision: 0 } },
          y: { ticks: { font: { size: 11, weight: 'bold' } } },
        },
      },
    })
    return
  }

  topProductChart.data.labels = labels
  topProductChart.data.datasets[0].data = data
  topProductChart.update()
}

const renderStatusChart = () => {
  if (!statusChartCanvas.value) return
  const values = statuses.map((status) => statusTotal(status))
  const data = values.some((value) => value > 0) ? values : [1, 0, 0, 0, 0, 0, 0, 0]

  if (!statusChart) {
    statusChart = new Chart(statusChartCanvas.value, {
      type: 'doughnut',
      data: {
        labels: statuses.map((status) => statusLabels[status]),
        datasets: [{
          data,
          backgroundColor: statuses.map((status) => statusColors[status]),
          borderColor: '#ffffff',
          borderWidth: 4,
          hoverOffset: 8,
        }],
      },
      options: {
        ...chartOptions,
        cutout: '72%',
        plugins: {
          ...chartOptions.plugins,
          legend: { display: false },
        },
      },
    })
    return
  }

  statusChart.data.datasets[0].data = data
  statusChart.update()
}

const renderProductStatusChart = () => {
  if (!productStatusChartCanvas.value) return
  const labels = topLabels()
  const datasets = statuses.map((status) => ({
    label: statusLabels[status],
    data: topProducts.value.length
      ? topProducts.value.map((product) => Number(product.status_counts?.[status] || 0))
      : [0],
    backgroundColor: statusColors[status],
    borderRadius: 6,
  }))

  if (!productStatusChart) {
    productStatusChart = new Chart(productStatusChartCanvas.value, {
      type: 'bar',
      data: { labels, datasets },
      options: {
        ...chartOptions,
        scales: {
          x: { stacked: true, ticks: { font: { size: 11, weight: 'bold' } } },
          y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
        },
      },
    })
    return
  }

  productStatusChart.data.labels = labels
  productStatusChart.data.datasets = datasets
  productStatusChart.update()
}

const renderQuantityChart = () => {
  if (!quantityChartCanvas.value) return
  const labels = topLabels()
  const data = topProducts.value.length ? topProducts.value.map((product) => Number(product.total_quantity || 0)) : [0]

  if (!quantityChart) {
    quantityChart = new Chart(quantityChartCanvas.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Units ordered',
          data,
          backgroundColor: '#10b981',
          borderRadius: 8,
        }],
      },
      options: {
        ...chartOptions,
        scales: {
          x: { ticks: { font: { size: 11, weight: 'bold' } } },
          y: { beginAtZero: true, ticks: { precision: 0 } },
        },
      },
    })
    return
  }

  quantityChart.data.labels = labels
  quantityChart.data.datasets[0].data = data
  quantityChart.update()
}

const onFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.seller_id = payload?.seller_id || null
  fetchReport({ append: false })
}

const refreshReport = () => fetchReport({ append: false })

const loadMore = () => {
  if (loading.value || loadingMore.value || !pagination.has_more) return
  fetchReport({ append: true })
}

const fetchReport = async ({ append = false } = {}) => {
  if (append) {
    loadingMore.value = true
  } else {
    loading.value = true
    pagination.offset = 0
  }

  try {
    const { data } = await axios.get('/api/admin/reports/product-breakdown', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        per_page: perPage,
        offset: append ? report.products.length : 0,
      },
    })

    report.total_orders = Number(data?.total_orders || 0)
    report.product_count = Number(data?.product_count || 0)
    report.status_totals = data?.status_totals || {}
    report.quantity_total = Number(data?.quantity_total || 0)
    report.product_total = Number(data?.product_total || 0)
    report.delivery.completed = Number(data?.delivery?.completed || 0)
    report.delivery.unsuccessful = Number(data?.delivery?.unsuccessful || 0)
    report.delivery.success_ratio = Number(data?.delivery?.success_ratio || 0)
    report.chart_products = Array.isArray(data?.chart_products) ? data.chart_products : []
    const rows = Array.isArray(data?.products) ? data.products : []
    report.products = append ? [...report.products, ...rows] : rows
    pagination.offset = Number(data?.pagination?.offset || 0)
    pagination.has_more = Boolean(data?.pagination?.has_more)
    renderCharts()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load product breakdown report.')
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

watch(() => report.chart_products, renderCharts)

onBeforeUnmount(() => {
  ;[topProductChart, statusChart, productStatusChart, quantityChart].forEach((chart) => chart?.destroy())
  topProductChart = null
  statusChart = null
  productStatusChart = null
  quantityChart = null
})
</script>
