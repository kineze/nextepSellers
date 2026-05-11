<template>
  <div class="space-y-6">
    <div
      v-if="error"
      class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
    >
      {{ error }}
    </div>

    <section class="rounded-3xl ">
      <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-blue-600 dark:text-blue-300">Seller Dashboard</p>
          <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">
            Welcome back, {{ data.seller.name || 'Seller' }}
          </h1>
          <p class="mt-3 max-w-3xl text-sm text-slate-600 dark:text-slate-300">
            Track selling performance, fulfillment movement, points, and payout readiness from one analytics view.
          </p>
        </div>

        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-blue-500/30 dark:bg-slate-950 dark:text-blue-200 dark:hover:bg-blue-500/10"
          :disabled="loading"
          @click="fetchAnalytics"
        >
          <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-arrows-rotate'" aria-hidden="true"></i>
          Refresh
        </button>
      </div>

      <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <MetricCard label="Current Level" :value="levelLabel" icon="fa-medal" />
        <MetricCard label="Current Points" :value="formatNumber(data.points.current_points)" icon="fa-star" />
        <MetricCard
          label="Pending Points"
          :value="formatNumber(data.points.pending_points)"
          :subtext="`From LKR ${toMoney(data.points.pending_base_amount)} ongoing value`"
          icon="fa-hourglass-half"
          tone="amber"
        />
        <MetricCard
          label="Projected Points"
          :value="formatNumber(data.points.projected_points)"
          :subtext="nextLevelLabel ? `${formatNumber(data.points.points_to_next_level)} more for ${nextLevelLabel}` : 'Top level reached'"
          icon="fa-arrow-trend-up"
          tone="emerald"
        />
      </div>

      <div class="mt-3 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
        <div class="flex items-center justify-between gap-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <span>Projected Progress</span>
          <span>{{ data.points.progress_pct }}%</span>
        </div>
        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
          <div
            class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 transition-all duration-300"
            :style="{ width: `${data.points.progress_pct || 0}%` }"
          ></div>
        </div>
        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
          <template v-if="data.points.next_level_points">
            {{ formatNumber(data.points.level_progress_points) }} / {{ formatNumber(data.points.level_progress_target) }} points from {{ levelLabel }} to {{ nextLevelLabel }}
          </template>
          <template v-else>
            Highest seller level is active.
          </template>
        </p>
        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
          Point conversion: LKR {{ toMoney(data.points.lkr_per_point, 0) }} = 1 point
        </p>
      </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <MetricCard label="Total Orders" :value="formatNumber(data.orders.total)" icon="fa-boxes-stacked" />
      <MetricCard label="Completed Orders" :value="formatNumber(statusCount('completed'))" icon="fa-circle-check" tone="emerald" />
      <MetricCard label="Available Commission" :value="`LKR ${toMoney(data.finance.available_commission)}`" icon="fa-wallet" tone="blue" />
      <MetricCard label="Paid Commission" :value="`LKR ${toMoney(data.finance.paid_commission)}`" icon="fa-money-check-dollar" tone="violet" />
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
      <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Order Status</h2>
          <span class="rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:text-slate-300">
            {{ formatNumber(data.orders.total) }} Total
          </span>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          <div
            v-for="status in statuses"
            :key="status.key"
            class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/70"
          >
            <div class="flex items-center justify-between gap-3">
              <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ status.label }}</span>
              <span class="text-lg font-extrabold text-slate-900 dark:text-white">{{ formatNumber(statusCount(status.key)) }}</span>
            </div>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
              <div
                class="h-full rounded-full"
                :class="status.bar"
                :style="{ width: `${statusPercent(status.key)}%` }"
              ></div>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">14 Day Activity</h2>
        <div class="mt-4 flex h-44 items-end gap-2">
          <div
            v-for="day in data.trend"
            :key="day.date"
            class="flex min-w-0 flex-1 flex-col items-center gap-2"
            :title="`${formatShortDate(day.date)}: ${formatNumber(day.orders_count)} orders`"
          >
            <div class="flex h-32 w-full items-end rounded-full bg-slate-100 px-1 dark:bg-slate-800">
              <div
                class="w-full rounded-full bg-blue-500 transition-all duration-300 dark:bg-blue-400"
                :style="{ height: `${trendHeight(day.orders_count)}%` }"
              ></div>
            </div>
            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">{{ formatDay(day.date) }}</span>
          </div>
        </div>
      </section>
    </div>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Recent Orders</h2>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Latest customer orders connected to your seller account.</p>
        </div>
        <a :href="data.links.orders || '/seller/orders'" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
          <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
          View All
        </a>
      </div>

      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead>
            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
              <th class="px-3 py-2">Order</th>
              <th class="px-3 py-2">Customer</th>
              <th class="px-3 py-2">Date</th>
              <th class="px-3 py-2">Status</th>
              <th class="px-3 py-2">Payment</th>
              <th class="px-3 py-2 text-right">Collectable</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            <tr v-if="loading">
              <td colspan="6" class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Loading dashboard analytics...</td>
            </tr>
            <tr v-else-if="!data.recent_orders.length">
              <td colspan="6" class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No orders to show yet.</td>
            </tr>
            <tr v-for="order in data.recent_orders" :key="order.id" class="text-slate-700 dark:text-slate-200">
              <td class="px-3 py-2.5 font-semibold">#{{ order.id }}</td>
              <td class="px-3 py-2.5">{{ order.customer_name || '-' }}</td>
              <td class="px-3 py-2.5">{{ formatDateTime(order.order_datetime) }}</td>
              <td class="px-3 py-2.5">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="statusPillClass(order.status)">
                  {{ capitalize(order.status) }}
                </span>
              </td>
              <td class="px-3 py-2.5">{{ capitalize(order.payment_status) }}</td>
              <td class="px-3 py-2.5 text-right font-semibold">LKR {{ toMoney(order.total_collectable_amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
      <QuickLink title="Product List" eyebrow="Products" text="Browse active catalog items." icon="fa-layer-group" :href="data.links.products || '/seller/products'" />
      <QuickLink title="My Orders" eyebrow="Orders" text="Track fulfillment progress." icon="fa-cart-shopping" :href="data.links.orders || '/seller/orders'" />
      <QuickLink title="Bulk Orders" eyebrow="Orders" text="Create multiple orders." icon="fa-table-list" :href="data.links.bulk_orders || '/seller/bulk-orders'" />
      <QuickLink title="Payments" eyebrow="Finance" text="Monitor payout values." icon="fa-wallet" :href="data.links.payments || '/seller/payments'" />
      <QuickLink title="Affiliate" eyebrow="Growth" text="Refer other sellers." icon="fa-people-arrows" :href="data.links.affiliate || '/seller/affiliate'" />
    </div>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const error = ref('')

const data = reactive({
  seller: { id: null, name: '', status: '' },
  points: {
    level_name: '',
    level_no: null,
    current_level_points: 0,
    current_points: 0,
    pending_points: 0,
    projected_points: 0,
    pending_base_amount: 0,
    next_level_name: '',
    next_level_no: null,
    next_level_points: null,
    points_to_next_level: null,
    level_progress_points: 0,
    level_progress_target: null,
    progress_pct: 0,
    lkr_per_point: 100,
  },
  orders: { total: 0, by_status: {} },
  finance: {
    pending_commission: 0,
    available_commission: 0,
    paid_commission: 0,
    pending_order_value: 0,
    completed_order_value: 0,
  },
  trend: [],
  recent_orders: [],
  links: {},
})

const statuses = [
  { key: 'draft', label: 'Draft', bar: 'bg-slate-500' },
  { key: 'approved', label: 'Approved', bar: 'bg-sky-500' },
  { key: 'confirmed', label: 'Confirmed', bar: 'bg-indigo-500' },
  { key: 'packed', label: 'Packed', bar: 'bg-violet-500' },
  { key: 'shipped', label: 'Shipped', bar: 'bg-blue-500' },
  { key: 'completed', label: 'Completed', bar: 'bg-emerald-500' },
  { key: 'cancelled', label: 'Cancelled', bar: 'bg-rose-500' },
  { key: 'rejected', label: 'Rejected', bar: 'bg-amber-500' },
]

const MetricCard = defineComponent({
  props: {
    label: { type: String, required: true },
    value: { type: String, required: true },
    subtext: { type: String, default: '' },
    icon: { type: String, default: 'fa-chart-line' },
    tone: { type: String, default: 'slate' },
  },
  setup(props) {
    const tones = {
      slate: 'border-slate-200/80 bg-white text-slate-900 dark:border-slate-700 dark:bg-slate-900/70 dark:text-white',
      blue: 'border-blue-200/80 bg-blue-50/80 text-blue-900 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-100',
      emerald: 'border-emerald-200/80 bg-emerald-50/80 text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100',
      amber: 'border-amber-200/80 bg-amber-50/80 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100',
      violet: 'border-violet-200/80 bg-violet-50/80 text-violet-900 dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100',
    }

    return () => h('article', { class: `rounded-2xl border p-4 shadow-sm ${tones[props.tone] || tones.slate}` }, [
      h('div', { class: 'flex items-start justify-between gap-3' }, [
        h('div', [
          h('p', { class: 'text-[11px] font-semibold uppercase tracking-wider opacity-70' }, props.label),
          h('p', { class: 'mt-2 text-2xl font-extrabold' }, props.value),
          props.subtext ? h('p', { class: 'mt-1 text-[11px] opacity-75' }, props.subtext) : null,
        ]),
        h('span', { class: 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/70 text-sm shadow-sm dark:bg-slate-950/50' }, [
          h('i', { class: `fas ${props.icon}`, 'aria-hidden': 'true' }),
        ]),
      ]),
    ])
  },
})

const QuickLink = defineComponent({
  props: {
    title: { type: String, required: true },
    eyebrow: { type: String, required: true },
    text: { type: String, required: true },
    href: { type: String, required: true },
    icon: { type: String, default: 'fa-arrow-right' },
  },
  setup(props) {
    return () => h('a', {
      href: props.href,
      class: 'rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:hover:border-blue-500/40',
    }, [
      h('div', { class: 'flex items-center justify-between gap-3' }, [
        h('p', { class: 'text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400' }, props.eyebrow),
        h('span', { class: 'inline-flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300' }, [
          h('i', { class: `fas ${props.icon}`, 'aria-hidden': 'true' }),
        ]),
      ]),
      h('p', { class: 'mt-2 text-base font-bold text-slate-900 dark:text-white' }, props.title),
      h('p', { class: 'mt-1 text-xs text-slate-600 dark:text-slate-300' }, props.text),
    ])
  },
})

const levelLabel = computed(() => {
  if (!data.points.level_name) return 'Not Assigned'
  return data.points.level_no ? `${data.points.level_name} (L${data.points.level_no})` : data.points.level_name
})

const nextLevelLabel = computed(() => {
  if (!data.points.next_level_name) return ''
  return data.points.next_level_no ? `${data.points.next_level_name} (L${data.points.next_level_no})` : data.points.next_level_name
})

const maxTrendOrders = computed(() => Math.max(1, ...data.trend.map((day) => Number(day.orders_count || 0))))

const applyPayload = (payload) => {
  Object.assign(data.seller, payload?.seller || {})
  Object.assign(data.points, payload?.points || {})
  data.orders = {
    total: Number(payload?.orders?.total || 0),
    by_status: payload?.orders?.by_status || {},
  }
  Object.assign(data.finance, payload?.finance || {})
  data.trend = Array.isArray(payload?.trend) ? payload.trend : []
  data.recent_orders = Array.isArray(payload?.recent_orders) ? payload.recent_orders : []
  data.links = payload?.links || {}
}

const fetchAnalytics = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get('/api/seller/dashboard/analytics')
    applyPayload(response.data)
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to load seller dashboard analytics.'
    toast.error(error.value)
  } finally {
    loading.value = false
  }
}

const statusCount = (status) => Number(data.orders.by_status?.[status] || 0)

const statusPercent = (status) => {
  if (!data.orders.total) return 0
  return Math.round((statusCount(status) / data.orders.total) * 100)
}

const trendHeight = (count) => {
  const value = Number(count || 0)
  if (value <= 0) return 5
  return Math.max(12, Math.round((value / maxTrendOrders.value) * 100))
}

const toMoney = (value, decimals = 2) => Number(value || 0).toLocaleString(undefined, {
  minimumFractionDigits: decimals,
  maximumFractionDigits: decimals,
})

const formatNumber = (value) => Number(value || 0).toLocaleString()

const capitalize = (value) => {
  if (!value) return '-'
  return String(value).replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

const formatDateTime = (value) => {
  if (!value) return '-'
  return new Intl.DateTimeFormat(undefined, {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value))
}

const formatShortDate = (value) => {
  if (!value) return '-'
  return new Intl.DateTimeFormat(undefined, { month: 'short', day: '2-digit' }).format(new Date(`${value}T00:00:00`))
}

const formatDay = (value) => {
  if (!value) return '-'
  return new Intl.DateTimeFormat(undefined, { day: '2-digit' }).format(new Date(`${value}T00:00:00`))
}

const statusPillClass = (status) => {
  const classes = {
    completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
    shipped: 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200',
    packed: 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-200',
    approved: 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200',
    confirmed: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200',
    cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200',
    rejected: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
  }

  return classes[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

onMounted(fetchAnalytics)
</script>
