<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Orders</p>
          <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">My Orders</h1>
        </div>

        <a
          href="/seller/orders/create"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700"
        >
          <i class="fas fa-plus" aria-hidden="true"></i>
          Create Order
        </a>
      </div>

      <admin-global-filter-bar
        context-key="seller-my-orders"
        :show-seller-filter="false"
        search-placeholder="Search order/customer/phone/waybill"
        @filters-changed="onGlobalFiltersChanged"
      />

      <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
        <select
          v-model="filters.status"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
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
          <option value="rejected">Rejected</option>
        </select>

        <select
          v-model="filters.payment_status"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @change="onFilterChanged"
        >
          <option value="all">All Payments</option>
          <option value="pending">Pending</option>
          <option value="available">Available</option>
          <option value="paid">Paid</option>
        </select>

        <select
          v-model.number="filters.per_page"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @change="onFilterChanged"
        >
          <option :value="10">10 per page</option>
          <option :value="20">20 per page</option>
          <option :value="30">30 per page</option>
          <option :value="50">50 per page</option>
        </select>

        <button
          type="button"
          class="ml-auto rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
          @click="fetchOrders"
        >
          Refresh
        </button>
      </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
      <p class="text-xs text-slate-500 dark:text-slate-400">
        Showing <span class="font-semibold text-slate-900 dark:text-white">{{ paginationFrom }}</span>
        to <span class="font-semibold text-slate-900 dark:text-white">{{ paginationTo }}</span>
        of <span class="font-semibold text-slate-900 dark:text-white">{{ meta.total }}</span> orders
      </p>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">Order</th>
            <th class="px-3 py-3 text-left">Customer</th>
            <th class="px-3 py-3 text-left">Products</th>
            <th class="px-3 py-3 text-left">City</th>
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-left">Payment</th>
            <th class="px-3 py-3 text-left">Waybill</th>
            <th class="px-3 py-3 text-right">Collectable</th>
            <th class="px-3 py-3 text-right">Earnings</th>
            <th class="px-3 py-3 text-left">Created</th>
            <th class="px-3 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="11" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading orders...</td>
          </tr>
          <tr v-else-if="!orders.length">
            <td colspan="11" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No orders found.</td>
          </tr>

          <tr v-for="order in orders" :key="order.id" class="bg-white align-top dark:bg-slate-900/40">
            <td class="px-3 py-3">
              <a :href="`/seller/orders/${order.id}`" class="font-bold text-blue-600 hover:underline dark:text-blue-300">#{{ order.id }}</a>
              <p v-if="order.bulk_order_request?.request_no" class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                {{ order.bulk_order_request.request_no }}
              </p>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
              <p class="font-semibold text-slate-900 dark:text-white">{{ order.customer_name || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ order.phone || '-' }}</p>
            </td>
            <td class="px-3 py-3">
              <div class="max-w-72 space-y-1.5">
                <div
                  v-for="item in visibleItems(order)"
                  :key="item.id"
                  class="rounded-lg border border-slate-200 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-800/60"
                >
                  <p class="truncate text-xs font-semibold text-slate-800 dark:text-slate-100">{{ item.product?.title || 'Product' }}</p>
                  <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-300">
                    SKU: <span class="font-mono">{{ item.variant?.sku || '-' }}</span>
                    <span class="mx-1">|</span>
                    Qty: <span class="font-semibold">{{ item.quantity || 0 }}</span>
                  </p>
                </div>
                <p v-if="(order.items || []).length > 2" class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                  +{{ (order.items || []).length - 2 }} more
                </p>
              </div>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ order.city?.name_en || '-' }}</td>
            <td class="px-3 py-3">
              <span :class="statusClass(order.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ order.status || '-' }}</span>
            </td>
            <td class="px-3 py-3">
              <span :class="paymentClass(order.payment_status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ order.payment_status || 'pending' }}</span>
            </td>
            <td class="px-3 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ order.waybill_no || '-' }}</td>
            <td class="px-3 py-3 text-right font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(order.total_collectable_amount) }}</td>
            <td class="px-3 py-3 text-right">
              <p class="font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(commissionAmount(order)) }}</p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ pointsEarned(order) }} pts</p>
            </td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(order.order_datetime) }}</td>
            <td class="px-3 py-3 text-right">
              <a
                :href="`/seller/orders/${order.id}`"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              >
                View
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Page {{ meta.current_page }} of {{ meta.last_page }}</p>

      <div class="flex flex-wrap items-center gap-1">
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="meta.current_page <= 1 || loading"
          @click="changePage(meta.current_page - 1)"
        >
          Prev
        </button>

        <button
          v-for="page in pageNumbers"
          :key="page"
          type="button"
          class="h-8 min-w-8 rounded-lg border px-2 text-xs font-semibold transition"
          :class="page === meta.current_page
            ? 'border-blue-600 bg-blue-600 text-white'
            : 'border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800'"
          :disabled="loading"
          @click="changePage(page)"
        >
          {{ page }}
        </button>

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
  </section>
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
  date_from: '',
  date_to: '',
  page: 1,
  per_page: 20,
})

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const paginationFrom = computed(() => {
  if (!meta.total) return 0
  return ((meta.current_page - 1) * meta.per_page) + 1
})

const paginationTo = computed(() => {
  if (!meta.total) return 0
  return Math.min(meta.current_page * meta.per_page, meta.total)
})

const pageNumbers = computed(() => {
  const total = meta.last_page || 1
  const current = meta.current_page || 1
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  const pages = []

  for (let page = start; page <= end; page += 1) {
    pages.push(page)
  }

  return pages
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

const visibleItems = (order) => (order?.items || []).slice(0, 2)

const commissionAmount = (order) => {
  return Number(order?.computed_commission_amount ?? order?.commission_amount ?? 0)
}

const pointsEarned = (order) => {
  return Number(order?.computed_points_earned ?? 0)
}

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'completed') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'cancelled' || value === 'rejected') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  if (value === 'shipped' || value === 'packed') return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
  if (value === 'confirmed' || value === 'approved') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

const paymentClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'paid') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'available') return 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/seller/orders', {
      params: {
        status: filters.status,
        payment_status: filters.payment_status,
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
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

const onGlobalFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  onFilterChanged()
}

const changePage = (page) => {
  if (page < 1 || page > meta.last_page || page === meta.current_page) return
  filters.page = page
  fetchOrders()
}

const onOrderCreated = () => {
  filters.page = 1
  fetchOrders()
}

onMounted(() => {
  window.addEventListener('seller-order-created', onOrderCreated)
})

onBeforeUnmount(() => {
  window.removeEventListener('seller-order-created', onOrderCreated)
})
</script>
