<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-amber-600 dark:text-amber-300">Orders</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Packed Orders</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-packed-orders"
        @filters-changed="onGlobalFiltersChanged"
      />
    </div>

    <div class="mt-4 flex items-center justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} packed orders</p>
      <button
        type="button"
        class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
        @click="fetchOrders"
      >
        Refresh
      </button>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">Order</th>
            <th class="px-3 py-3 text-left">Customer</th>
            <th class="px-3 py-3 text-left">Seller</th>
            <th class="px-3 py-3 text-left">Waybill</th>
            <th class="px-3 py-3 text-left">Products</th>
            <th class="px-3 py-3 text-left">Dispatch</th>
            <th class="px-3 py-3 text-left">Packed At</th>
            <th class="px-3 py-3 text-right">Collectable</th>
            <th class="px-3 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading packed orders...</td>
          </tr>
          <tr v-else-if="!orders.length">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No packed orders found.</td>
          </tr>

          <tr v-for="order in orders" :key="order.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3 align-top font-semibold text-slate-900 dark:text-white">
              <p>#{{ order.id }}</p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ order.items?.length || 0 }} item line(s)</p>
            </td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">
              <p>{{ order.customer_name || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ order.phone || '-' }}</p>
              <p v-if="order.city?.name_en" class="text-xs text-slate-500 dark:text-slate-400">{{ order.city.name_en }}</p>
            </td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">
              {{ sellerName(order.seller) }}
            </td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">
              <span class="font-mono text-xs">{{ order.waybill_no || '-' }}</span>
            </td>
            <td class="px-3 py-3 align-top">
              <div class="space-y-2">
                <div
                  v-for="item in order.items || []"
                  :key="item.id"
                  class="rounded-lg border border-slate-200 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-800/60"
                >
                  <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                    {{ item.product?.title || 'Product' }}
                  </p>
                  <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500 dark:text-slate-300">
                    <span>SKU: <span class="font-mono">{{ item.variant?.sku || '-' }}</span></span>
                    <span>|</span>
                    <span>Qty: <span class="font-semibold">{{ item.quantity || 0 }}</span></span>
                  </div>
                  <p v-if="variantText(item)" class="mt-1 text-[11px] text-slate-500 dark:text-slate-300">
                    {{ variantText(item) }}
                  </p>
                </div>
              </div>
            </td>
            <td class="px-3 py-3 align-top">
              <span
                class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold"
                :class="order.is_dispatched
                  ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                  : 'bg-slate-100 text-slate-700 dark:bg-slate-700/60 dark:text-slate-300'"
              >
                {{ order.is_dispatched ? 'Added to Dispatch' : 'Not in Dispatch' }}
              </span>
              <p v-if="order.dispatch_note?.ref_no" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                <a :href="`/admin/orders/dispatch-notes/${order.dispatch_note.id}`" class="font-semibold text-blue-600 hover:underline dark:text-blue-300">
                  {{ order.dispatch_note.ref_no }}
                </a>
              </p>
            </td>
            <td class="px-3 py-3 align-top text-slate-600 dark:text-slate-300">
              {{ formatDate(order.packed_at) }}
            </td>
            <td class="px-3 py-3 align-top text-right font-semibold text-slate-900 dark:text-white">
              LKR {{ toMoney(order.total_collectable_amount) }}
            </td>
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
  page: 1,
  per_page: 20,
})

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

const stringifyAttrValue = (value) => {
  if (value === null || value === undefined) return ''
  if (typeof value === 'object') {
    return Object.entries(value)
      .map(([k, v]) => `${k}=${stringifyAttrValue(v)}`)
      .filter(Boolean)
      .join(' | ')
  }
  return String(value)
}

const variantText = (item) => {
  const attrs = item?.variant?.attributes || {}
  return Object.entries(attrs)
    .map(([k, v]) => `${k}:${stringifyAttrValue(v)}`)
    .filter((row) => !row.endsWith(':'))
    .join(', ')
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/orders/packed', {
      params: {
        search: filters.search || undefined,
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
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load packed orders.')
  } finally {
    loading.value = false
  }
}

const onGlobalFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.seller_id = payload?.seller_id || null
  filters.page = 1
  fetchOrders()
}

const changePage = (page) => {
  filters.page = page
  fetchOrders()
}

fetchOrders()
</script>
