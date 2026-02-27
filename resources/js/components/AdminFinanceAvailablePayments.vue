<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-cyan-600 dark:text-cyan-300">Finance</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Available Payment Orders</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-finance-available-payments"
        @filters-changed="onGlobalFiltersChanged"
      />
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <article class="rounded-xl border border-cyan-200 bg-cyan-50/70 p-3 dark:border-cyan-500/30 dark:bg-cyan-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Available Order Value</p>
        <p class="mt-1 text-lg font-bold text-cyan-800 dark:text-cyan-200">LKR {{ toMoney(summary.order_value) }}</p>
      </article>
      <article class="rounded-xl border border-sky-200 bg-sky-50/70 p-3 dark:border-sky-500/30 dark:bg-sky-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Available Delivery Charges</p>
        <p class="mt-1 text-lg font-bold text-sky-800 dark:text-sky-200">LKR {{ toMoney(summary.delivery_charge_value) }}</p>
      </article>
      <article class="rounded-xl border border-amber-200 bg-amber-50/70 p-3 dark:border-amber-500/30 dark:bg-amber-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Available Net Sale</p>
        <p class="mt-1 text-lg font-bold text-amber-800 dark:text-amber-200">LKR {{ toMoney(summary.net_sale_value) }}</p>
      </article>
      <article class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Available Commission Value</p>
        <p class="mt-1 text-lg font-bold text-emerald-800 dark:text-emerald-200">LKR {{ toMoney(summary.commission_value) }}</p>
      </article>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} available payment orders</p>
     
       <div class="flex flex-wrap items-end gap-3">
          <label class="space-y-1">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-600 mr-3 dark:text-slate-300">Filter By</span>
            <select
              v-model="filters.date_type"
              class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @change="onFilterChanged"
            >
              <option value="order_datetime">Order Date</option>
              <option value="completed_at">Completed Date</option>
            </select>
          </label>

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
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-right">Net Sale</th>
            <th class="px-3 py-3 text-right">Delivery Charge</th>
            <th class="px-3 py-3 text-right">Order Value</th>
            <th class="px-3 py-3 text-right">Commission</th>
            <th class="px-3 py-3 text-left">Created</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading available payments...</td>
          </tr>
          <tr v-else-if="!orders.length">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No available payment orders found.</td>
          </tr>

          <tr v-for="order in orders" :key="order.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">#{{ order.id }}</td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
              <p>{{ order.customer_name || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ order.phone || '-' }}</p>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ sellerName(order.seller) }}</td>
            <td class="px-3 py-3">
              <span class="rounded-full bg-cyan-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300">
                {{ order.payment_status }}
              </span>
            </td>
            <td class="px-3 py-3 text-right font-semibold text-amber-700 dark:text-amber-300">LKR {{ toMoney(order.net_sale_amount) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-sky-700 dark:text-sky-300">LKR {{ toMoney(order.delivery_charge) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(order.total_collectable_amount) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(order.commission_amount) }}</td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(order.order_datetime) }}</td>
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
  date_type: 'order_datetime',
  date_from: '',
  date_to: '',
  seller_id: null,
  page: 1,
  per_page: 20,
})

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const summary = reactive({
  order_value: 0,
  commission_value: 0,
  delivery_charge_value: 0,
  net_sale_value: 0,
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

const fetchOrders = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/finance/available-payments', {
      params: {
        search: filters.search || undefined,
        date_type: filters.date_type || 'order_datetime',
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        page: filters.page,
        per_page: filters.per_page,
      },
    })

    orders.value = Array.isArray(data?.orders) ? data.orders : []
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
    meta.total = Number(data?.meta?.total || 0)

    summary.order_value = Number(data?.summary?.order_value || 0)
    summary.commission_value = Number(data?.summary?.commission_value || 0)
    summary.delivery_charge_value = Number(data?.summary?.delivery_charge_value || 0)
    summary.net_sale_value = Number(data?.summary?.net_sale_value || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load available payment orders.')
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
  filters.seller_id = payload?.seller_id || null
  onFilterChanged()
}

const changePage = (page) => {
  filters.page = page
  fetchOrders()
}
</script>
