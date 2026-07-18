<template>
  <section class="mx-3 mt-3 space-y-4 pb-8">
    <div class="rounded-3xl border border-slate-200/80 bg-white/95 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/90">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">My Order</p>
          <h1 class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">#{{ order?.id || orderId }}</h1>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ formatDate(order?.order_datetime) }}</p>
        </div>
        <a href="/seller/orders" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
          Back to Orders
        </a>
      </div>
    </div>

    <div v-if="loading" class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
      Loading order...
    </div>

    <template v-else-if="order">
      <div class="grid gap-4 lg:grid-cols-4">
        <article class="rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-4 text-white shadow-sm">
          <p class="text-xs uppercase tracking-wide text-emerald-100">Seller Earnings</p>
          <p class="mt-1 text-2xl font-bold">LKR {{ toMoney(commissionAmount) }}</p>
          <p class="mt-1 text-xs text-emerald-100">Your earning from this order</p>
        </article>
        <article class="rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 p-4 text-white shadow-sm">
          <p class="text-xs uppercase tracking-wide text-sky-100">Points Earned</p>
          <p class="mt-1 text-2xl font-bold">{{ pointsEarned }}</p>
          <p class="mt-1 text-xs text-sky-100">Based on net sale value</p>
        </article>
        <article class="rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-600 p-4 text-white shadow-sm">
          <p class="text-xs uppercase tracking-wide text-violet-100">Net Sale</p>
          <p class="mt-1 text-2xl font-bold">LKR {{ toMoney(netSale) }}</p>
          <p class="mt-1 text-xs text-violet-100">Excludes delivery fee</p>
        </article>
        <article class="rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-4 text-white shadow-sm">
          <p class="text-xs uppercase tracking-wide text-amber-100">Delivery Fee</p>
          <p class="mt-1 text-2xl font-bold">LKR {{ toMoney(order.delivery_charge) }}</p>
          <p class="mt-1 text-xs text-amber-100">Charged on this order</p>
        </article>
      </div>

      <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-1">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Order Details</h2>
            <div class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-200">
              <p><span class="font-semibold">Status:</span> {{ order.status || '-' }}</p>
              <p><span class="font-semibold">Waybill:</span> {{ order.waybill_no || '-' }}</p>
              <p><span class="font-semibold">Delivery Status:</span> {{ order.delivery_status || '-' }}</p>
              <p><span class="font-semibold">Net:</span> LKR {{ toMoney(order.net_total) }}</p>
              <p><span class="font-semibold">Delivery Fee:</span> LKR {{ toMoney(order.delivery_charge) }}</p>
              <p><span class="font-semibold">Discount:</span> LKR {{ toMoney(order.total_discount) }}</p>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Customer</h2>
            <div class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-200">
              <p><span class="font-semibold">Name:</span> {{ order.customer_name || '-' }}</p>
              <p><span class="font-semibold">Phone:</span> {{ order.phone || '-' }}</p>
              <p><span class="font-semibold">Additional:</span> {{ order.additional_phone || '-' }}</p>
              <p><span class="font-semibold">City:</span> {{ order.city?.name_en || '-' }}</p>
              <p><span class="font-semibold">Address:</span> {{ order.address || '-' }}</p>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Dispatch</h2>
            <p class="mt-3 text-sm text-slate-700 dark:text-slate-200">
              <span class="font-semibold">Latest Note:</span>
              {{ latestDispatchRef || '-' }}
            </p>
          </div>
        </div>

        <div class="space-y-4 lg:col-span-2">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-2">
              <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Royal Express Tracking</h2>
              <button
                type="button"
                class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                :disabled="timelineLoading"
                @click="fetchTimeline(true)"
              >
                {{ timelineLoading ? 'Refreshing...' : 'Refresh Tracking' }}
              </button>
            </div>
            <p v-if="timelineMessage" class="mt-2 text-xs text-amber-600 dark:text-amber-300">{{ timelineMessage }}</p>
            <div class="mt-3">
              <p v-if="timelineLoading" class="text-sm text-slate-500 dark:text-slate-400">Loading tracking...</p>
              <ul v-else-if="timeline.length" class="space-y-2">
                <li
                  v-for="(event, idx) in timeline"
                  :key="`${idx}-${event.title}-${event.at || 'na'}`"
                  class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50"
                >
                  <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ event.title }}</p>
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                      Courier
                    </span>
                  </div>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ formatDate(event.at) }}</p>
                  <p v-if="event.details" class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ event.details }}</p>
                </li>
              </ul>
              <p v-else class="text-sm text-slate-500 dark:text-slate-400">No tracking events yet.</p>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Products</h2>
            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
              <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                  <tr>
                    <th class="px-3 py-2 text-left">Product</th>
                    <th class="px-3 py-2 text-left">Variant</th>
                    <th class="px-3 py-2 text-left">Pricing</th>
                    <th class="px-3 py-2 text-right">Price</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2 text-right">Earning</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                  <tr v-for="item in order.items || []" :key="item.id">
                    <td class="px-3 py-2">{{ item.product?.title || '-' }}</td>
                    <td class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">{{ variantText(item.variant?.attributes) }}</td>
                    <td class="px-3 py-2"><span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase" :class="item.pricing_model === 'reseller' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300'">{{ item.pricing_model === 'reseller' ? 'Margin' : 'Commission' }}</span></td>
                    <td class="px-3 py-2 text-right">LKR {{ toMoney(item.price) }}</td>
                    <td class="px-3 py-2 text-right">{{ item.quantity || 0 }}</td>
                    <td class="px-3 py-2 text-right font-semibold">LKR {{ toMoney(Number(item.price || 0) * Number(item.quantity || 0)) }}</td>
                    <td class="px-3 py-2 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(item.seller_earning_amount) }}</td>
                  </tr>
                  <tr v-if="!(order.items || []).length">
                    <td colspan="7" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No items</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Order Activity Log</h2>
              <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">Latest on top</span>
            </div>
            <ul v-if="orderLogs.length" class="mt-3 space-y-2">
              <li
                v-for="log in orderLogs"
                :key="log.id"
                class="relative rounded-lg border border-slate-200 bg-slate-50/80 p-3 pl-4 dark:border-slate-700 dark:bg-slate-800/50"
              >
                <span class="absolute left-0 top-3 h-7 w-1 rounded-r-full bg-blue-500/70"></span>
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ logEventTitle(log) }}</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(log.created_at) }}</p>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">By {{ logActor(log) }}</p>
                <p v-if="log.note" class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ log.note }}</p>
              </li>
            </ul>
            <p v-else class="mt-3 text-sm text-slate-500 dark:text-slate-400">No order activity logs yet.</p>
          </div>
        </div>
      </div>
    </template>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
  orderId: { type: Number, required: true },
})

