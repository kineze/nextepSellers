<template>
  <div class="mx-3 mt-3 mb-8 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-300">Sellers</p>
        <h2 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Seller Profile</h2>
      </div>
      <a href="/active-sellers" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
        Back to Active Sellers
      </a>
    </div>

    <div v-if="seller" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="border-b border-slate-200 px-4 pt-4 dark:border-slate-800">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            class="rounded-t-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide transition"
            :class="activeTab === tab.key ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
            @click="setActiveTab(tab.key)"
          >
            <i :class="tab.icon" class="mr-2" aria-hidden="true"></i>{{ tab.label }}
          </button>
        </div>
      </div>

      <section v-if="activeTab === 'overview'" class="p-5">
        <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
          <aside class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Profile Image</p>
            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
              <img v-if="seller.seller_image" :src="storageUrl(seller.seller_image)" alt="Seller image" class="h-64 w-full object-cover" />
              <div v-else class="flex h-64 w-full flex-col items-center justify-center bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                <i class="fas fa-image text-3xl"></i>
                <p class="mt-3 text-sm font-semibold">No image uploaded</p>
              </div>
            </div>

            <div class="mt-4">
              <label class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-black dark:bg-white dark:text-slate-900">
                <i class="fas fa-upload"></i>
                {{ imageUploading ? 'Uploading...' : (seller.seller_image ? 'Update Image' : 'Upload Image') }}
                <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp" :disabled="imageUploading" @change="uploadSellerImage" />
              </label>
              <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">JPG, PNG or WEBP up to 5MB.</p>
            </div>
          </aside>

          <div class="space-y-5">
            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
              <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Seller Overview</p>
                  <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ sellerName }}</h3>
                </div>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide" :class="sellerStatusClass(seller.status)">
                  {{ seller.status }}
                </span>
              </div>

              <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div class="detail-tile"><p class="detail-label">Email</p><p class="detail-value">{{ seller.email }}</p></div>
                <div class="detail-tile"><p class="detail-label">Phone</p><p class="detail-value">{{ seller.phone || '-' }}</p></div>
                <div class="detail-tile"><p class="detail-label">Seller Type</p><p class="detail-value capitalize">{{ seller.seller_type || '-' }}</p></div>
                <div class="detail-tile"><p class="detail-label">Tax Number</p><p class="detail-value">{{ seller.tax_number || '-' }}</p></div>
                <div class="detail-tile"><p class="detail-label">NIC Number</p><p class="detail-value">{{ seller.nic_number || '-' }}</p></div>
                <div class="detail-tile"><p class="detail-label">Level</p><p class="detail-value">{{ seller.level?.level_name || '-' }}</p></div>
                <div class="detail-tile"><p class="detail-label">Points</p><p class="detail-value">{{ seller.points ?? 0 }}</p></div>
                <div class="detail-tile"><p class="detail-label">First Success Order</p><p class="detail-value">{{ formatDateOnly(seller.first_success_order_date) }}</p></div>
                <div class="detail-tile"><p class="detail-label">Linked User ID</p><p class="detail-value">{{ seller.user_id || '-' }}</p></div>
              </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
              <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Business Information</h3>
              <div v-if="seller.business_information" class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="detail-tile"><p class="detail-label">Business Name</p><p class="detail-value">{{ seller.business_information.business_name || '-' }}</p></div>
                <div class="detail-tile"><p class="detail-label">Business Reg No</p><p class="detail-value">{{ seller.business_information.business_registration_number || '-' }}</p></div>
                <div class="detail-tile"><p class="detail-label">Business Type</p><p class="detail-value">{{ seller.business_information.business_type || '-' }}</p></div>
                <div class="detail-tile"><p class="detail-label">City</p><p class="detail-value">{{ seller.business_information.city || '-' }}</p></div>
                <div class="detail-tile sm:col-span-2"><p class="detail-label">Address</p><p class="detail-value">{{ businessAddress }}</p></div>
                <div class="detail-tile"><p class="detail-label">Country</p><p class="detail-value">{{ seller.business_information.country || '-' }}</p></div>
                <div class="detail-tile"><p class="detail-label">Postal Code</p><p class="detail-value">{{ seller.business_information.postal_code || '-' }}</p></div>
              </div>
              <p v-else class="mt-3 text-sm text-slate-500 dark:text-slate-300">No business information available.</p>
            </div>
          </div>
        </div>
      </section>

      <section v-else-if="activeTab === 'orders'" class="space-y-4 p-5">
        <admin-global-filter-bar
          :context-key="`seller-profile-orders-${sellerId}`"
          :show-seller-filter="false"
          default-date-preset="year"
          search-placeholder="Search order, customer, phone, waybill"
          @filters-changed="onOrderFiltersChanged"
        />

        <div class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <button
            v-for="status in orderStatuses"
            :key="status.value"
            type="button"
            class="rounded-full px-3 py-1.5 text-xs font-semibold uppercase tracking-wide transition"
            :class="orderFilters.status === status.value ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'"
            @click="setOrderStatus(status.value)"
          >
            {{ status.label }} <span class="ml-1 opacity-70">{{ statusCount(orderSummary.status_counts, status.value) }}</span>
          </button>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
          <summary-card label="Total Orders" :value="orderSummary.total_orders" tone="sky" />
          <summary-card label="Collectable" :value="`LKR ${toMoney(orderSummary.total_collectable_amount)}`" tone="emerald" />
          <summary-card label="Commission" :value="`LKR ${toMoney(orderSummary.total_commission_amount)}`" tone="cyan" />
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
          <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
              <tr>
                <th class="px-3 py-3 text-left">Order</th>
                <th class="px-3 py-3 text-left">Customer</th>
                <th class="px-3 py-3 text-left">Waybill</th>
                <th class="px-3 py-3 text-left">Date</th>
                <th class="px-3 py-3 text-left">Status</th>
                <th class="px-3 py-3 text-right">Collectable</th>
                <th class="px-3 py-3 text-right">Commission</th>
                <th class="px-3 py-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr v-if="ordersLoading"><td colspan="8" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading orders...</td></tr>
              <tr v-else-if="!orders.length"><td colspan="8" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No orders found.</td></tr>
              <tr v-for="order in orders" :key="order.id" class="bg-white dark:bg-slate-900/40">
                <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">#{{ order.id }}<p class="text-xs text-slate-500">{{ order.items_count }} item line(s)</p></td>
                <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ order.customer_name || '-' }}<p class="text-xs text-slate-500">{{ order.phone || '-' }}</p></td>
                <td class="px-3 py-3 font-mono text-xs text-slate-700 dark:text-slate-200">{{ order.waybill_no || '-' }}</td>
                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(order.order_datetime) }}</td>
                <td class="px-3 py-3"><span :class="orderStatusClass(order.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ order.status }}</span><p class="mt-1 text-xs text-slate-500">{{ order.payment_status || '-' }}</p></td>
                <td class="px-3 py-3 text-right font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(order.total_collectable_amount) }}</td>
                <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(order.commission_amount) }}</td>
                <td class="px-3 py-3 text-right"><a :href="`/admin/orders/${order.id}`" class="rounded-lg border border-slate-300 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View</a></td>
              </tr>
            </tbody>
          </table>
        </div>

        <pagination-controls :meta="orderMeta" :loading="ordersLoading" @page="changeOrderPage" />
      </section>

      <section v-else class="space-y-4 p-5">
        <admin-global-filter-bar
          :context-key="`seller-profile-invoices-${sellerId}`"
          :show-seller-filter="false"
          default-date-preset="year"
          search-placeholder="Search invoice ID"
          @filters-changed="onInvoiceFiltersChanged"
        />

        <div class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <button
            v-for="status in invoiceStatuses"
            :key="status.value"
            type="button"
            class="rounded-full px-3 py-1.5 text-xs font-semibold uppercase tracking-wide transition"
            :class="invoiceFilters.status === status.value ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'"
            @click="setInvoiceStatus(status.value)"
          >
            {{ status.label }} <span class="ml-1 opacity-70">{{ statusCount(invoiceSummary.status_counts, status.value) }}</span>
          </button>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
          <summary-card label="Invoice Count" :value="invoiceSummary.invoice_count" tone="sky" />
          <summary-card label="Total Commission" :value="`LKR ${toMoney(invoiceSummary.total_commission_value)}`" tone="emerald" />
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
          <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
              <tr>
                <th class="px-3 py-3 text-left">Invoice</th>
                <th class="px-3 py-3 text-left">Date</th>
                <th class="px-3 py-3 text-left">Time</th>
                <th class="px-3 py-3 text-right">Orders</th>
                <th class="px-3 py-3 text-right">Commission</th>
                <th class="px-3 py-3 text-left">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr v-if="invoicesLoading"><td colspan="6" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading invoices...</td></tr>
              <tr v-else-if="!invoices.length"><td colspan="6" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No invoices found.</td></tr>
              <tr v-for="invoice in invoices" :key="invoice.id" class="bg-white dark:bg-slate-900/40">
                <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">#{{ invoice.id }}</td>
                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDateOnly(invoice.invoice_date) }}</td>
                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ invoice.invoice_time || '-' }}</td>
                <td class="px-3 py-3 text-right font-semibold text-cyan-700 dark:text-cyan-300">{{ invoice.orders_count || 0 }}</td>
                <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(invoice.total_commission_value) }}</td>
                <td class="px-3 py-3"><span :class="invoiceStatusClass(invoice.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ invoice.status }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <pagination-controls :meta="invoiceMeta" :loading="invoicesLoading" @page="changeInvoicePage" />
      </section>
    </div>

    <div v-else class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
      Loading seller profile...
    </div>
  </div>
