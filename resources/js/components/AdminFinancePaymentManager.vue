<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-cyan-600 dark:text-cyan-300">Finance</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Payment Manager</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-finance-payment-manager"
        @filters-changed="onGlobalFiltersChanged"
      />
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      <article class="rounded-xl border border-cyan-200 bg-cyan-50/70 p-3 dark:border-cyan-500/30 dark:bg-cyan-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Sellers with Available Payments</p>
        <p class="mt-1 text-lg font-bold text-cyan-800 dark:text-cyan-200">{{ summary.sellers_count }}</p>
      </article>
      <article class="rounded-xl border border-sky-200 bg-sky-50/70 p-3 dark:border-sky-500/30 dark:bg-sky-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Available Order Count</p>
        <p class="mt-1 text-lg font-bold text-sky-800 dark:text-sky-200">{{ summary.available_orders_count }}</p>
      </article>
      <article class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Total Commission Value</p>
        <p class="mt-1 text-lg font-bold text-emerald-800 dark:text-emerald-200">LKR {{ toMoney(summary.total_commission_value) }}</p>
      </article>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} seller records</p>
      <div class="flex flex-wrap items-end gap-3">
          <label class="space-y-1">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-600 mr-3 dark:text-slate-300">Filter By</span>
            <select
              v-model="filters.date_type"
              class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @change="onDateTypeChanged"
            >
              <option value="order_datetime">Order Date</option>
              <option value="completed_at">Completed Date</option>
            </select>
          </label>
          <button
            type="button"
            class="rounded-xl bg-emerald-700 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="generatingGlobal || loadingSellers"
            @click="openGlobalGenerateConfirm"
          >
            {{ generatingGlobal ? 'Generating...' : 'Generate Invoices' }}
          </button>
            <button
            type="button"
            class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
            @click="fetchSellers"
          >
            Refresh
          </button>
        </div>
      
      
   
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">Seller</th>
            <th class="px-3 py-3 text-left">Contact</th>
            <th class="px-3 py-3 text-left">Level</th>
            <th class="px-3 py-3 text-right">Available Orders</th>
            <th class="px-3 py-3 text-right">Total Commission</th>
            <th class="px-3 py-3 text-center">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loadingSellers">
            <td colspan="6" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading sellers...</td>
          </tr>
          <tr v-else-if="!sellers.length">
            <td colspan="6" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No sellers with available payments found.</td>
          </tr>

          <tr v-for="seller in sellers" :key="seller.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3">
              <p class="font-semibold text-slate-900 dark:text-white">{{ sellerName(seller) }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">Seller #{{ seller.id }}</p>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
              <p>{{ seller.email || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ seller.phone || '-' }}</p>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
              {{ seller?.level?.level_name || '-' }}
            </td>
            <td class="px-3 py-3 text-right font-semibold text-cyan-700 dark:text-cyan-300">{{ Number(seller.available_orders_count || 0) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(seller.available_commission_value) }}</td>
            <td class="px-3 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <button
                  type="button"
                  class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
                  @click="openSellerOrders(seller)"
                >
                  View
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="isGeneratingSeller(seller.id)"
                  @click="openSellerGenerateConfirm(seller)"
                >
                  {{ isGeneratingSeller(seller.id) ? 'Generating...' : 'Generate Invoice' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="meta.current_page <= 1 || loadingSellers"
        @click="changePage(meta.current_page - 1)"
      >
        Previous
      </button>
      <p class="text-xs text-slate-500 dark:text-slate-400">Page {{ meta.current_page }} of {{ meta.last_page }}</p>
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="meta.current_page >= meta.last_page || loadingSellers"
        @click="changePage(meta.current_page + 1)"
      >
        Next
      </button>
    </div>

    <div v-if="drawerOpen" class="fixed inset-0 z-[80] bg-slate-900/35" @click="closeDrawer"></div>

    <transition name="slide-right">
      <aside v-if="drawerOpen" class="fixed right-0 top-0 z-[1000] flex h-full w-full max-w-none flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900 lg:w-[70vw]">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Eligible Orders</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ drawerSellerLabel }}</p>
          </div>
          <button
            type="button"
            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            @click="closeDrawer"
          >
            Close
          </button>
        </div>

        <div class="grid gap-3 border-b border-slate-200 p-5 dark:border-slate-700 md:grid-cols-3">
          <article class="rounded-xl border border-cyan-200 bg-cyan-50/70 p-3 dark:border-cyan-500/30 dark:bg-cyan-500/10">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Available Orders</p>
            <p class="mt-1 text-lg font-bold text-cyan-800 dark:text-cyan-200">{{ drawerSummary.order_count }}</p>
          </article>
          <article class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Total Commission</p>
            <p class="mt-1 text-lg font-bold text-emerald-800 dark:text-emerald-200">LKR {{ toMoney(drawerSummary.total_commission_value) }}</p>
          </article>
          <label class="space-y-1">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Search Order</span>
            <input
              v-model.trim="drawer.search"
              type="text"
              placeholder="Order id, customer, phone"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @keyup.enter="applyDrawerFilter"
            />
          </label>
        </div>

        <div class="flex-1 overflow-y-auto p-5">
          <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
              <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                <tr>
                  <th class="px-3 py-3 text-left">Order</th>
                  <th class="px-3 py-3 text-left">Customer</th>
                  <th class="px-3 py-3 text-left">Order Date</th>
                  <th class="px-3 py-3 text-left">Completed</th>
                  <th class="px-3 py-3 text-right">Net Sale</th>
                  <th class="px-3 py-3 text-right">Delivery</th>
                  <th class="px-3 py-3 text-right">Commission</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <tr v-if="drawer.loading">
                  <td colspan="7" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading orders...</td>
                </tr>
                <tr v-else-if="!drawer.orders.length">
                  <td colspan="7" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No eligible orders found for this seller.</td>
                </tr>
                <tr v-for="order in drawer.orders" :key="order.id" class="bg-white dark:bg-slate-900/40">
                  <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">#{{ order.id }}</td>
                  <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
                    <p>{{ order.customer_name || '-' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ order.phone || '-' }}</p>
                  </td>
                  <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(order.order_datetime) }}</td>
                  <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(order.completed_at) }}</td>
                  <td class="px-3 py-3 text-right font-semibold text-cyan-700 dark:text-cyan-300">LKR {{ toMoney(order.net_sale_amount) }}</td>
                  <td class="px-3 py-3 text-right font-semibold text-sky-700 dark:text-sky-300">LKR {{ toMoney(order.delivery_charge) }}</td>
                  <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(order.commission_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex items-center justify-between">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              :disabled="drawer.meta.current_page <= 1 || drawer.loading"
              @click="changeDrawerPage(drawer.meta.current_page - 1)"
            >
              Previous
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">Page {{ drawer.meta.current_page }} of {{ drawer.meta.last_page }}</p>
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              :disabled="drawer.meta.current_page >= drawer.meta.last_page || drawer.loading"
              @click="changeDrawerPage(drawer.meta.current_page + 1)"
            >
              Next
            </button>
          </div>
        </div>
      </aside>
    </transition>

    <div v-if="confirmModal.open" class="fixed inset-0 z-[1100] flex items-center justify-center bg-slate-900/45 p-4">
      <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ confirmModal.title }}</h3>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ confirmModal.message }}</p>

        <div class="mt-5 flex items-center justify-end gap-2">
          <button
            type="button"
            class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            @click="closeConfirmModal"
          >
            Cancel
          </button>
          <button
            type="button"
            class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-800"
            @click="confirmAndRun"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loadingSellers = ref(false)
