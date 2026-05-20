<template>
  <div class="space-y-6">
    <div
      v-if="error"
      class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
    >
      {{ error }}
    </div>

    <div
      v-if="data.seller.is_restrict"
      class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800 shadow-sm dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-100"
    >
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.24em] text-rose-600 dark:text-rose-300">Account Restricted</p>
          <h2 class="mt-2 text-xl font-extrabold">Your account is restricted</h2>
          <p class="mt-1 text-sm text-rose-700 dark:text-rose-200">
            Some seller actions are currently limited due to active penalties on your account.
          </p>
        </div>
        <span class="inline-flex w-fit rounded-full bg-white/80 px-3 py-1 text-xs font-bold uppercase tracking-wide text-rose-700 dark:bg-rose-950/40 dark:text-rose-200">
          {{ restrictionLabel }}
        </span>
      </div>

      <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
        <span
          v-if="data.seller.restrictions.blocked_order_placing"
          class="rounded-full bg-rose-100 px-3 py-1 text-rose-700 dark:bg-rose-950/50 dark:text-rose-200"
        >
          Order placing blocked
        </span>
        <span
          v-if="data.seller.restrictions.blocked_withdrawals"
          class="rounded-full bg-rose-100 px-3 py-1 text-rose-700 dark:bg-rose-950/50 dark:text-rose-200"
        >
          Withdrawals blocked
        </span>
        <span
          v-if="data.seller.restrictions.daily_order_limit !== null"
          class="rounded-full bg-amber-100 px-3 py-1 text-amber-800 dark:bg-amber-950/50 dark:text-amber-200"
        >
          Daily order limit: {{ data.seller.restrictions.daily_order_limit }}
        </span>
      </div>
    </div>

    <section class="rounded-3xl">
      <div class="grid gap-3 xl:grid-cols-[0.75fr_1.25fr]">
        <article class="rounded-2xl border border-zinc-200/80 bg-gradient-to-br from-stone-100 via-white to-zinc-100 p-5 shadow-sm dark:border-zinc-700 dark:from-zinc-900 dark:via-slate-900 dark:to-stone-900">
          <div class="flex h-full flex-col justify-between gap-6">
            <div>
              <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-zinc-600 dark:text-zinc-300">Welcome</p>
              <h1 class="mt-2 text-2xl font-extrabold text-slate-950 dark:text-white sm:text-3xl">
                {{ data.seller.name || 'Seller' }}
              </h1>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Seller Level</p>
                <p class="mt-1 text-lg font-black text-slate-950 dark:text-white">{{ levelLabel }}</p>
              </div>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Current Points</p>
                <p class="mt-1 text-lg font-black text-zinc-900 dark:text-zinc-100">{{ formatNumber(data.points.current_points) }}</p>
              </div>
            </div>
          </div>
        </article>

        <div class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm ring-1 ring-zinc-500/5 dark:border-zinc-800 dark:bg-slate-900/80 dark:ring-zinc-400/10">
          <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <div class="flex items-start gap-4">
                  <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                    <img v-if="progressLevelIcon" :src="progressLevelIcon" alt="Seller level icon" class="aspect-square h-full w-full object-cover" />
                    <i v-else class="fas fa-medal text-xl text-zinc-500 dark:text-zinc-300"></i>
                  </div>
                  <div>
                    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-zinc-600 dark:text-zinc-300">Progress</p>
                    <h2 class="mt-2 text-xl font-extrabold text-slate-950 dark:text-white">
                      {{ nextLevelLabel ? `On the way to ${nextLevelLabel}` : 'Top seller level active' }}
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                      Current level: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ levelLabel }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="rounded-2xl bg-zinc-100 px-4 py-3 text-right dark:bg-zinc-800/70">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">Current Points</p>
                <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">{{ formatNumber(data.points.current_points) }}</p>
              </div>
            </div>

            <div class="mt-5">
              <div class="flex items-center justify-between gap-3 text-xs font-semibold text-slate-600 dark:text-slate-300">
                <span>{{ formatNumber(data.points.current_level_progress_points) }} earned in this level</span>
                <span>{{ currentProgressPct }}%</span>
              </div>
              <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-zinc-800 via-stone-500 to-zinc-400 transition-all duration-500 dark:from-zinc-200 dark:via-stone-400 dark:to-zinc-500"
                  :style="{ width: `${currentProgressPct}%` }"
                ></div>
              </div>
              <div class="mt-2 flex items-center justify-between gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                <span>{{ formatNumber(data.points.current_level_points) }} pts</span>
                <span v-if="data.points.next_level_points">{{ formatNumber(data.points.next_level_points) }} pts target</span>
                <span v-else>Highest level</span>
              </div>
            </div>
          </div>
      </div>
      </div>
    </section>

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
                class="w-full rounded-full bg-zinc-700 transition-all duration-300 dark:bg-zinc-300"
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
  seller: {
    id: null,
    name: '',
    status: '',
    is_restrict: false,
    restrictions: {
      blocked_withdrawals: false,
      blocked_order_placing: false,
      daily_order_limit: null,
      active_penalties_count: 0,
    },
    delivery_score: 100,
    delivery_score_penalty_limit: 60,
  },
  points: {
    level_name: '',
    level_no: null,
    level_icon_url: '',
    current_level_points: 0,
    current_points: 0,
    pending_points: 0,
    projected_points: 0,
    pending_base_amount: 0,
    next_level_name: '',
    next_level_no: null,
    next_level_icon_url: '',
    next_level_points: null,
    current_points_to_next_level: null,
    current_level_progress_points: 0,
    current_level_progress_target: null,
    current_progress_pct: 0,
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

    return () => h('article', { class: `flex min-h-28 rounded-2xl border p-4 shadow-sm ${tones[props.tone] || tones.slate}` }, [
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
      class: 'rounded-2xl border border-zinc-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-zinc-400 hover:shadow-sm dark:border-zinc-800 dark:bg-slate-950 dark:hover:border-zinc-500',
    }, [
      h('div', { class: 'flex items-center justify-between gap-3' }, [
        h('p', { class: 'text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400' }, props.eyebrow),
        h('span', { class: 'inline-flex h-8 w-8 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200' }, [
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

const progressLevelIcon = computed(() => data.points.next_level_icon_url || data.points.level_icon_url || '')

const currentProgressPct = computed(() => {
  return Math.min(100, Math.max(0, Number(data.points.current_progress_pct || 0)))
})

const maxTrendOrders = computed(() => Math.max(1, ...data.trend.map((day) => Number(day.orders_count || 0))))

const deliveryScore = computed(() => Math.min(100, Math.max(0, Number(data.seller.delivery_score ?? 100))))

const deliveryScorePenaltyLimit = computed(() => Math.min(100, Math.max(0, Number(data.seller.delivery_score_penalty_limit ?? 60))))

const deliveryScoreTone = computed(() => {
  if (deliveryScore.value < deliveryScorePenaltyLimit.value) return 'amber'
  return 'emerald'
})

const restrictionLabel = computed(() => {
  const count = Number(data.seller.restrictions?.active_penalties_count || 0)
  return count === 1 ? '1 Active Penalty' : `${count} Active Penalties`
})

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