</template>

<script setup>
import { computed, h, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
  sellerId: {
    type: Number,
    required: true,
  },
})

const SummaryCard = {
  props: {
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    tone: { type: String, default: 'sky' },
  },
  setup(cardProps) {
    const tones = {
      sky: 'border-sky-200 bg-sky-50/70 text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200',
      emerald: 'border-emerald-200 bg-emerald-50/70 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200',
      cyan: 'border-cyan-200 bg-cyan-50/70 text-cyan-800 dark:border-cyan-500/30 dark:bg-cyan-500/10 dark:text-cyan-200',
    }
    return () => h('article', { class: `rounded-xl border p-3 ${tones[cardProps.tone] || tones.sky}` }, [
      h('p', { class: 'text-[11px] font-semibold uppercase tracking-wide opacity-80' }, cardProps.label),
      h('p', { class: 'mt-1 text-lg font-bold' }, String(cardProps.value)),
    ])
  },
}

const PaginationControls = {
  props: {
    meta: { type: Object, required: true },
    loading: { type: Boolean, default: false },
  },
  emits: ['page'],
  setup(paginationProps, { emit }) {
    return () => h('div', { class: 'mt-4 flex items-center justify-between' }, [
      h('button', {
        type: 'button',
        class: 'rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800',
        disabled: Number(paginationProps.meta.current_page || 1) <= 1 || paginationProps.loading,
        onClick: () => emit('page', Number(paginationProps.meta.current_page || 1) - 1),
      }, 'Previous'),
      h('p', { class: 'text-xs text-slate-500 dark:text-slate-400' }, `Page ${paginationProps.meta.current_page || 1} of ${paginationProps.meta.last_page || 1}`),
      h('button', {
        type: 'button',
        class: 'rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800',
        disabled: Number(paginationProps.meta.current_page || 1) >= Number(paginationProps.meta.last_page || 1) || paginationProps.loading,
        onClick: () => emit('page', Number(paginationProps.meta.current_page || 1) + 1),
      }, 'Next'),
    ])
  },
}

