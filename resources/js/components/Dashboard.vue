<template>
  <section class=" mt-3 mb-8 space-y-5">
    <div class="relative overflow-hidden rounded-3xl border border-pink-200/70 bg-gradient-to-br from-rose-100 via-pink-50 to-sky-100 p-6 shadow-sm dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
      <div class="pointer-events-none absolute -top-10 -right-10 h-40 w-40 rounded-full bg-pink-300/30 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-12 -left-8 h-36 w-36 rounded-full bg-sky-300/30 blur-3xl"></div>

      <div class="relative z-10 flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-rose-600 dark:text-rose-300">Admin Analytics</p>
          <h1 class="mt-1 text-2xl font-black text-slate-900 dark:text-white">Dashboard Overview</h1>
          <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Key performance snapshot for sales, payouts, invoices and seller activity.</p>
        </div>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-2xl border border-white/60 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-wide text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-white disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-100 dark:hover:bg-slate-800"
          :disabled="loading"
          @click="fetchAnalytics"
        >
          <i class="fas fa-rotate"></i>
          {{ loading ? 'Refreshing...' : 'Refresh Data' }}
        </button>
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <article class="group rounded-2xl border border-cyan-200 bg-cyan-50/70 p-4 shadow-sm transition hover:-translate-y-0.5 dark:border-cyan-500/30 dark:bg-cyan-500/10">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Total Orders</p>
            <p class="mt-2 text-3xl font-black text-cyan-900 dark:text-cyan-100">{{ totals.orders_total }}</p>
          </div>
          <div class="rounded-xl bg-cyan-200/70 px-3 py-2 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300"><i class="fas fa-box"></i></div>
        </div>
      </article>

      <article class="group rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 shadow-sm transition hover:-translate-y-0.5 dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Completed Orders</p>
            <p class="mt-2 text-3xl font-black text-emerald-900 dark:text-emerald-100">{{ totals.completed_orders }}</p>
          </div>
          <div class="rounded-xl bg-emerald-200/70 px-3 py-2 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300"><i class="fas fa-circle-check"></i></div>
        </div>
      </article>

      <article class="group rounded-2xl border border-violet-200 bg-violet-50/70 p-4 shadow-sm transition hover:-translate-y-0.5 dark:border-violet-500/30 dark:bg-violet-500/10">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-300">Net Sales</p>
            <p class="mt-2 text-2xl font-black text-violet-900 dark:text-violet-100">LKR {{ money(totals.net_sales_total) }}</p>
          </div>
          <div class="rounded-xl bg-violet-200/70 px-3 py-2 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300"><i class="fas fa-chart-line"></i></div>
        </div>
      </article>

      <article class="group rounded-2xl border border-amber-200 bg-amber-50/70 p-4 shadow-sm transition hover:-translate-y-0.5 dark:border-amber-500/30 dark:bg-amber-500/10">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Commission</p>
            <p class="mt-2 text-2xl font-black text-amber-900 dark:text-amber-100">LKR {{ money(totals.commission_total) }}</p>
          </div>
          <div class="rounded-xl bg-amber-200/70 px-3 py-2 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300"><i class="fas fa-coins"></i></div>
        </div>
      </article>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-300">Orders Breakdown</h2>
        <div class="mt-4 space-y-3 text-sm">
          <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><span>Shipped</span><strong>{{ totals.shipped_orders }}</strong></div>
          <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><span>Cancelled</span><strong>{{ totals.cancelled_orders }}</strong></div>
        </div>
      </article>

      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-300">Payments Breakdown</h2>
        <div class="mt-4 space-y-3 text-sm">
          <div class="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 dark:border-rose-500/20 dark:bg-rose-500/10"><span>Pending</span><strong>LKR {{ money(payments.pending_commission) }}</strong></div>
          <div class="flex items-center justify-between rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-2 dark:border-cyan-500/20 dark:bg-cyan-500/10"><span>Available</span><strong>LKR {{ money(payments.available_commission) }}</strong></div>
          <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 dark:border-emerald-500/20 dark:bg-emerald-500/10"><span>Paid</span><strong>LKR {{ money(payments.paid_commission) }}</strong></div>
        </div>
      </article>

      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-300">Seller Breakdown</h2>
        <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
          <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><p class="text-xs text-slate-500">Total</p><p class="text-lg font-bold">{{ sellers.total }}</p></div>
          <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><p class="text-xs text-slate-500">Approved</p><p class="text-lg font-bold">{{ sellers.approved }}</p></div>
          <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><p class="text-xs text-slate-500">Pending</p><p class="text-lg font-bold">{{ sellers.pending }}</p></div>
          <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70"><p class="text-xs text-slate-500">Blocked</p><p class="text-lg font-bold">{{ sellers.blocked }}</p></div>
        </div>
      </article>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-300">Invoice Breakdown</h2>
        <div class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
          <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs text-slate-500">Total Invoices</p><p class="mt-1 text-lg font-bold">{{ invoiceStats.total }}</p></div>
          <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs text-slate-500">Draft</p><p class="mt-1 text-lg font-bold">{{ invoiceStats.draft }}</p></div>
          <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs text-slate-500">Paid</p><p class="mt-1 text-lg font-bold">{{ invoiceStats.paid }}</p></div>
          <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"><p class="text-xs text-slate-500">Total Commission</p><p class="mt-1 text-lg font-bold">LKR {{ money(invoiceStats.total_commission) }}</p></div>
        </div>
      </article>

      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-300">Last 14 Days Orders</h2>
        <div class="mt-4 space-y-2">
          <div v-for="point in trend" :key="point.date" class="grid grid-cols-[74px_1fr_56px] items-center gap-2 text-xs">
            <span class="text-slate-500">{{ shortDate(point.date) }}</span>
            <div class="h-2.5 overflow-hidden rounded bg-slate-200 dark:bg-slate-700">
              <div class="h-full rounded bg-gradient-to-r from-fuchsia-500 to-sky-500" :style="{ width: `${barWidth(point.orders_count)}%` }"></div>
            </div>
            <strong class="text-right text-slate-700 dark:text-slate-200">{{ point.orders_count }}</strong>
          </div>
        </div>
      </article>
    </div>

    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-300">Top Sellers By Commission</h2>
      <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
            <tr>
              <th class="px-3 py-2 text-left">Seller</th>
              <th class="px-3 py-2 text-right">Completed Orders</th>
              <th class="px-3 py-2 text-right">Commission</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <tr v-if="loading">
              <td colspan="3" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">Loading...</td>
            </tr>
            <tr v-else-if="!topSellers.length">
              <td colspan="3" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No seller data.</td>
            </tr>
            <tr v-for="seller in topSellers" :key="seller.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
              <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                <div class="font-semibold">{{ sellerName(seller) }}</div>
                <div class="text-xs text-slate-500">{{ seller.email || '-' }}</div>
              </td>
              <td class="px-3 py-2 text-right font-semibold text-cyan-700 dark:text-cyan-300">{{ seller.completed_orders_count }}</td>
              <td class="px-3 py-2 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ money(seller.total_commission_value) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)