const generatingGlobal = ref(false)
const generatingSellerIds = ref([])
const sellers = ref([])

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
  sellers_count: 0,
  available_orders_count: 0,
  total_commission_value: 0,
})

const confirmModal = reactive({
  open: false,
  title: '',
  message: '',
  payload: null,
})

const drawerOpen = ref(false)
const drawerSeller = ref(null)

const drawer = reactive({
  loading: false,
  search: '',
  orders: [],
  meta: {
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
  },
})

const drawerSummary = reactive({
  order_count: 0,
  total_commission_value: 0,
})

const toMoney = (value) => Number(value || 0).toFixed(2)
const isGeneratingSeller = (sellerId) => generatingSellerIds.value.includes(Number(sellerId))

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

const drawerSellerLabel = computed(() => {
  if (!drawerSeller.value) return ''
  return `${sellerName(drawerSeller.value)} (${drawerSeller.value.email || '-'})`
})

const fetchSellers = async () => {
  loadingSellers.value = true
  try {
    const { data } = await axios.get('/api/admin/finance/payment-manager/sellers', {
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

    sellers.value = Array.isArray(data?.sellers) ? data.sellers : []
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
    meta.total = Number(data?.meta?.total || 0)
    summary.sellers_count = Number(data?.summary?.sellers_count || 0)
    summary.available_orders_count = Number(data?.summary?.available_orders_count || 0)
    summary.total_commission_value = Number(data?.summary?.total_commission_value || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load sellers.')
  } finally {
    loadingSellers.value = false
  }
}

const fetchDrawerOrders = async () => {
  if (!drawerSeller.value) return

  drawer.loading = true
  try {
    const { data } = await axios.get(`/api/admin/finance/payment-manager/sellers/${drawerSeller.value.id}/orders`, {
      params: {
        search: drawer.search || undefined,
        date_type: filters.date_type || 'order_datetime',
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        page: drawer.meta.current_page,
        per_page: drawer.meta.per_page,
      },
    })

    drawer.orders = Array.isArray(data?.orders) ? data.orders : []
    drawer.meta.current_page = Number(data?.meta?.current_page || 1)
    drawer.meta.last_page = Number(data?.meta?.last_page || 1)
    drawer.meta.per_page = Number(data?.meta?.per_page || drawer.meta.per_page)
    drawer.meta.total = Number(data?.meta?.total || 0)

    drawerSummary.order_count = Number(data?.summary?.order_count || 0)
    drawerSummary.total_commission_value = Number(data?.summary?.total_commission_value || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load eligible orders.')
  } finally {
    drawer.loading = false
  }
}

const applyFilters = () => {
  filters.page = 1
  fetchSellers()

  if (drawerOpen.value) {
    drawer.meta.current_page = 1
    fetchDrawerOrders()
  }
}

const onGlobalFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.seller_id = payload?.seller_id || null
  applyFilters()
}

const onDateTypeChanged = () => {
  applyFilters()
}

const changePage = (page) => {
  filters.page = page
  fetchSellers()
}

const openSellerOrders = (seller) => {
  drawerSeller.value = seller
  drawer.search = ''
  drawer.orders = []
  drawer.meta.current_page = 1
  drawer.meta.last_page = 1
  drawer.meta.total = 0
  drawerSummary.order_count = 0
  drawerSummary.total_commission_value = 0
  drawerOpen.value = true
  fetchDrawerOrders()
}

const applyDrawerFilter = () => {
  drawer.meta.current_page = 1
  fetchDrawerOrders()
}

const changeDrawerPage = (page) => {
  drawer.meta.current_page = page
  fetchDrawerOrders()
}

const closeDrawer = () => {
  drawerOpen.value = false
}

const baseInvoiceFilterPayload = () => ({
  search: filters.search || undefined,
  seller_id: filters.seller_id || undefined,
  date_type: filters.date_type || 'order_datetime',
  date_from: filters.date_from || undefined,
  date_to: filters.date_to || undefined,
})

const openGlobalGenerateConfirm = () => {
  confirmModal.open = true
  confirmModal.title = 'Generate Invoices'
  confirmModal.message = 'Generate draft invoices for all eligible filtered sellers?'
  confirmModal.payload = {
    type: 'global',
  }
}

const generateGlobalInvoices = async () => {
  generatingGlobal.value = true
  try {
    const { data } = await axios.post('/api/admin/finance/payment-manager/generate-invoices', baseInvoiceFilterPayload())
    toast.success(data?.message || 'Invoices generated successfully.')
    fetchSellers()
    if (drawerOpen.value) {
      fetchDrawerOrders()
    }
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to generate invoices.')
  } finally {
    generatingGlobal.value = false
  }
}

const openSellerGenerateConfirm = (seller) => {
  confirmModal.open = true
  confirmModal.title = 'Generate Seller Invoice'
  confirmModal.message = `Generate draft invoice for ${sellerName(seller)}?`
  confirmModal.payload = {
    type: 'seller',
    seller,
  }
}

const generateSellerInvoice = async (seller) => {
  const sellerId = Number(seller?.id || 0)
  if (!sellerId) return

  generatingSellerIds.value = [...generatingSellerIds.value, sellerId]
  try {
    const { data } = await axios.post(`/api/admin/finance/payment-manager/sellers/${sellerId}/generate-invoice`, {
      date_type: filters.date_type || 'order_datetime',
      date_from: filters.date_from || undefined,
      date_to: filters.date_to || undefined,
    })
    toast.success(data?.message || 'Invoice generated successfully.')
    fetchSellers()
    if (drawerOpen.value && Number(drawerSeller.value?.id || 0) === sellerId) {
      fetchDrawerOrders()
    }
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to generate seller invoice.')
  } finally {
    generatingSellerIds.value = generatingSellerIds.value.filter((id) => id !== sellerId)
  }
}

const closeConfirmModal = () => {
  confirmModal.open = false
  confirmModal.title = ''
  confirmModal.message = ''
  confirmModal.payload = null
}

const confirmAndRun = async () => {
  const payload = confirmModal.payload
  closeConfirmModal()

  if (!payload?.type) return
  if (payload.type === 'global') {
    await generateGlobalInvoices()
    return
  }
  if (payload.type === 'seller') {
    await generateSellerInvoice(payload.seller)
  }
}
</script>

<style scoped>
.slide-right-enter-active,
.slide-right-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.slide-right-enter-from,
.slide-right-leave-to {
  transform: translateX(24px);
  opacity: 0;
}
</style>