const toast = useToast()
const seller = ref(null)
const imageUploading = ref(false)
const activeTab = ref('overview')

const tabs = [
  { key: 'overview', label: 'Overview', icon: 'fas fa-user' },
  { key: 'orders', label: 'Orders', icon: 'fas fa-box' },
  { key: 'invoices', label: 'Invoices', icon: 'fas fa-file-invoice-dollar' },
]

const orderStatuses = [
  { value: 'all', label: 'All' },
  { value: 'draft', label: 'Draft' },
  { value: 'approved', label: 'Approved' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'packed', label: 'Packed' },
  { value: 'shipped', label: 'Shipped' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
  { value: 'rejected', label: 'Rejected' },
]

const invoiceStatuses = [
  { value: 'all', label: 'All' },
  { value: 'draft', label: 'Draft' },
  { value: 'paid', label: 'Paid' },
  { value: 'cancelled', label: 'Cancelled' },
]

const orders = ref([])
const ordersLoading = ref(false)
const orderFilters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  status: 'all',
  page: 1,
  per_page: 20,
})
const orderSummary = reactive({
  total_orders: 0,
  total_collectable_amount: 0,
  total_commission_amount: 0,
  status_counts: {},
})
const orderMeta = reactive({ current_page: 1, last_page: 1, per_page: 20, total: 0 })

const invoices = ref([])
const invoicesLoading = ref(false)
const invoiceFilters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  status: 'all',
  page: 1,
  per_page: 20,
})
const invoiceSummary = reactive({
  invoice_count: 0,
  total_commission_value: 0,
  status_counts: {},
})
const invoiceMeta = reactive({ current_page: 1, last_page: 1, per_page: 20, total: 0 })

const sellerName = computed(() => {
  if (!seller.value) return ''
  return `${seller.value.first_name || ''} ${seller.value.last_name || ''}`.trim() || seller.value.email || `Seller #${props.sellerId}`
})

const businessAddress = computed(() => {
  const info = seller.value?.business_information
  if (!info) return '-'
  return [info.address_line_1, info.address_line_2].filter(Boolean).join(', ') || '-'
})

const setActiveTab = (tab) => {
  activeTab.value = tab
  if (tab === 'orders' && !orders.value.length) fetchOrders()
  if (tab === 'invoices' && !invoices.value.length) fetchInvoices()
}