const toast = useToast()
const loading = ref(false)
const timelineLoading = ref(false)
const timelineMessage = ref('')
const order = ref(null)
const timeline = ref([])

const toMoney = (value) => Number(value || 0).toFixed(2)

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

const variantText = (attrs) => {
  if (!attrs || typeof attrs !== 'object') return '-'
  const text = Object.entries(attrs)
    .map(([k, v]) => `${k}: ${typeof v === 'object' ? JSON.stringify(v) : String(v)}`)
    .join(', ')
  return text || '-'
}

const commissionAmount = computed(() => Number(order.value?.computed_commission_amount ?? order.value?.commission_amount ?? 0))
const pointsEarned = computed(() => Number(order.value?.computed_points_earned ?? 0))
const netSale = computed(() => Math.max(0, Number(order.value?.net_total || 0) - Number(order.value?.total_discount || 0)))

const orderLogs = computed(() => {
  const rows = Array.isArray(order.value?.logs) ? [...order.value.logs] : []
  return rows.sort((a, b) => {
    const aTs = a?.created_at ? new Date(a.created_at).getTime() : 0
    const bTs = b?.created_at ? new Date(b.created_at).getTime() : 0
    return bTs - aTs
  })
})

const latestDispatchRef = computed(() => {
  const rows = order.value?.dispatch_note_items || []
  if (!rows.length) return null
  const latest = [...rows].sort((a, b) => Number(b.dispatch_note_id || 0) - Number(a.dispatch_note_id || 0))[0]
  return latest?.dispatch_note?.ref_no || null
})

const logEventTitle = (log) => {
  const type = String(log?.event_type || '').toLowerCase()
  if (type === 'status_changed') {
    return `Status changed: ${log?.from_status || 'unknown'} -> ${log?.to_status || 'unknown'}`
  }
  if (type === 'created') return 'Order created'
  if (type === 'updated') return 'Order updated'
  return log?.event_type || 'Order event'
}

const logActor = (log) => log?.user?.name || log?.user?.email || 'System'

const fetchOrder = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/seller/orders/${props.orderId}`)
    order.value = data?.order || null
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load order.')
  } finally {
    loading.value = false
  }
}

const fetchTimeline = async (refresh = true) => {
  timelineLoading.value = true
  timelineMessage.value = ''
  try {
    const { data } = await axios.get(`/api/seller/orders/${props.orderId}/delivery-timeline`, {
      params: { refresh: refresh ? 1 : 0 },
    })
    timeline.value = Array.isArray(data?.timeline) ? data.timeline : []
    if (data?.courier_message) {
      timelineMessage.value = data.courier_message
    }
    if (order.value && data?.delivery_status) {
      order.value.delivery_status = data.delivery_status
    }
  } catch (error) {
    timeline.value = []
    timelineMessage.value = error?.response?.data?.message || 'Failed to load tracking.'
  } finally {
    timelineLoading.value = false
  }
}

const bootstrap = async () => {
  await fetchOrder()
  await fetchTimeline(true)
}

bootstrap()
</script>
