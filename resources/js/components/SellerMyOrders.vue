<template>
  <div class="space-y-4 rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">My Orders</p>
        <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">Submitted Orders</h2>
      </div>

      <div class="grid gap-2 sm:grid-cols-3">
        <select
          v-model="filters.status"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @change="onFilterChanged"
        >
          <option value="all">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="approved">Approved</option>
          <option value="confirmed">Confirmed</option>
          <option value="packed">Packed</option>
          <option value="shipped">Shipped</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <select
          v-model="filters.payment_status"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @change="onFilterChanged"
        >
          <option value="all">All Payments</option>
          <option value="pending">Pending</option>
          <option value="available">Available</option>
          <option value="paid">Paid</option>
        </select>

        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="Search by order, customer, phone"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @keyup.enter="onFilterChanged"
        />
      </div>
    </div>

    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
      <p>Total {{ meta.total }} orders</p>
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        @click="fetchOrders"
      >
        Refresh
      </button>
    </div>

    <div v-if="loading" class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
      Loading orders...
    </div>

    <div v-else-if="!orders.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
      No orders found.
    </div>

    <div v-else class="space-y-4">
      <section v-for="group in bulkGroups" :key="`group-${group.id}`" class="rounded-xl border border-blue-200/70 bg-blue-50/40 p-3 dark:border-blue-500/20 dark:bg-blue-500/5">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">Bulk Order Request</p>
            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ group.request_no }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ formatDate(group.created_at) }}</p>
          </div>
          <div class="flex items-center gap-2">
            <span :class="bulkStatusClass(group.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ group.status }}</span>
            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">
              {{ group.orders.length }} order{{ group.orders.length === 1 ? '' : 's' }}
            </span>
          </div>
        </div>

        <div class="space-y-2">
          <article
            v-for="order in group.orders"
            :key="order.id"
            class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-950/50"
          >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">#{{ order.id }} · {{ order.customer_name || 'Unknown Customer' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  {{ formatDate(order.order_datetime) }} · {{ order.phone || '-' }} · {{ order.city?.name_en || 'No city' }}
                </p>
              </div>

              <div class="flex items-center gap-2">
                <span :class="statusClass(order.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ order.status }}</span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                  {{ itemCount(order) }} item{{ itemCount(order) === 1 ? '' : 's' }}
                </span>
                <a
                  :href="`/seller/orders/${order.id}`"
                  class="rounded-lg bg-blue-600 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-blue-700"
                >
                  View
                </a>
              </div>
            </div>

            <div class="mt-3 grid gap-2 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-4">
              <p><span class="font-semibold">Net:</span> LKR {{ toMoney(order.net_total) }}</p>
              <p><span class="font-semibold">Delivery:</span> LKR {{ toMoney(order.delivery_charge) }}</p>
              <p><span class="font-semibold">Commission:</span> LKR {{ toMoney(commissionAmount(order)) }}</p>
              <p><span class="font-semibold">Points Earned:</span> {{ pointsEarned(order) }}</p>
            </div>
          </article>
        </div>
      </section>

      <section v-if="singleOrders.length" class="space-y-2">
        <div class="flex items-center justify-between">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Single Orders</p>
          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">
            {{ singleOrders.length }} order{{ singleOrders.length === 1 ? '' : 's' }}
          </span>
        </div>

        <article
          v-for="order in singleOrders"
          :key="order.id"
          class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-950/50"
        >
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-sm font-semibold text-slate-900 dark:text-white">#{{ order.id }} · {{ order.customer_name || 'Unknown Customer' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ formatDate(order.order_datetime) }} · {{ order.phone || '-' }} · {{ order.city?.name_en || 'No city' }}
              </p>
            </div>

            <div class="flex items-center gap-2">
              <span :class="statusClass(order.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ order.status }}</span>
              <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                {{ itemCount(order) }} item{{ itemCount(order) === 1 ? '' : 's' }}
              </span>
              <a
                :href="`/seller/orders/${order.id}`"
                class="rounded-lg bg-blue-600 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-blue-700"
              >
                View
              </a>
            </div>
          </div>

          <div class="mt-3 grid gap-2 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-4">
            <p><span class="font-semibold">Net:</span> LKR {{ toMoney(order.net_total) }}</p>
            <p><span class="font-semibold">Delivery:</span> LKR {{ toMoney(order.delivery_charge) }}</p>
            <p><span class="font-semibold">Commission:</span> LKR {{ toMoney(commissionAmount(order)) }}</p>
            <p><span class="font-semibold">Points Earned:</span> {{ pointsEarned(order) }}</p>
          </div>
        </article>
      </section>
    </div>

    <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="meta.current_page <= 1 || loading"
        @click="changePage(meta.current_page - 1)"
      >
        Previous
      </button>

      <p class="text-xs text-slate-500 dark:text-slate-400">Page {{ meta.current_page }} of {{ meta.last_page }}</p>

      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="meta.current_page >= meta.last_page || loading"
        @click="changePage(meta.current_page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const orders = ref([])

const filters = reactive({
  status: 'all',
  payment_status: 'all',
  search: '',
  page: 1,
  per_page: 10,
})

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

const itemCount = (order) => {
  return (order?.items || []).reduce((sum, row) => sum + Number(row.quantity || 0), 0)
}

const commissionAmount = (order) => {
  return Number(order?.computed_commission_amount ?? order?.commission_amount ?? 0)
}

const pointsEarned = (order) => {
  return Number(order?.computed_points_earned ?? 0)
}

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'completed') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'cancelled') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  if (value === 'shipped' || value === 'packed') return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
  if (value === 'confirmed' || value === 'approved') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

const bulkStatusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'submitted') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  if (value === 'approved') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'cancelled') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
}

const bulkGroups = computed(() => {
  const map = new Map()

  for (const order of orders.value) {
    const bulk = order?.bulk_order_request
    if (!bulk?.id) continue

    if (!map.has(bulk.id)) {
      map.set(bulk.id, {
        id: Number(bulk.id),
        request_no: bulk.request_no || `Bulk #${bulk.id}`,
        status: bulk.status || 'draft',
        created_at: bulk.created_at || order.order_datetime || null,
        orders: [],
      })
    }

    map.get(bulk.id).orders.push(order)
  }

  return Array.from(map.values())
    .map((group) => ({
      ...group,
      orders: group.orders.sort((a, b) => Number(b.id || 0) - Number(a.id || 0)),
    }))
    .sort((a, b) => Number(b.id || 0) - Number(a.id || 0))
})

const singleOrders = computed(() => {
  return orders.value
    .filter((order) => !order?.bulk_order_request?.id)
    .sort((a, b) => Number(b.id || 0) - Number(a.id || 0))
})

const fetchOrders = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/seller/orders', {
      params: {
        status: filters.status,
        payment_status: filters.payment_status,
        search: filters.search || undefined,
        page: filters.page,
        per_page: filters.per_page,
      },
    })

    orders.value = Array.isArray(data?.orders) ? data.orders : []
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
    meta.total = Number(data?.meta?.total || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load orders.')
  } finally {
    loading.value = false
  }
}

const onFilterChanged = () => {
  filters.page = 1
  fetchOrders()
}

const changePage = (page) => {
  filters.page = page
  fetchOrders()
}

const onOrderCreated = () => {
  filters.page = 1
  fetchOrders()
}

onMounted(() => {
  fetchOrders()
  window.addEventListener('seller-order-created', onOrderCreated)
})

onBeforeUnmount(() => {
  window.removeEventListener('seller-order-created', onOrderCreated)
})
</script>