const fetchSeller = async () => {
  try {
    const res = await axios.get(`/api/sellers/${props.sellerId}`)
    seller.value = res.data
  } catch {
    toast.error('Failed to load seller profile')
  }
}

const fetchOrders = async () => {
  ordersLoading.value = true
  try {
    const { data } = await axios.get(`/api/sellers/${props.sellerId}/orders`, {
      params: {
        search: orderFilters.search || undefined,
        date_from: orderFilters.date_from || undefined,
        date_to: orderFilters.date_to || undefined,
        status: orderFilters.status === 'all' ? undefined : orderFilters.status,
        page: orderFilters.page,
        per_page: orderFilters.per_page,
      },
    })
    orders.value = Array.isArray(data?.orders) ? data.orders : []
    syncObject(orderSummary, data?.summary || {})
    syncObject(orderMeta, data?.meta || {})
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load seller orders.')
  } finally {
    ordersLoading.value = false
  }
}

const fetchInvoices = async () => {
  invoicesLoading.value = true
  try {
    const { data } = await axios.get(`/api/sellers/${props.sellerId}/invoices`, {
      params: {
        search: invoiceFilters.search || undefined,
        date_from: invoiceFilters.date_from || undefined,
        date_to: invoiceFilters.date_to || undefined,
        status: invoiceFilters.status === 'all' ? undefined : invoiceFilters.status,
        page: invoiceFilters.page,
        per_page: invoiceFilters.per_page,
      },
    })
    invoices.value = Array.isArray(data?.invoices) ? data.invoices : []
    syncObject(invoiceSummary, data?.summary || {})
    syncObject(invoiceMeta, data?.meta || {})
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load seller invoices.')
  } finally {
    invoicesLoading.value = false
  }
}

const syncObject = (target, source) => {
  Object.keys(target).forEach((key) => {
    if (Object.prototype.hasOwnProperty.call(source, key)) {
      target[key] = source[key]
    }
  })
}

const onOrderFiltersChanged = (payload) => {
  orderFilters.search = payload?.search || ''
  orderFilters.date_from = payload?.date_from || ''
  orderFilters.date_to = payload?.date_to || ''
  orderFilters.page = 1
  fetchOrders()
}

const onInvoiceFiltersChanged = (payload) => {
  invoiceFilters.search = payload?.search || ''
  invoiceFilters.date_from = payload?.date_from || ''
  invoiceFilters.date_to = payload?.date_to || ''
  invoiceFilters.page = 1
  fetchInvoices()
}

const setOrderStatus = (status) => {
  orderFilters.status = status
  orderFilters.page = 1
  fetchOrders()
}

const setInvoiceStatus = (status) => {
  invoiceFilters.status = status
  invoiceFilters.page = 1
  fetchInvoices()
}

const changeOrderPage = (page) => {
  orderFilters.page = page
  fetchOrders()
}

const changeInvoicePage = (page) => {
  invoiceFilters.page = page
  fetchInvoices()
}

const uploadSellerImage = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  const formData = new FormData()
  formData.append('seller_image', file)

  try {
    imageUploading.value = true
    const res = await axios.post(`/api/sellers/${props.sellerId}/image`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    seller.value = res.data.seller
    toast.success(res.data.message || 'Seller image uploaded successfully')
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error(err.response?.data?.message || 'Failed to upload seller image')
    }
  } finally {
    imageUploading.value = false
    event.target.value = ''
  }
}

const storageUrl = (path) => `/storage/${path}`
const toMoney = (value) => Number(value || 0).toFixed(2)

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString()
}

const formatDateOnly = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value).slice(0, 10)
  return date.toLocaleDateString()
}

const statusCount = (counts, status) => {
  if (status === 'all') {
    return Object.values(counts || {}).reduce((sum, value) => sum + Number(value || 0), 0)
  }
  return Number((counts || {})[status] || 0)
}

const sellerStatusClass = (status) => {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
  if (status === 'rejected') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'
  if (status === 'blocked') return 'bg-slate-200 text-slate-700 dark:bg-slate-500/20 dark:text-slate-200'
  return 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200'
}

const orderStatusClass = (status) => {
  if (status === 'completed') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (status === 'cancelled' || status === 'rejected') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  if (status === 'shipped') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  if (status === 'packed' || status === 'approved') return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
}

const invoiceStatusClass = (status) => {
  if (status === 'paid') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (status === 'cancelled') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  return 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300'
}

onMounted(fetchSeller)
</script>

<style scoped>
.detail-tile {
  @apply rounded-xl border border-slate-200 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/60;
}

.detail-label {
  @apply text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400;
}

.detail-value {
  @apply mt-1 break-words text-sm font-semibold text-slate-900 dark:text-white;
}
</style>
