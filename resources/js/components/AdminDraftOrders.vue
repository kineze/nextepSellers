<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Orders</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Draft Orders</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-draft-orders"
        @filters-changed="onGlobalFiltersChanged"
      />
    </div>

    <div class="mt-4 flex items-center justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} draft orders</p>
      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="loading"
          @click="toggleShowAll"
        >
          {{ showAll ? 'Show 20 per page' : 'Show all' }}
        </button>
        <button
          type="button"
          class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
          @click="fetchOrders"
        >
          Refresh
        </button>
        <button
          type="button"
          class="rounded-lg bg-emerald-600 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="!selectedIds.length || approvingBulk"
          @click="bulkApprove"
        >
          {{ approvingBulk ? 'Approving...' : `Bulk Approve (${selectedIds.length})` }}
        </button>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">
              <input type="checkbox" :checked="allChecked" @change="toggleSelectAll" />
            </th>
            <th class="px-3 py-3 text-left">Order</th>
            <th class="px-3 py-3 text-left">Customer</th>
            <th class="px-3 py-3 text-left">Seller</th>
            <th class="px-3 py-3 text-left">Products</th>
            <th class="px-3 py-3 text-left">City</th>
            <th class="px-3 py-3 text-right">Collectable</th>
            <th class="px-3 py-3 text-left">Created</th>
            <th class="px-3 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading draft orders...</td>
          </tr>
          <tr v-else-if="!orders.length">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No draft orders found.</td>
          </tr>

          <tr v-for="order in orders" :key="order.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3">
              <input type="checkbox" :checked="selectedIds.includes(order.id)" @change="toggleOrder(order.id)" />
            </td>
            <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">#{{ order.id }}</td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
              <p>{{ order.customer_name || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ order.phone || '-' }}</p>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
              {{ sellerName(order.seller) }}
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
              <div class="relative min-w-48">
                <input
                  v-model="cityInputs[order.id]"
                  type="text"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                  placeholder="Search city"
                  :disabled="savingCityId === order.id"
                  @focus="openCityDropdown(order)"
                  @input="onCityInput(order)"
                  @blur="closeCityDropdownWithDelay"
                />
                <button
                  v-if="order.city?.name_en && cityInputs[order.id] !== order.city.name_en"
                  type="button"
                  class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                  @mousedown.prevent="resetCityInput(order)"
                >
                  Reset
                </button>

                <div
                  v-if="cityDropdownOrderId === order.id"
                  class="absolute z-40 mt-1 max-h-56 w-full overflow-auto rounded-xl border border-slate-200 bg-white p-1 shadow-xl dark:border-slate-700 dark:bg-slate-900"
                >
                  <div v-if="loadingCityOrderId === order.id" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">Searching cities...</div>
                  <button
                    v-for="city in cityOptions[order.id] || []"
                    :key="city.id"
                    type="button"
                    class="block w-full rounded-lg px-3 py-2 text-left text-xs font-semibold text-slate-700 hover:bg-blue-50 dark:text-slate-200 dark:hover:bg-blue-500/10"
                    @mousedown.prevent="selectCity(order, city)"
                  >
                    {{ city.name_en }}
                  </button>
                  <div v-if="loadingCityOrderId !== order.id && !(cityOptions[order.id] || []).length" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                    No matching cities.
                  </div>
                </div>
              </div>
            </td>
            <td class="px-3 py-3 text-right font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(order.total_collectable_amount) }}</td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(order.order_datetime) }}</td>
            <td class="px-3 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <a
                  :href="`/admin/orders/${order.id}`"
                  class="rounded-lg border border-slate-300 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                  View
                </a>
                <button
                  type="button"
                  class="rounded-lg bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="approvingSingleId === order.id || rejectingSingleId === order.id"
                  @click="approveSingle(order.id)"
                >
                  {{ approvingSingleId === order.id ? 'Approving...' : 'Approve' }}
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-rose-600 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="approvingSingleId === order.id || rejectingSingleId === order.id"
                  @click="confirmReject(order)"
                >
                  {{ rejectingSingleId === order.id ? 'Rejecting...' : 'Reject' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">
        Showing {{ orders.length }} of {{ meta.total }} draft orders
      </p>
      <div v-if="!showAll" class="flex flex-wrap items-center gap-1">
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
          :disabled="meta.current_page <= 1 || loading"
          @click="changePage(meta.current_page - 1)"
        >
          Previous
        </button>
        <template v-for="item in paginationItems" :key="item.key">
          <span
            v-if="item.type === 'ellipsis'"
            class="min-w-8 px-1 text-center text-xs text-slate-400"
            aria-hidden="true"
          >
            &hellip;
          </span>
          <button
            v-else
            type="button"
            class="min-w-8 rounded-lg border px-2 py-1.5 text-xs font-semibold"
            :class="item.page === meta.current_page
              ? 'border-slate-900 bg-slate-900 text-white dark:border-white dark:bg-white dark:text-slate-900'
              : 'border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800'"
            :disabled="loading || item.page === meta.current_page"
            :aria-current="item.page === meta.current_page ? 'page' : undefined"
            @click="changePage(item.page)"
          >
            {{ item.page }}
          </button>
        </template>
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
import { computed, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const approvingBulk = ref(false)
const approvingSingleId = ref(null)
const rejectingSingleId = ref(null)
const savingCityId = ref(null)
const loadingCityOrderId = ref(null)
const cityDropdownOrderId = ref(null)
const orders = ref([])
const selectedIds = ref([])
const showAll = ref(false)
const cityInputs = reactive({})
const cityOptions = reactive({})
const citySearchTimers = {}

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

const allChecked = computed(() => {
  if (!orders.value.length) return false
  return orders.value.every((row) => selectedIds.value.includes(row.id))
})

const paginationItems = computed(() => {
  const lastPage = Math.max(1, Number(meta.last_page || 1))
  const currentPage = Math.min(lastPage, Math.max(1, Number(meta.current_page || 1)))

  if (lastPage <= 7) {
    return Array.from({ length: lastPage }, (_, index) => ({
      type: 'page',
      page: index + 1,
      key: `page-${index + 1}`,
    }))
  }

  const pages = new Set([1, lastPage])
  for (let page = currentPage - 1; page <= currentPage + 1; page += 1) {
    if (page > 1 && page < lastPage) pages.add(page)
  }

  const sortedPages = Array.from(pages).sort((a, b) => a - b)
  const items = []
  sortedPages.forEach((page, index) => {
    if (index > 0 && page - sortedPages[index - 1] > 1) {
      items.push({ type: 'ellipsis', key: `ellipsis-${sortedPages[index - 1]}-${page}` })
    }
    items.push({ type: 'page', page, key: `page-${page}` })
  })

  return items
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
  const text = Object.entries(attrs)
    .map(([k, v]) => `${k}:${stringifyAttrValue(v)}`)
    .filter((row) => !row.endsWith(':'))
    .join(', ')
  return text
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/orders/draft', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        page: filters.page,
        per_page: filters.per_page,
        show_all: showAll.value ? 1 : undefined,
      },
    })

    orders.value = Array.isArray(data?.orders) ? data.orders : []
    orders.value.forEach((order) => {
      cityInputs[order.id] = order.city?.name_en || ''
      cityOptions[order.id] = []
    })
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
    meta.total = Number(data?.meta?.total || 0)

    const visibleIds = new Set(orders.value.map((row) => row.id))
    selectedIds.value = selectedIds.value.filter((id) => visibleIds.has(id))
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load draft orders.')
  } finally {
    loading.value = false
  }
}

