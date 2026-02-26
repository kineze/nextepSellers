<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-300">Orders</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Approved Orders</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-approved-orders"
        @filters-changed="onGlobalFiltersChanged"
      />
    </div>

    <div class="mt-4 flex items-center justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} approved orders</p>
      <div class="flex gap-2">
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
          :disabled="!dispatchableSelection.length || creatingDispatch"
          @click="openDispatchModal"
        >
          {{ creatingDispatch ? 'Creating...' : `Add to Dispatch (${dispatchableSelection.length})` }}
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
            <th class="px-3 py-3 text-left">Waybill</th>
            <th class="px-3 py-3 text-left">Products</th>
            <th class="px-3 py-3 text-left">Stock Check</th>
            <th class="px-3 py-3 text-right">Collectable</th>
            <th class="px-3 py-3 text-left">Created</th>
            <th class="px-3 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="10" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading approved orders...</td>
          </tr>
          <tr v-else-if="!orders.length">
            <td colspan="10" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No approved orders found.</td>
          </tr>

          <tr v-for="order in orders" :key="order.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3 align-top">
              <input
                type="checkbox"
                :checked="selectedIds.includes(order.id)"
                :disabled="!order.can_ship"
                @change="toggleOrder(order.id)"
              />
            </td>
            <td class="px-3 py-3 align-top font-semibold text-slate-900 dark:text-white">
              <p>#{{ order.id }}</p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ order.items?.length || 0 }} item line(s)</p>
            </td>
            <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">
              <p>{{ order.customer_name || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ order.phone || '-' }}</p>
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

                  <div class="mt-1 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                    <div class="flex items-center gap-2 text-slate-500 dark:text-slate-300">
                      <span>SKU: <span class="font-mono">{{ item.variant?.sku || '-' }}</span></span>
                      <span>|</span>
                      <span>Qty: <span class="font-semibold">{{ item.quantity || 0 }}</span></span>
                    </div>

                    <div class="flex items-center gap-1">
                      <span
                        class="inline-block h-2.5 w-2.5 rounded-full"
                        :class="itemAvailability(order, item).ok ? 'bg-emerald-500' : 'bg-rose-500'"
                      ></span>
                      <span
                        class="text-[11px] font-semibold"
                        :class="itemAvailability(order, item).ok ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'"
                      >
                        {{ itemAvailability(order, item).ok ? 'Available' : 'Insufficient' }}
                      </span>
                    </div>
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
                :class="order.can_ship
                  ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                  : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'"
              >
                {{ order.can_ship ? 'Can Dispatch' : stockStatusLabel(order) }}
              </span>

              <div v-if="order.stock_checks?.length" class="mt-2 space-y-1">
                <p
                  v-for="check in order.stock_checks"
                  :key="`${order.id}-${check.variant_id}`"
                  class="text-[11px]"
                  :class="check.is_available ? 'text-slate-500 dark:text-slate-400' : 'text-rose-600 dark:text-rose-300'"
                >
                  Variant #{{ check.variant_id }}: req {{ check.required_qty }} / avail {{ check.available_qty }}
                </p>
              </div>
            </td>
            <td class="px-3 py-3 align-top text-right font-semibold text-slate-900 dark:text-white">
              LKR {{ toMoney(order.total_collectable_amount) }}
            </td>
            <td class="px-3 py-3 align-top text-slate-600 dark:text-slate-300">
              {{ formatDate(order.order_datetime) }}
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

    <div v-if="showDispatchModal" class="fixed inset-0 z-[1100] flex items-center justify-center bg-black/60 p-4 backdrop-blur-[1px]">
      <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-5 py-4 text-white">
          <h3 class="text-lg font-semibold">Create Dispatch Note</h3>
          <p class="mt-1 text-xs opacity-90">Use selected approved orders to create a new dispatch batch.</p>
        </div>

        <div class="space-y-4 p-5">
          <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800/60 dark:bg-emerald-900/30">
            <div class="flex flex-wrap items-center gap-2 text-xs">
              <span class="inline-flex rounded-full bg-white px-2.5 py-1 font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                Selected {{ dispatchableSelection.length }} order(s)
              </span>
              <span class="inline-flex rounded-full bg-white px-2.5 py-1 font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                Stock Validated
              </span>
            </div>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Dispatch Date</label>
              <input v-model="dispatchForm.dispatch_date" type="date" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Dispatch Time</label>
              <input v-model="dispatchForm.dispatch_time" type="time" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
            </div>
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Remarks</label>
            <textarea
              v-model="dispatchForm.remarks"
              rows="3"
              placeholder="Optional note for this dispatch batch"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
            ></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-700 dark:bg-slate-800/60">
          <button @click="showDispatchModal = false" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-white dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-900">Cancel</button>
          <button
            @click="createDispatchNote"
            :disabled="creatingDispatch || !dispatchableSelection.length"
            class="rounded-lg bg-emerald-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
          >
            {{ creatingDispatch ? 'Creating...' : 'Create Dispatch Note' }}
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

