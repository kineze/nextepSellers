<template>
  <section class="mx-3 mt-3 pb-8 space-y-4">
    <div class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/90">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-300">Order</p>
          <h1 class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">#{{ order?.id || orderId }}</h1>
          <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">{{ formatDate(order?.order_datetime) }}</p>
        </div>

        <div class="flex items-center gap-2">
          <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide" :class="statusClass(order?.status)">
            {{ order?.status || '-' }}
          </span>
          <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
            Delivery: {{ order?.delivery_status || 'N/A' }}
          </span>
          <button
            type="button"
            class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
            @click="refreshAll"
          >
            Refresh
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
      Loading order data...
    </div>

    <div v-else-if="!order" class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-700 shadow-sm dark:border-rose-800/60 dark:bg-rose-900/20 dark:text-rose-300">
      Order not found.
    </div>

    <template v-else>
      <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-1">
          <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-sm dark:border-slate-800/70 dark:bg-slate-900/85">
            <h2 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Order Details</h2>
            <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
              <p><span class="font-semibold text-slate-900 dark:text-white">Waybill:</span> {{ order.waybill_no || '-' }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Collectable:</span> LKR {{ toMoney(order.total_collectable_amount) }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Net Total:</span> LKR {{ toMoney(order.net_total) }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Delivery:</span> LKR {{ toMoney(order.delivery_charge) }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Discount:</span> LKR {{ toMoney(order.total_discount) }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Commission:</span> LKR {{ toMoney(order.commission_amount) }}</p>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
              <div class="rounded-lg bg-slate-100 px-2 py-1.5 font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">Items: {{ totalQty }}</div>
              <div class="rounded-lg bg-emerald-100 px-2 py-1.5 font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Due: LKR {{ toMoney(netDue) }}</div>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-sm dark:border-slate-800/70 dark:bg-slate-900/85">
            <h2 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Customer</h2>
            <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
              <p><span class="font-semibold text-slate-900 dark:text-white">Name:</span> {{ order.customer_name || order.customer?.default_name || '-' }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Phone:</span> {{ order.phone || order.customer?.primary_phone || '-' }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Additional:</span> {{ order.additional_phone || order.customer?.additional_phone || '-' }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Address:</span> {{ order.address || order.customer?.default_address || '-' }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">City:</span> {{ order.city?.name_en || '-' }}</p>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-sm dark:border-slate-800/70 dark:bg-slate-900/85">
            <h2 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Seller & Dispatch</h2>
            <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
              <p><span class="font-semibold text-slate-900 dark:text-white">Seller:</span> {{ sellerName(order.seller) }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Email:</span> {{ order.seller?.email || '-' }}</p>
              <p><span class="font-semibold text-slate-900 dark:text-white">Phone:</span> {{ order.seller?.phone || '-' }}</p>
              <p>
                <span class="font-semibold text-slate-900 dark:text-white">Dispatch Note:</span>
                <a
                  v-if="latestDispatchRef && latestDispatchId"
                  :href="`/admin/orders/dispatch-notes/${latestDispatchId}`"
                  class="rounded-md bg-sky-100 px-2 py-0.5 text-sky-700 hover:underline dark:bg-sky-900/30 dark:text-sky-300"
                >
                  {{ latestDispatchRef }}
                </a>
                <span v-else class="rounded-md bg-sky-100 px-2 py-0.5 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">-</span>
              </p>
            </div>
          </div>
        </div>

        <div class="space-y-4 lg:col-span-2">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Products</h2>
            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
              <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                  <tr>
                    <th class="px-3 py-2 text-left">Product</th>
                    <th class="px-3 py-2 text-left">SKU</th>
                    <th class="px-3 py-2 text-left">Variant</th>
                    <th class="px-3 py-2 text-right">Price</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-right">Line Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                  <tr v-if="!order.items?.length">
                    <td colspan="6" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No items found.</td>
                  </tr>
                  <tr v-for="item in order.items || []" :key="item.id" class="bg-white dark:bg-slate-900/40">
                    <td class="px-3 py-2">{{ item.product?.title || '-' }}</td>
                    <td class="px-3 py-2 font-mono text-xs">{{ item.variant?.sku || '-' }}</td>
                    <td class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">{{ variantText(item.variant?.attributes) }}</td>
                    <td class="px-3 py-2 text-right">LKR {{ toMoney(item.price) }}</td>
                    <td class="px-3 py-2 text-right">{{ item.quantity || 0 }}</td>
                    <td class="px-3 py-2 text-right font-semibold">LKR {{ toMoney((Number(item.price || 0) * Number(item.quantity || 0))) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Courier Label</h2>
                <button
                  type="button"
                  class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
                  @click="printLabel"
                >
                  Print Label
                </button>
              </div>

              <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs dark:border-slate-700 dark:bg-slate-800/60">
                <div class="grid gap-2 sm:grid-cols-2">
                  <p><span class="font-semibold">Waybill:</span> {{ waybillToShow }}</p>
                  <p><span class="font-semibold">Qty:</span> {{ totalQty }}</p>
                  <p><span class="font-semibold">Customer:</span> {{ order.customer_name || '-' }}</p>
                  <p><span class="font-semibold">Collectable:</span> LKR {{ toMoney(netDue) }}</p>
                </div>
                <p class="mt-2"><span class="font-semibold">Address:</span> {{ order.address || '-' }}</p>
                <p class="mt-1 truncate"><span class="font-semibold">Products:</span> {{ productSummary }}</p>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
              <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Delivery Timeline</h2>
                <button
                  type="button"
                  class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                  :disabled="timelineLoading"
                  @click="fetchTimeline(true)"
                >
                  {{ timelineLoading ? 'Refreshing...' : 'Refresh Timeline' }}
                </button>
              </div>

              <p v-if="timelineMessage" class="mt-2 text-xs text-amber-600 dark:text-amber-300">{{ timelineMessage }}</p>

              <div class="mt-4">
                <div v-if="timelineLoading" class="text-sm text-slate-500 dark:text-slate-400">Loading timeline...</div>
                <ul v-else-if="timeline.length" class="space-y-3">
                  <li v-for="(event, idx) in timeline" :key="`${idx}-${event.title}-${event.at || 'na'}`" class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
                    <div class="flex items-center justify-between gap-2">
                      <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ event.title }}</p>
                      <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
                        :class="event.source === 'courier' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : event.source === 'dispatch' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'">
                        {{ event.source }}
                      </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ formatDate(event.at) }}</p>
                    <p v-if="event.details" class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ event.details }}</p>
                  </li>
                </ul>
                <p v-else class="text-sm text-slate-500 dark:text-slate-400">No timeline events available.</p>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Order Activity Log</h2>
              <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                Latest on top
              </span>
            </div>

            <div class="mt-4">
              <ul v-if="orderLogs.length" class="space-y-3">
                <li
                  v-for="log in orderLogs"
                  :key="log.id"
                  class="relative rounded-xl border border-slate-200 bg-slate-50/80 p-3 pl-4 dark:border-slate-700 dark:bg-slate-800/50"
                >
                  <span class="absolute left-0 top-3 h-8 w-1 rounded-r-full bg-sky-500/70"></span>
                  <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ logEventTitle(log) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(log.created_at) }}</p>
                  </div>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">By {{ logActor(log) }}</p>
                  <p v-if="log.note" class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ log.note }}</p>
                  <p v-if="logSummary(log)" class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ logSummary(log) }}</p>
                  <div class="mt-2">
                    <button
                      type="button"
                      class="rounded-lg border border-slate-300 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700"
                      @click="toggleLog(log)"
                    >
                      {{ isLogExpanded(log.id) ? 'Hide Changes' : 'View Changes' }}
                    </button>

                    <div
                      v-if="isLogExpanded(log.id)"
                      class="mt-2 overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900/70"
                    >
                      <div v-if="isLogLoading(log.id)" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                        Loading changes...
                      </div>
                      <div v-else-if="logFetchError(log.id)" class="px-3 py-2 text-xs text-rose-600 dark:text-rose-300">
                        {{ logFetchError(log.id) }}
                      </div>
                      <table class="min-w-full divide-y divide-slate-200 text-xs dark:divide-slate-700">
                        <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                          <tr>
                            <th class="px-2 py-2 text-left">Field</th>
                            <th class="px-2 py-2 text-left">Old</th>
                            <th class="px-2 py-2 text-left">New</th>
                          </tr>
                        </thead>
                        <tbody v-if="!isLogLoading(log.id) && !logFetchError(log.id)" class="divide-y divide-slate-200 dark:divide-slate-700">
                          <tr v-for="row in logChangeRows(log)" :key="`${log.id}-${row.field}`">
                            <td class="px-2 py-1.5 font-semibold text-slate-700 dark:text-slate-200">{{ row.field }}</td>
                            <td class="px-2 py-1.5 text-slate-600 dark:text-slate-300">{{ formatChangeValue(row.oldValue) }}</td>
                            <td class="px-2 py-1.5 text-slate-600 dark:text-slate-300">{{ formatChangeValue(row.newValue) }}</td>
                          </tr>
                          <tr v-if="!logChangeRows(log).length">
                            <td colspan="3" class="px-2 py-2 text-center text-slate-500 dark:text-slate-400">No changed fields captured.</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </li>
              </ul>
              <p v-else class="text-sm text-slate-500 dark:text-slate-400">No order activity logs yet.</p>
            </div>
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
const expandedLogId = ref(null)
const logDetails = ref({})
const logLoading = ref({})
const logErrors = ref({})