const fetchCityOptions = async (orderId, search = '') => {
  loadingCityOrderId.value = orderId
  try {
    const { data } = await axios.get('/api/admin/cities', {
      params: { search: search || undefined, limit: 20 },
    })
    cityOptions[orderId] = Array.isArray(data?.cities) ? data.cities : []
  } catch (error) {
    cityOptions[orderId] = []
    toast.error(error?.response?.data?.message || 'Failed to load cities.')
  } finally {
    if (loadingCityOrderId.value === orderId) {
      loadingCityOrderId.value = null
    }
  }
}

const openCityDropdown = (order) => {
  cityDropdownOrderId.value = order.id
  fetchCityOptions(order.id, cityInputs[order.id] || '')
}

const closeCityDropdownWithDelay = () => {
  window.setTimeout(() => {
    cityDropdownOrderId.value = null
  }, 140)
}

const onCityInput = (order) => {
  cityDropdownOrderId.value = order.id
  window.clearTimeout(citySearchTimers[order.id])
  citySearchTimers[order.id] = window.setTimeout(() => {
    fetchCityOptions(order.id, cityInputs[order.id] || '')
  }, 220)
}

const resetCityInput = (order) => {
  cityInputs[order.id] = order.city?.name_en || ''
  cityOptions[order.id] = []
  cityDropdownOrderId.value = null
}

