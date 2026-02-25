<template>
  <div class="space-y-4 rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">My Orders</p>
        <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">Submitted Orders</h2>
      </div>

      <div class="grid gap-2 sm:grid-cols-2">
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

    <div v-else class="space-y-2">
      <article
        v-for="order in orders"
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
            <button
              type="button"
              class="rounded-lg bg-blue-600 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-blue-700"
              @click="openDrawer(order)"
            >
              View
            </button>
          </div>
        </div>

        <div class="mt-3 grid gap-2 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-4">
          <p><span class="font-semibold">Net:</span> LKR {{ toMoney(order.net_total) }}</p>
          <p><span class="font-semibold">Delivery:</span> LKR {{ toMoney(order.delivery_charge) }}</p>
          <p><span class="font-semibold">Discount:</span> LKR {{ toMoney(order.total_discount) }}</p>
          <p><span class="font-semibold">Collectable:</span> LKR {{ toMoney(order.total_collectable_amount) }}</p>
        </div>
      </article>
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

    <div v-if="drawerOpen" class="fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/35" @click="closeDrawer"></div>
      <aside class="absolute right-0 top-0 h-full w-full max-w-2xl overflow-y-auto border-l border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-950">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Order Details</p>
            <h3 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">Order #{{ selectedOrder?.id }}</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ formatDate(selectedOrder?.order_datetime) }}</p>
          </div>

          <button
            type="button"
            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            @click="closeDrawer"
          >
            Close
          </button>
        </div>

        <div v-if="selectedOrder" class="mt-4 space-y-4">
          <section class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Customer</h4>
            <div class="mt-2 grid gap-2 text-sm text-slate-700 dark:text-slate-200">
              <p><span class="font-semibold">Name:</span> {{ selectedOrder.customer_name || '-' }}</p>
              <p><span class="font-semibold">Phone:</span> {{ selectedOrder.phone || '-' }}</p>
              <p><span class="font-semibold">Additional Phone:</span> {{ selectedOrder.additional_phone || '-' }}</p>
              <p><span class="font-semibold">City:</span> {{ selectedOrder.city?.name_en || '-' }}</p>
              <p><span class="font-semibold">Address:</span> {{ selectedOrder.address || '-' }}</p>
              <p><span class="font-semibold">Notes:</span> {{ selectedOrder.customer?.notes || '-' }}</p>
            </div>
          </section>

          <section class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Order Info</h4>
            <div class="mt-2 grid gap-2 text-sm text-slate-700 dark:text-slate-200 sm:grid-cols-2">
              <p><span class="font-semibold">Status:</span> {{ selectedOrder.status }}</p>
              <p><span class="font-semibold">Waybill:</span> {{ selectedOrder.waybill_no || '-' }}</p>
              <p><span class="font-semibold">Delivery Status:</span> {{ selectedOrder.delivery_status || '-' }}</p>
              <p><span class="font-semibold">Payment Status:</span> {{ selectedOrder.payment_status || '-' }}</p>
              <p><span class="font-semibold">Is Draft:</span> {{ selectedOrder.is_draft ? 'Yes' : 'No' }}</p>
              <p><span class="font-semibold">Damaged:</span> {{ selectedOrder.is_damaged ? 'Yes' : 'No' }}</p>
              <p><span class="font-semibold">Packed At:</span> {{ formatDate(selectedOrder.packed_at) }}</p>
              <p><span class="font-semibold">Shipped At:</span> {{ formatDate(selectedOrder.shipped_at) }}</p>
              <p><span class="font-semibold">Completed At:</span> {{ formatDate(selectedOrder.completed_at) }}</p>
              <p><span class="font-semibold">Cancelled At:</span> {{ formatDate(selectedOrder.cancelled_at) }}</p>
            </div>
          </section>

          <section class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Items</h4>
            <div class="mt-3 space-y-2">
              <article
                v-for="item in selectedOrder.items || []"
                :key="item.id"
                class="rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-700"
              >
                <p class="font-semibold text-slate-900 dark:text-white">{{ item.product?.title || 'Product' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  Product ID: {{ item.product_id }} · Variant: {{ item.variant?.sku || item.product_variant_id || '-' }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" v-if="item.variant?.attributes">
                  {{ attributeText(item.variant.attributes) }}
                </p>
                <div class="mt-2 grid gap-2 text-xs text-slate-700 dark:text-slate-200 sm:grid-cols-3">
                  <p><span class="font-semibold">Qty:</span> {{ item.quantity }}</p>
                  <p><span class="font-semibold">Price:</span> LKR {{ toMoney(item.price) }}</p>
                  <p><span class="font-semibold">Line Total:</span> LKR {{ toMoney(Number(item.quantity || 0) * Number(item.price || 0)) }}</p>
                </div>
              </article>
            </div>
          </section>

          <section class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Totals</h4>
            <div class="mt-2 grid gap-2 text-sm text-slate-700 dark:text-slate-200 sm:grid-cols-2">
              <p><span class="font-semibold">Net:</span> LKR {{ toMoney(selectedOrder.net_total) }}</p>
              <p><span class="font-semibold">Delivery:</span> LKR {{ toMoney(selectedOrder.delivery_charge) }}</p>
              <p><span class="font-semibold">Discount:</span> LKR {{ toMoney(selectedOrder.total_discount) }}</p>
              <p><span class="font-semibold">Commission:</span> LKR {{ toMoney(selectedOrder.commission_amount) }}</p>
              <p class="sm:col-span-2 text-base font-bold text-slate-900 dark:text-white">
                Collectable: LKR {{ toMoney(selectedOrder.total_collectable_amount) }}
              </p>
            </div>
          </section>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const orders = ref([])
const drawerOpen = ref(false)
const selectedOrder = ref(null)

const filters = reactive({
  status: 'all',
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

const attributeText = (attributes) => {
  if (!attributes || typeof attributes !== 'object') return ''

  return Object.entries(attributes)
    .map(([key, meta]) => {
      const label = String(meta?.label || meta?.value || '').trim()
      return label ? `${key}: ${label}` : ''
    })
    .filter(Boolean)
    .join(', ')
}

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'completed') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'cancelled') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  if (value === 'shipped' || value === 'packed') return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
  if (value === 'confirmed' || value === 'approved') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/seller/orders', {
      params: {
        status: filters.status,
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

const openDrawer = (order) => {
  selectedOrder.value = order
  drawerOpen.value = true
}

const closeDrawer = () => {
  drawerOpen.value = false
  selectedOrder.value = null
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