const toMoney = (value) => Number(value || 0).toFixed(2)
const waybillToShow = computed(() => {
  const wb = String(order.value?.waybill_no || '').trim()
  if (wb !== '') return wb
  return `PKG${String(props.orderId).padStart(6, '0')}`
})

const totalQty = computed(() => {
  const items = order.value?.items || []
  return items.reduce((sum, item) => sum + Number(item?.quantity || 0), 0)
})

const netDue = computed(() => {
  const total = Number(order.value?.total_collectable_amount || 0)
  return total < 0 ? 0 : total
})

const productSummary = computed(() => {
  const rows = order.value?.items || []
  if (!rows.length) return '-'
  return rows
    .map((row) => `${row?.product?.title || 'Product'} x ${Number(row?.quantity || 0)}`)
    .join(', ')
})

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

const sellerName = (seller) => {
  if (!seller) return '-'
  const name = `${seller.first_name || ''} ${seller.last_name || ''}`.trim()
  return name || seller.email || `Seller #${seller.id}`
}

const statusClass = (status) => {
  const s = String(status || '').toLowerCase()
  if (s === 'draft') return 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:ring-slate-600'
  if (s === 'approved' || s === 'confirmed') return 'bg-blue-100 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:ring-blue-700/40'
  if (s === 'packed' || s === 'shipped') return 'bg-amber-100 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-700/40'
  if (s === 'completed') return 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-700/40'
  if (s === 'cancelled' || s === 'rejected') return 'bg-rose-100 text-rose-700 ring-1 ring-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-700/40'
  return 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:ring-slate-600'
}