const totals = reactive({
  orders_total: 0,
  completed_orders: 0,
  shipped_orders: 0,
  cancelled_orders: 0,
  net_sales_total: 0,
  commission_total: 0,
})

const payments = reactive({
  pending_commission: 0,
  available_commission: 0,
  paid_commission: 0,
})

const sellers = reactive({
  total: 0,
  approved: 0,
  pending: 0,
  blocked: 0,
  rejected: 0,
})

const invoiceStats = reactive({
  total: 0,
  draft: 0,
  paid: 0,
  cancelled: 0,
  total_commission: 0,
})

const trend = ref([])
const topSellers = ref([])

const money = (value) => Number(value || 0).toFixed(2)

const shortDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
}

const sellerName = (seller) => {
  const full = `${seller?.first_name || ''} ${seller?.last_name || ''}`.trim()
  return full || seller?.email || `Seller #${seller?.id || ''}`
}

const barWidth = (count) => {
  const max = Math.max(...trend.value.map((item) => Number(item.orders_count || 0)), 1)
  return Math.max(6, Math.round((Number(count || 0) / max) * 100))
}

const fetchAnalytics = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/dashboard/analytics')

    Object.assign(totals, data?.totals || {})
    Object.assign(payments, data?.payments || {})
    Object.assign(sellers, data?.sellers || {})
    Object.assign(invoiceStats, data?.invoices || {})

    trend.value = Array.isArray(data?.trend) ? data.trend : []
    topSellers.value = Array.isArray(data?.top_sellers) ? data.top_sellers : []
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load admin analytics.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAnalytics()
})
</script>
