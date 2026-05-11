<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-rose-600 dark:text-rose-300">Orders</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Cancelled Orders</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-cancelled-orders"
        @filters-changed="onGlobalFiltersChanged"
      />
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} cancelled orders</p>
      <div class="flex items-center gap-2">
        <select
          v-model="filters.date_basis"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
          @change="applyFilters"
        >
          <option value="cancelled_date">Cancelled Date</option>
          <option value="order_date">Order Date</option>
        </select>
        <button
          type="button"
          class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
          @click="fetchOrders"
        >
          Refresh
        </button>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">Order</th>
            <th class="px-3 py-3 text-left">Customer</th>
            <th class="px-3 py-3 text-left">Seller</th>
            <th class="px-3 py-3 text-left">Waybill</th>
            <th class="px-3 py-3 text-left">Dispatch</th>
            <th class="px-3 py-3 text-left">Order Date</th>
            <th class="px-3 py-3 text-left">Cancelled Date</th>
            <th class="px-3 py-3 text-right">Collectable</th>
            <th class="px-3 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading cancelled orders...</td>
          </tr>
          <tr v-else-if="!orders.length">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No cancelled orders found.</td>
          </tr>

          <tr v-for="order in orders" :key="order.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3 align-top font-semibold text-slate-900 dark:text-white">
              <p>#{{ order.id }}</p>
              <span class="mt-1 inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                {{ order.status }}
              </span>
            </td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">
              <p>{{ order.customer_name || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ order.phone || '-' }}</p>
              <p v-if="order.city?.name_en" class="text-xs text-slate-500 dark:text-slate-400">{{ order.city.name_en }}</p>
            </td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">{{ sellerName(order.seller) }}</td>
            <td class="px-3 py-3 align-top font-mono text-xs text-slate-700 dark:text-slate-200">{{ order.waybill_no || '-' }}</td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">
              <a
                v-if="order.dispatch_note?.id"
                :href="`/admin/orders/dispatch-notes/${order.dispatch_note.id}`"
                class="font-semibold text-blue-600 hover:underline dark:text-blue-300"
              >
                {{ order.dispatch_note.ref_no }}
              </a>
              <span v-else>-</span>
            </td>
            <td class="px-3 py-3 align-top text-slate-600 dark:text-slate-300">{{ formatDate(order.order_datetime) }}</td>
            <td class="px-3 py-3 align-top text-slate-600 dark:text-slate-300">{{ formatDate(order.cancelled_at) }}</td>
            <td class="px-3 py-3 align-top text-right font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(order.total_collectable_amount) }}</td>
            <td class="px-3 py-3 align-top text-right">
              <a
                :href="`/admin/orders/${order.id}`"
                class="rounded-lg border border-slate-300 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              >
                View
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
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
  </section>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const orders = ref([])

const filters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  seller_id: null,
  date_basis: 'cancelled_date',
  page: 1,
  per_page: 20,
})

const storageKey = 'nextep-preferences-admin-cancelled-orders'

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const toMoney = (value) => Number(value || 0).toFixed(2)

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

const savePreferences = () => {
  window.localStorage.setItem(storageKey, JSON.stringify({
    date_basis: filters.date_basis,
  }))
}

const loadPreferences = () => {
  try {
    const raw = window.localStorage.getItem(storageKey)
    if (!raw) return
    const parsed = JSON.parse(raw)
    if (parsed?.date_basis === 'order_date' || parsed?.date_basis === 'cancelled_date') {
      filters.date_basis = parsed.date_basis
    }
  } catch {
    // ignore preference load errors
  }
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/orders/cancelled', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        date_basis: filters.date_basis,
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
    toast.error(error?.response?.data?.message || 'Failed to load cancelled orders.')
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  filters.page = 1
  savePreferences()
  fetchOrders()
}

const onGlobalFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.seller_id = payload?.seller_id || null
  applyFilters()
}

const changePage = (page) => {
  filters.page = page
  fetchOrders()
}

loadPreferences()
</script>
