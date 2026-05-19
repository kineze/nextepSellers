<template>
  <section class="mx-3 mt-3 mb-8 space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-violet-600 dark:text-violet-300">Reports</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Finance Report</h1>
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
      context-key="admin-reports-finance"
      default-date-preset="month"
      search-placeholder="Search order, customer, waybill, seller or product"
      @filters-changed="onFiltersChanged"
    />

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
      <summary-tile label="Selling Total" :value="formatCurrency(report.summary.selling_total)" icon="fa-sack-dollar" tone="sky" />
      <summary-tile label="Commission Cost" :value="formatCurrency(report.summary.commission_total)" icon="fa-chart-line" tone="amber" />
      <summary-tile label="Affiliate Cost" :value="formatCurrency(report.summary.affiliate_total)" icon="fa-people-arrows" tone="amber" />
      <summary-tile label="Company Net Profit" :value="formatCurrency(report.summary.company_net_profit)" icon="fa-building-columns" tone="violet" />
    </div>

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
      <summary-tile label="Completed Orders" :value="formatNumber(report.summary.completed_orders)" icon="fa-circle-check" tone="slate" />
      <summary-tile label="Net Sales" :value="formatCurrency(report.summary.net_sales_total)" icon="fa-receipt" tone="slate" />
      <summary-tile label="Delivery Collected" :value="formatCurrency(report.summary.delivery_total)" icon="fa-truck-fast" tone="slate" />
      <summary-tile label="Profit Margin" :value="`${report.summary.profit_margin}%`" icon="fa-percent" tone="slate" />
    </div>

    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-200">Winning Product</p>
          <h2 class="mt-1 text-xl font-black text-emerald-950 dark:text-white">{{ winningProductName }}</h2>
          <p class="mt-1 text-xs font-semibold text-emerald-700/80 dark:text-emerald-200/80">
            {{ formatNumber(report.winning_product?.total_quantity || 0) }} units · {{ formatCurrency(report.winning_product?.selling_total || 0) }} selling total
          </p>
        </div>
        <span class="rounded-full bg-white px-3 py-1 text-xs font-black uppercase tracking-wide text-emerald-700 shadow-sm dark:bg-slate-950/50 dark:text-emerald-200">
          Top by sales
        </span>
      </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Income</p>
          <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Top products by selling total</h2>
        </div>
        <div class="h-80">
          <canvas ref="salesChartCanvas"></canvas>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cost Breakdown</p>
          <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Sales after payouts</h2>
        </div>
        <div class="relative mx-auto h-64 w-64 max-w-full">
          <canvas ref="costChartCanvas"></canvas>
          <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ formatCurrency(report.summary.company_net_profit) }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Net Profit</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">What We Sell</p>
          <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Units by product</h2>
        </div>
        <div class="h-80">
          <canvas ref="unitsChartCanvas"></canvas>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Commission Cost</p>
          <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Product commission allocation</h2>
        </div>
        <div class="h-80">
          <canvas ref="commissionChartCanvas"></canvas>
        </div>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4 dark:border-slate-800">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Product Finance</p>
          <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">Completed sales by product</h2>
        </div>
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Loads 15 products at a time</p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-[980px] w-full text-left text-sm">
          <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-slate-800/70 dark:text-slate-300">
            <tr>
              <th class="px-4 py-3">Rank</th>
              <th class="px-4 py-3">Product</th>
              <th class="px-4 py-3 text-right">Completed Orders</th>
              <th class="px-4 py-3 text-right">Units</th>
              <th class="px-4 py-3 text-right">Selling Total</th>
              <th class="px-4 py-3 text-right">Commission Cost</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="!loading && !report.products.length">
              <td colspan="6" class="px-4 py-8 text-center text-sm font-semibold text-slate-500 dark:text-slate-400">
                No completed product sales found for the selected filters.
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
              <td class="px-4 py-3 text-right align-top font-semibold text-slate-700 dark:text-slate-200">{{ formatNumber(product.completed_orders) }}</td>
              <td class="px-4 py-3 text-right align-top font-semibold text-slate-700 dark:text-slate-200">{{ formatNumber(product.total_quantity) }}</td>
              <td class="px-4 py-3 text-right align-top font-semibold text-slate-700 dark:text-slate-200">{{ formatCurrency(product.selling_total) }}</td>
              <td class="px-4 py-3 text-right align-top font-black text-slate-900 dark:text-white">{{ formatCurrency(product.commission_total) }}</td>
            </tr>
          </tbody>
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
      amber: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200',
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
const salesChartCanvas = ref(null)
const costChartCanvas = ref(null)
const unitsChartCanvas = ref(null)
const commissionChartCanvas = ref(null)
let salesChart = null
let costChart = null
let unitsChart = null
let commissionChart = null

const perPage = 15
const filters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  seller_id: null,
})
const pagination = reactive({
  has_more: false,
})
const report = reactive({
  summary: {
    completed_orders: 0,
    selling_total: 0,
    net_sales_total: 0,
    delivery_total: 0,
    discount_total: 0,
    commission_total: 0,
    affiliate_total: 0,
    company_net_profit: 0,
    profit_margin: 0,
  },
  winning_product: null,
  chart_products: [],
  products: [],
  product_count: 0,
})