const selectCity = async (order, city) => {
  savingCityId.value = order.id
  try {
    const { data } = await axios.put(`/api/admin/orders/${order.id}/city`, {
      city_id: city.id,
    })

    order.city = data?.order?.city || { id: city.id, name_en: city.name_en }
    order.city_id = Number(data?.order?.city_id || city.id)
    cityInputs[order.id] = order.city?.name_en || city.name_en || ''
    cityOptions[order.id] = []
    cityDropdownOrderId.value = null
    toast.success(data?.message || 'Order city updated.')
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to update order city.')
    resetCityInput(order)
  } finally {
    savingCityId.value = null
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
  if (loading.value || page < 1 || page > meta.last_page || page === meta.current_page) return
  filters.page = page
  fetchOrders()
}

const toggleShowAll = () => {
  showAll.value = !showAll.value
  filters.page = 1
  fetchOrders()
}

const toggleOrder = (id) => {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((rowId) => rowId !== id)
    return
  }
  selectedIds.value.push(id)
}

const toggleSelectAll = () => {
  if (allChecked.value) {
    const pageIds = new Set(orders.value.map((row) => row.id))
    selectedIds.value = selectedIds.value.filter((id) => !pageIds.has(id))
    return
  }

  const merged = new Set([...selectedIds.value, ...orders.value.map((row) => row.id)])
  selectedIds.value = Array.from(merged)
}

const approveSingle = async (orderId) => {
  approvingSingleId.value = orderId
  try {
    const { data } = await axios.post(`/api/admin/orders/${orderId}/approve`)
    toast.success(data?.message || 'Order approved successfully.')

    selectedIds.value = selectedIds.value.filter((id) => id !== orderId)
    fetchOrders()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to approve order.')
  } finally {
    approvingSingleId.value = null
  }
}

const confirmReject = (order) => {
  const label = order?.customer_name ? `#${order.id} for ${order.customer_name}` : `#${order?.id}`
  if (!window.confirm(`Reject draft order ${label}? This will remove it from the draft order list.`)) {
    return
  }

  rejectSingle(order.id)
}

const rejectSingle = async (orderId) => {
  rejectingSingleId.value = orderId
  try {
    const { data } = await axios.post(`/api/admin/orders/${orderId}/reject`)
    toast.success(data?.message || 'Order rejected successfully.')

    selectedIds.value = selectedIds.value.filter((id) => id !== orderId)
    fetchOrders()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to reject order.')
  } finally {
    rejectingSingleId.value = null
  }
}

const bulkApprove = async () => {
  if (!selectedIds.value.length) return

  approvingBulk.value = true
  try {
    const { data } = await axios.post('/api/admin/orders/bulk-approve', {
      order_ids: selectedIds.value,
    })

    const approvedCount = Array.isArray(data?.approved) ? data.approved.length : 0
    const failedCount = Array.isArray(data?.failed) ? data.failed.length : 0

    toast.success(`${approvedCount} approved. ${failedCount} failed.`)
    selectedIds.value = []
    fetchOrders()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Bulk approve failed.')
  } finally {
    approvingBulk.value = false
  }
}
</script>