const variantText = (attrs) => {
  if (!attrs || typeof attrs !== 'object') return '-'
  const text = Object.entries(attrs)
    .map(([k, v]) => `${k}:${typeof v === 'object' ? JSON.stringify(v) : String(v)}`)
    .join(', ')
  return text || '-'
}

const latestDispatchRef = computed(() => {
  const rows = order.value?.dispatch_note_items || []
  if (!rows.length) return null
  const latest = [...rows].sort((a, b) => Number(b.dispatch_note_id || 0) - Number(a.dispatch_note_id || 0))[0]
  return latest?.dispatch_note?.ref_no || null
})

const latestDispatchId = computed(() => {
  const rows = order.value?.dispatch_note_items || []
  if (!rows.length) return null
  const latest = [...rows].sort((a, b) => Number(b.dispatch_note_id || 0) - Number(a.dispatch_note_id || 0))[0]
  return latest?.dispatch_note?.id || latest?.dispatch_note_id || null
})

const orderLogs = computed(() => {
  const rows = Array.isArray(order.value?.logs) ? [...order.value.logs] : []
  return rows.sort((a, b) => {
    const aTs = a?.created_at ? new Date(a.created_at).getTime() : 0
    const bTs = b?.created_at ? new Date(b.created_at).getTime() : 0
    return bTs - aTs
  })
})

const logEventTitle = (log) => {
  const type = String(log?.event_type || '').toLowerCase()
  if (type === 'status_changed') {
    const from = log?.from_status || 'unknown'
    const to = log?.to_status || 'unknown'
    return `Status changed: ${from} -> ${to}`
  }
  if (type === 'created') return 'Order created'
  if (type === 'updated') return 'Order updated'
  return log?.event_type || 'Order event'
}

const logActor = (log) => {
  return log?.user?.name || log?.user?.email || 'System'
}

const logSummary = (log) => {
  const changes = logDetails.value[log?.id]?.changes
  if (!changes || typeof changes !== 'object') return ''

  const keys = Object.keys(changes).filter((k) => k !== 'updated_at')
  if (!keys.length) return ''

  if (keys.length <= 4) return `Changed: ${keys.join(', ')}`
  return `Changed: ${keys.slice(0, 4).join(', ')} +${keys.length - 4} more`
}

const toggleLog = async (log) => {
  const logId = Number(log?.id || 0)
  if (!logId) return

  if (expandedLogId.value === logId) {
    expandedLogId.value = null
    return
  }

  expandedLogId.value = logId
  if (!logDetails.value[logId]) {
    await fetchLogChanges(logId)
  }
}