const winningProductName = computed(() => report.winning_product?.product_name || '-')
const formatNumber = (value) => Number(value || 0).toLocaleString()
const formatCurrency = (value) => `LKR ${Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 2 })}`

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
        label: (context) => `${context.dataset?.label || context.label}: ${formatCurrency(context.raw)}`,
      },
    },
  },
}

const chartLabels = () => report.chart_products.length ? report.chart_products.map((product) => product.product_name) : ['No data']

const renderCharts = async () => {
  await nextTick()
  renderSalesChart()
  renderCostChart()
  renderUnitsChart()
  renderCommissionChart()
}

const renderSalesChart = () => {
  if (!salesChartCanvas.value) return
  const labels = chartLabels()
  const data = report.chart_products.length ? report.chart_products.map((product) => Number(product.selling_total || 0)) : [0]

  if (!salesChart) {
    salesChart = new Chart(salesChartCanvas.value, {
      type: 'bar',
      data: { labels, datasets: [{ label: 'Selling total', data, backgroundColor: '#7c3aed', borderRadius: 8 }] },
      options: { ...chartOptions, indexAxis: 'y', scales: { x: { beginAtZero: true }, y: { ticks: { font: { size: 11, weight: 'bold' } } } } },
    })
    return
  }

  salesChart.data.labels = labels
  salesChart.data.datasets[0].data = data
  salesChart.update()
}

const renderCostChart = () => {
  if (!costChartCanvas.value) return
  const values = [
    Math.max(0, Number(report.summary.company_net_profit || 0)),
    Number(report.summary.commission_total || 0),
    Number(report.summary.affiliate_total || 0),
  ]
  const data = values.some((value) => value > 0) ? values : [1, 0]

  if (!costChart) {
    costChart = new Chart(costChartCanvas.value, {
      type: 'doughnut',
      data: {
        labels: ['Company Net Profit', 'Commission Cost', 'Affiliate Cost'],
        datasets: [{ data, backgroundColor: ['#10b981', '#f59e0b', '#ef4444'], borderColor: '#ffffff', borderWidth: 4, hoverOffset: 8 }],
      },
      options: { ...chartOptions, cutout: '72%', plugins: { ...chartOptions.plugins, legend: { display: false } } },
    })
    return
  }

  costChart.data.datasets[0].data = data
  costChart.update()
}

const renderUnitsChart = () => {
  if (!unitsChartCanvas.value) return
  const labels = chartLabels()
  const data = report.chart_products.length ? report.chart_products.map((product) => Number(product.total_quantity || 0)) : [0]

  if (!unitsChart) {
    unitsChart = new Chart(unitsChartCanvas.value, {
      type: 'bar',
      data: { labels, datasets: [{ label: 'Units sold', data, backgroundColor: '#0ea5e9', borderRadius: 8 }] },
      options: {
        ...chartOptions,
        scales: { x: { ticks: { font: { size: 11, weight: 'bold' } } }, y: { beginAtZero: true, ticks: { precision: 0 } } },
        plugins: { ...chartOptions.plugins, tooltip: { callbacks: { label: (context) => `Units sold: ${formatNumber(context.raw)}` } } },
      },
    })
    return
  }

  unitsChart.data.labels = labels
  unitsChart.data.datasets[0].data = data
  unitsChart.update()
}

const renderCommissionChart = () => {
  if (!commissionChartCanvas.value) return
  const labels = chartLabels()
  const data = report.chart_products.length ? report.chart_products.map((product) => Number(product.commission_total || 0)) : [0]

  if (!commissionChart) {
    commissionChart = new Chart(commissionChartCanvas.value, {
      type: 'bar',
      data: { labels, datasets: [{ label: 'Commission cost', data, backgroundColor: '#f59e0b', borderRadius: 8 }] },
      options: { ...chartOptions, scales: { x: { ticks: { font: { size: 11, weight: 'bold' } } }, y: { beginAtZero: true } } },
    })
    return
  }

  commissionChart.data.labels = labels
  commissionChart.data.datasets[0].data = data
  commissionChart.update()
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
  }

  try {
    const { data } = await axios.get('/api/admin/reports/finance', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        per_page: perPage,
        offset: append ? report.products.length : 0,
      },
    })

    Object.assign(report.summary, data?.summary || {})
    report.winning_product = data?.winning_product || null
    report.chart_products = Array.isArray(data?.chart_products) ? data.chart_products : []
    report.product_count = Number(data?.product_count || 0)
    const rows = Array.isArray(data?.products) ? data.products : []
    report.products = append ? [...report.products, ...rows] : rows
    pagination.has_more = Boolean(data?.pagination?.has_more)
    renderCharts()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load finance report.')
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

watch(() => report.chart_products, renderCharts)

onBeforeUnmount(() => {
  ;[salesChart, costChart, unitsChart, commissionChart].forEach((chart) => chart?.destroy())
  salesChart = null
  costChart = null
  unitsChart = null
  commissionChart = null
})
</script>