const loading = ref(false)
const creatingDispatch = ref(false)
const orders = ref([])
const selectedIds = ref([])
const showDispatchModal = ref(false)

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

const dispatchForm = reactive({
  dispatch_date: new Date().toISOString().slice(0, 10),
  dispatch_time: new Date().toTimeString().slice(0, 5),
  remarks: '',
})

const dispatchableSelection = computed(() => {
  const map = new Map(orders.value.map((row) => [row.id, row]))
  return selectedIds.value.filter((id) => map.get(id)?.can_ship)
})

const allChecked = computed(() => {
  if (!orders.value.length) return false
  const selectableIds = orders.value.filter((row) => row.can_ship).map((row) => row.id)
  if (!selectableIds.length) return false
  return selectableIds.every((id) => selectedIds.value.includes(id))
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

const itemAvailability = (order, item) => {
  const variantId = Number(item?.product_variant_id || 0)
  const checks = Array.isArray(order?.stock_checks) ? order.stock_checks : []
  const check = checks.find((x) => Number(x?.variant_id || 0) === variantId)
  if (!check) {
    return { ok: true, required: Number(item?.quantity || 0), available: Number(item?.quantity || 0) }
  }
  return {
    ok: Boolean(check.is_available),
    required: Number(check.required_qty || item?.quantity || 0),
    available: Number(check.available_qty || 0),
  }
}

const stockStatusLabel = (order) => {
  if (order.dispatch_note_id) {
    return `Already in Dispatch #${order.dispatch_note_id}`
  }
  return 'Insufficient Lot Stock'
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/orders/approved', {
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

    const pageIds = new Set(orders.value.map((row) => row.id))
    selectedIds.value = selectedIds.value.filter((id) => pageIds.has(id))
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load approved orders.')
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

const toggleOrder = (id) => {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((x) => x !== id)
    return
  }
  selectedIds.value.push(id)
}

const toggleSelectAll = () => {
  const ids = orders.value.filter((row) => row.can_ship).map((row) => row.id)

  if (allChecked.value) {
    const set = new Set(ids)
    selectedIds.value = selectedIds.value.filter((id) => !set.has(id))
    return
  }

  selectedIds.value = Array.from(new Set([...selectedIds.value, ...ids]))
}

const openDispatchModal = () => {
  if (!dispatchableSelection.value.length) {
    toast.error('Select dispatchable orders first.')
    return
  }
  showDispatchModal.value = true
}

const createDispatchNote = async () => {
  if (!dispatchableSelection.value.length) return

  creatingDispatch.value = true
  try {
    const { data } = await axios.post('/api/admin/dispatch-notes/from-approved', {
      order_ids: dispatchableSelection.value,
      dispatch_date: dispatchForm.dispatch_date || null,
      dispatch_time: dispatchForm.dispatch_time || null,
      remarks: dispatchForm.remarks || null,
    })

    toast.success(data?.message || 'Dispatch note created successfully.')

    selectedIds.value = []
    showDispatchModal.value = false
    dispatchForm.remarks = ''
    const createdId = Number(data?.dispatch_note_id || 0)
    if (createdId > 0) {
      window.location.href = `/admin/orders/dispatch-notes/${createdId}`
      return
    }
    await fetchOrders()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to create dispatch note.')
  } finally {
    creatingDispatch.value = false
  }
}

fetchOrders()
</script>