const isLogExpanded = (logId) => expandedLogId.value === logId
const isLogLoading = (logId) => Boolean(logLoading.value[logId])
const logFetchError = (logId) => logErrors.value[logId] || ''

const fetchLogChanges = async (logId) => {
  logLoading.value = { ...logLoading.value, [logId]: true }
  logErrors.value = { ...logErrors.value, [logId]: '' }

  try {
    const { data } = await axios.get(`/api/admin/orders/${props.orderId}/logs/${logId}`)
    const log = data?.log || null
    if (!log) {
      throw new Error('Order log not found.')
    }

    logDetails.value = {
      ...logDetails.value,
      [logId]: log,
    }
  } catch (error) {
    const message = error?.response?.data?.message || 'Failed to load log changes.'
    logErrors.value = { ...logErrors.value, [logId]: message }
  } finally {
    logLoading.value = { ...logLoading.value, [logId]: false }
  }
}

const logChangeRows = (log) => {
  const changes = logDetails.value[log?.id]?.changes
  if (!changes || typeof changes !== 'object') return []

  return Object.entries(changes)
    .filter(([field]) => field !== 'updated_at')
    .map(([field, value]) => {
      if (value && typeof value === 'object' && Object.prototype.hasOwnProperty.call(value, 'old') && Object.prototype.hasOwnProperty.call(value, 'new')) {
        return {
          field,
          oldValue: value.old,
          newValue: value.new,
        }
      }

      return {
        field,
        oldValue: null,
        newValue: value,
      }
    })
}

const formatChangeValue = (value) => {
  if (value === null || value === undefined || value === '') return '-'
  if (typeof value === 'object') {
    try {
      return JSON.stringify(value)
    } catch {
      return String(value)
    }
  }
  return String(value)
}

