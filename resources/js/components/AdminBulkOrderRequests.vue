<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Orders</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Bulk Order Requests</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-bulk-order-requests"
        @filters-changed="onGlobalFiltersChanged"
      />

      <div class="flex flex-wrap items-center justify-end gap-2">
        <select
          v-model="filters.status"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @change="onStatusFilterChanged"
        >
          <option value="all">All Request Statuses</option>
          <option value="draft">Draft</option>
          <option value="submitted">Submitted</option>
          <option value="approved">Approved</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <button
          type="button"
          class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
          @click="fetchRequests"
        >
          Refresh
        </button>
      </div>
    </div>

    <div class="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
      <p>Total {{ meta.total }} bulk requests</p>
    </div>

    <div v-if="loading" class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
      Loading bulk requests...
    </div>

    <div v-else-if="!requests.length" class="mt-4 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
      No bulk requests found.
    </div>

    <div v-else class="mt-4 space-y-3">
      <article
        v-for="request in requests"
        :key="request.id"
        class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900/40"
      >
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ request.request_no }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ sellerName(request.seller) }} · {{ formatDate(request.created_at) }}
            </p>
          </div>

          <div class="flex items-center gap-2">
            <span :class="statusClass(request.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ request.status }}</span>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">
              {{ request.orders_count || (request.orders || []).length }} orders
            </span>
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="toggleExpand(request.id)"
            >
              {{ isExpanded(request.id) ? 'Collapse' : 'Expand' }}
            </button>
            <button
              v-if="request.status !== 'approved'"
              type="button"
              class="rounded-lg bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-emerald-700 disabled:opacity-50"
              :disabled="approvingId === request.id"
              @click="approveRequest(request)"
            >
              {{ approvingId === request.id ? 'Approving...' : 'Approve Request' }}
            </button>
          </div>
        </div>

        <div class="mt-3 grid gap-2 text-xs text-slate-600 dark:text-slate-300 sm:grid-cols-3">
          <p><span class="font-semibold">Total Collectable:</span> LKR {{ toMoney(request.total_collectable_amount) }}</p>
          <p><span class="font-semibold">Linked Orders:</span> {{ (request.orders || []).length }}</p>
          <p><span class="font-semibold">Seller:</span> {{ sellerName(request.seller) }}</p>
        </div>

        <div v-if="isExpanded(request.id)" class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
          <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
              <tr>
                <th class="px-3 py-2 text-left">Order</th>
                <th class="px-3 py-2 text-left">Customer</th>
                <th class="px-3 py-2 text-left">City</th>
                <th class="px-3 py-2 text-right">Collectable</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr v-for="order in request.orders || []" :key="order.id">
                <td class="px-3 py-2 font-semibold text-slate-900 dark:text-white">#{{ order.id }}</td>
                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                  <p>{{ order.customer_name || '-' }}</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ order.phone || '-' }}</p>
                </td>
                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ order.city?.name_en || '-' }}</td>
                <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(order.total_collectable_amount) }}</td>
                <td class="px-3 py-2">
                  <span :class="orderStatusClass(order.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ order.status }}</span>
                </td>
                <td class="px-3 py-2 text-right">
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
      </article>
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
const approvingId = ref(null)
const requests = ref([])
const expandedRequestIds = ref([])

const filters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  seller_id: null,
  status: 'all',
  page: 1,
  per_page: 10,
})

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
})

const statusStorageKey = 'nextep-admin-bulk-order-requests-status-v1'

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

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'approved') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'cancelled') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  if (value === 'submitted') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
}

const orderStatusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'completed') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'cancelled') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  if (value === 'shipped' || value === 'packed') return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
  if (value === 'confirmed' || value === 'approved') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

const fetchRequests = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/orders/bulk-requests', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        status: filters.status || 'all',
        page: filters.page,
        per_page: filters.per_page,
      },
    })

    requests.value = Array.isArray(data?.requests) ? data.requests : []
    const visibleIds = new Set(requests.value.map((row) => Number(row.id)))
    expandedRequestIds.value = expandedRequestIds.value.filter((id) => visibleIds.has(Number(id)))
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
    meta.total = Number(data?.meta?.total || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load bulk requests.')
  } finally {
    loading.value = false
  }
}

const isExpanded = (requestId) => expandedRequestIds.value.includes(Number(requestId))

const toggleExpand = (requestId) => {
  const id = Number(requestId)
  if (expandedRequestIds.value.includes(id)) {
    expandedRequestIds.value = expandedRequestIds.value.filter((row) => row !== id)
    return
  }
  expandedRequestIds.value.push(id)
}

const approveRequest = async (request) => {
  approvingId.value = request.id
  try {
    const { data } = await axios.post(`/api/admin/orders/bulk-requests/${request.id}/approve`)
    toast.success(data?.message || 'Bulk order request approved.')
    fetchRequests()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to approve bulk request.')
  } finally {
    approvingId.value = null
  }
}

const onFilterChanged = () => {
  filters.page = 1
  fetchRequests()
}

const onGlobalFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.seller_id = payload?.seller_id || null
  onFilterChanged()
}

const saveStatusPreference = () => {
  try {
    window.localStorage.setItem(statusStorageKey, filters.status || 'all')
  } catch {
    // ignore localStorage failure
  }
}

const loadStatusPreference = () => {
  try {
    const saved = window.localStorage.getItem(statusStorageKey)
    if (saved && ['all', 'draft', 'submitted', 'approved', 'cancelled'].includes(saved)) {
      filters.status = saved
    }
  } catch {
    // ignore localStorage failure
  }
}

const onStatusFilterChanged = () => {
  saveStatusPreference()
  onFilterChanged()
}

const changePage = (page) => {
  filters.page = page
  fetchRequests()
}

loadStatusPreference()
</script>