const fetchOrder = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/admin/orders/${props.orderId}`)
    order.value = data?.order || null
    expandedLogId.value = null
    logDetails.value = {}
    logLoading.value = {}
    logErrors.value = {}
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
    const { data } = await axios.get(`/api/admin/orders/${props.orderId}/delivery-timeline`, {
      params: { refresh: refresh ? 1 : 0 },
    })
    timeline.value = Array.isArray(data?.timeline) ? data.timeline : []
    if (data?.courier_message) {
      timelineMessage.value = data.courier_message
    }
    if (order.value) {
      order.value.delivery_status = data?.delivery_status || order.value.delivery_status
    }
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load delivery timeline.')
  } finally {
    timelineLoading.value = false
  }
}

const refreshAll = async () => {
  await Promise.all([fetchOrder(), fetchTimeline(true)])
}

const escapeHtml = (value) => {
  const str = String(value ?? '')
  return str
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')
}

const printLabel = () => {
  if (!order.value) return

  const printWindow = window.open('', '', 'width=920,height=1200')
  if (!printWindow) {
    toast.error('Popup blocked. Please allow popups for printing.')
    return
  }

  const waybill = escapeHtml(waybillToShow.value)
  const customerName = escapeHtml(order.value?.customer_name || '-')
  const phone = escapeHtml(order.value?.phone || '-')
  const additionalPhone = escapeHtml(order.value?.additional_phone || '')
  const address = escapeHtml(order.value?.address || '-')
  const products = (order.value?.items || [])
    .map((item) => `<div>${escapeHtml(item?.product?.title || 'Product')} x ${Number(item?.quantity || 0)}</div>`)
    .join('')
  const paidAmount = Number(order.value?.paid_amount || 0)
  const totalDue = Math.max(Number(netDue.value || 0) - paidAmount, 0)

  const html = `
  <html>
    <head>
      <title>Order Label #${escapeHtml(order.value?.id || props.orderId)}</title>
      <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
      <style>
        html, body {
          width: 10cm;
          height: 10cm;
          margin: 0;
          padding: 0;
          background: #fff;
          font-family: Arial, sans-serif;
        }

        @page {
          size: 10cm 10cm;
          margin: 0;
          bleed: 0;
        }

        .lable-outer-box {
          padding: 20px;
          width: 10cm;
          height: 10cm;
          box-sizing: border-box;
        }

        .label-box {
          height: 100%;
          box-sizing: border-box;
          border: 1px solid #000;
          margin: 0;
          font-size: 9px;
          line-height: 1.2;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          overflow: hidden;
          position: relative;
        }

        .label-header {
          display: flex;
          padding: 6px;
          height: 15%;
          justify-content: space-between;
          font-size: 12px;
          font-weight: 600;
        }

        .barcode-block {
          display: flex;
          align-items: stretch;
          justify-content: space-between;
          height: 25%;
        }

        .barcode-left {
          flex: 4;
          border: 1px solid #000;
          padding: 0.1cm;
          text-align: center;
          overflow: hidden;
          display: flex;
          font-size: 8px;
          flex-direction: column;
          justify-content: center;
        }

        .barcode-left svg {
          width: 100% !important;
          height: 60px !important;
        }

        .barcode-right {
          flex: 1;
          border: 1px solid #000;
          text-align: center;
          font-size: 11px;
          font-weight: bold;
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-self: stretch;
          background: #f9f9f9;
        }

        .barcode-number {
          font-size: 24px !important;
        }

        .first-line {
          border: 1px solid #000;
          padding: 0.1cm;
          font-size: 12px;
          font-weight: 700;
          min-height: 14%;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          text-align: center;
          word-break: break-word;
        }

        .table-top {
          width: 100%;
          border-collapse: collapse;
          font-size: 12px;
          border: 1px solid #000;
        }

        .table-top th {
          border-right: 1px solid #000;
          padding: 3px 5px;
          text-align: left;
          font-weight: 600;
        }

        .table-top th:last-child {
          border-right: none;
          text-align: right;
        }

        .table-bottom {
          width: 100%;
          height: 100%;
          border-collapse: collapse;
          font-size: 10px;
          border: 1px solid #000;
        }

        .table-bottom td {
          border: 1px solid #000;
          padding: 3px 5px;
          vertical-align: top;
        }

        .table-bottom .customer {
          width: 60%;
          font-size: 11px;
          line-height: 1.35;
          overflow-wrap: anywhere;
          vertical-align: middle;
          padding: 6px 5px;
        }

        .table-bottom .label {
          width: 25%;
          text-align: right;
          font-weight: 500;
        }

        .table-bottom .value {
          width: 15%;
          text-align: right;
          font-weight: 500;
        }

        .table-bottom .total-row td {
          font-weight: 700;
          background: #f7f7f7;
        }

        .text-right { text-align: right; }

        @media print {
          body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
          }
        }
      </style>
    </head>
    <body>
      <div class="lable-outer-box">
        <div class="label-box">
          <div class="label-header">
            <div>
              <strong>Name:</strong> Nextep Sellers<br/>
              <strong>Address:</strong> Seller Fulfillment Center
            </div>
            <div class="text-right">
              <p><strong>Date:</strong> ${escapeHtml(formatDate(order.value?.order_datetime))}</p>
              <p><strong>Waybill:</strong> ${waybill}</p>
            </div>
          </div>

          <div class="barcode-block">
            <div class="barcode-left">
              <svg id="wb-barcode"></svg>
              <div>${waybill}</div>
            </div>
            <div class="barcode-right">
              <div>Qty</div>
              <div class="barcode-number">${Number(totalQty.value || 0)}</div>
            </div>
          </div>

          <div class="first-line">
            ${products || '<div>-</div>'}
          </div>

          <table class="table-top">
            <tr>
              <th>Customer Details</th>
              <th>Order Total</th>
              <th class="text-right">${escapeHtml(toMoney(order.value?.net_total || 0))}</th>
            </tr>
          </table>

          <table class="table-bottom">
            <tr>
              <td rowspan="5" class="customer">
                <strong>Name:</strong> ${customerName}<br/>
                <strong>Address:</strong> ${address}<br/>
                <strong>Contacts:</strong> ${phone}${additionalPhone ? ` | ${additionalPhone}` : ''}
              </td>
              <td class="label">Delivery Cost</td>
              <td class="value text-right">${escapeHtml(toMoney(order.value?.delivery_charge || 0))}</td>
            </tr>
            <tr>
              <td class="label">Discount (-)</td>
              <td class="value text-right">${escapeHtml(toMoney(order.value?.total_discount || 0))}</td>
            </tr>
            <tr>
              <td class="label">Paid (-)</td>
              <td class="value text-right">${escapeHtml(toMoney(paidAmount))}</td>
            </tr>
            <tr class="total-row">
              <td class="label">Total Due</td>
              <td class="value text-right">${escapeHtml(toMoney(totalDue))}</td>
            </tr>
          </table>
        </div>
      </div>
      <script>
        try {
          if (window.JsBarcode) {
            JsBarcode('#wb-barcode', '${waybill}', { format: 'CODE128', width: 1.5, height: 45, displayValue: false, margin: 0 });
          }
        } catch (e) {}
        setTimeout(() => { window.print(); window.close(); }, 250);
      <\/script>
    </body>
  </html>`

  printWindow.document.open()
  printWindow.document.write(html)
  printWindow.document.close()
}

refreshAll()
</script>
