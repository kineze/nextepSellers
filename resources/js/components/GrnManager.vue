<template>
  <div class="p-6 w-full">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
      <div>
        <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Inventory</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">GRN Manager</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Supplier-first GRN flow with product search and quick quantity controls.</p>
      </div>
      <button
        @click="openCreate"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black dark:bg-white dark:text-slate-900"
      >
        <i class="fas fa-plus"></i> New GRN
      </button>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-3 border-b border-slate-200/70 p-4 dark:border-slate-800/70 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex gap-2">
          <input
            v-model="filters.search"
            @input="debounceSearch"
            type="search"
            placeholder="Search by GRN no or supplier"
            class="w-64 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          />
          <select
            v-model="filters.status"
            @change="fetchGrns(1)"
            class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          >
            <option value="all">All</option>
            <option value="draft">Draft</option>
            <option value="posted">Posted</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <button
          @click="fetchGrns(pagination.current_page || 1)"
          class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        >
          Refresh
        </button>
      </div>

      <div class="overflow-x-auto p-4">
        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">GRN No</th>
              <th class="px-3 py-3">Supplier</th>
              <th class="px-3 py-3">Received</th>
              <th class="px-3 py-3 text-right">Qty</th>
              <th class="px-3 py-3 text-right">Amount</th>
              <th class="px-3 py-3">Status</th>
              <th class="px-3 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
            <tr v-if="grns.length === 0">
              <td colspan="7" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No GRNs found</td>
            </tr>
            <tr v-for="grn in grns" :key="grn.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4 font-medium text-slate-900 dark:text-white">{{ grn.grn_no }}</td>
              <td class="px-3 py-4">{{ grn.supplier?.name || '-' }}</td>
              <td class="px-3 py-4">{{ formatDate(grn.received_date, grn.received_time) }}</td>
              <td class="px-3 py-4 text-right">{{ grn.total_quantity }}</td>
              <td class="px-3 py-4 text-right">LKR {{ toMoney(grn.total_amount) }}</td>
              <td class="px-3 py-4">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide" :class="statusClass(grn.status)">
                  {{ grn.status }}
                </span>
              </td>
              <td class="px-3 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button type="button" @click="openEdit(grn)" class="rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">{{ grn.status === 'draft' ? 'Edit' : 'View' }}</button>
                  <button v-if="grn.status === 'draft'" type="button" @click="postGrn(grn)" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-700">Post</button>
                  <button v-if="grn.status === 'posted'" type="button" @click="unpostGrn(grn)" class="rounded-lg bg-amber-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-amber-700">Unpost</button>
                  <button v-if="grn.status === 'draft'" type="button" @click="deleteGrn(grn)" class="rounded-lg bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-700">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200/70 px-4 py-4 text-sm text-slate-600 dark:border-slate-800/70 dark:text-slate-300 sm:flex-row sm:items-center sm:justify-between">
        <div>
          Showing
          <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.from || 0 }}</span>
          to
          <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.to || 0 }}</span>
          of
          <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.total || 0 }}</span>
        </div>

        <div class="flex items-center gap-2">
          <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page <= 1" @click="fetchGrns(pagination.current_page - 1)">Prev</button>
          <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page >= pagination.last_page" @click="fetchGrns(pagination.current_page + 1)">Next</button>
        </div>
      </div>
    </div>

    <transition name="fade">
      <div v-if="drawerOpen" class="fixed inset-0 z-[80] bg-slate-900/35" @click="closeDrawer"></div>
    </transition>

    <transition name="slide-right">
      <aside v-if="drawerOpen" class="fixed right-0 top-0 z-[1000] flex h-full w-full max-w-none flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900 lg:w-[75vw]">
        <div class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur dark:border-slate-700 dark:bg-slate-900/95">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ form.id ? (isViewMode ? 'View GRN' : 'Edit GRN') : 'Create GRN' }}</h3>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-300">Select supplier first, then search products and add rows quickly.</p>
            </div>
            <button type="button" @click="closeDrawer" class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Close</button>
          </div>
        </div>

        <form @submit.prevent="saveGrn" class="flex min-h-0 flex-1 flex-col gap-4 p-5">
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Supplier</label>
              <select v-model="form.supplier_id" @change="onSupplierChanged" :disabled="isViewMode" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option :value="null">Select supplier</option>
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
              </select>
              <p class="mt-1 text-[11px] text-slate-500" v-if="!form.supplier_id">Select a supplier to enable products.</p>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Received Date</label>
                <input v-model="form.received_date" :disabled="isViewMode" type="date" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Received Time</label>
                <input v-model="form.received_time" :disabled="isViewMode" type="time" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
              </div>
            </div>
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Notes</label>
            <textarea v-model="form.notes" :disabled="isViewMode" rows="2" class="w-full resize-none rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
          </div>

          <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Add Product</label>
            <div class="relative">
              <input
                ref="productInputRef"
                v-model="productQuery"
                type="search"
                :disabled="!canManageItems"
                :placeholder="canManageItems ? 'Search by product / SKU / variant' : 'Select supplier first'"
                @focus="productDdOpen = canManageItems"
                @keydown.down.prevent="moveActive(1)"
                @keydown.up.prevent="moveActive(-1)"
                @keydown.enter.prevent="selectActive"
                @blur="onProductBlur"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
              />

              <div v-if="productDdOpen && canManageItems" class="mt-2 max-h-56 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                <button
                  v-for="(variant, idx) in suggestionVariants"
                  :key="variant.id"
                  type="button"
                  @mousedown.prevent="addItemFromVariant(variant)"
                  @mousemove="activeIdx = idx"
                  :class="idx === activeIdx ? 'bg-slate-100 dark:bg-slate-800' : ''"
                  class="flex w-full items-center justify-between gap-3 border-b border-slate-100 px-3 py-2 text-left last:border-b-0 dark:border-slate-800"
                >
                  <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">{{ variant.product?.title || 'Product' }}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-300">{{ variant.sku || 'No SKU' }} <span v-if="variant.attributes">• {{ compactVariant(variant) }}</span></p>
                  </div>
                  <span class="shrink-0 text-xs text-slate-500 dark:text-slate-300">LKR {{ toMoney(variant.price || 0) }}</span>
                </button>

                <div v-if="suggestionVariants.length === 0" class="px-3 py-2 text-sm text-slate-500 dark:text-slate-300">
                  No matching products.
                </div>
              </div>
            </div>
          </div>

          <div class="min-h-0 flex-1 overflow-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-200">
              <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <tr>
                  <th class="px-2 py-2">Product</th>
                  <th class="px-2 py-2">Variant</th>
                  <th class="px-2 py-2 text-right">Unit Cost</th>
                  <th class="px-2 py-2 text-center">Qty</th>
                  <th class="px-2 py-2">Mfg</th>
                  <th class="px-2 py-2">Exp</th>
                  <th class="px-2 py-2 text-right">Subtotal</th>
                  <th class="px-2 py-2 text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="form.items.length === 0">
                  <td colspan="8" class="px-2 py-6 text-center text-slate-500 dark:text-slate-400">No items added yet.</td>
                </tr>
                <tr v-for="(item, idx) in form.items" :key="idx" class="border-t border-slate-100 align-top dark:border-slate-800">
                  <td class="px-2 py-2">
                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ item._title || 'Product' }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">SKU: {{ item._sku || '-' }}</p>
                  </td>
                  <td class="px-2 py-2">{{ item._variantText || '-' }}</td>
                  <td class="px-2 py-2 text-right">
                    <input v-model.number="item.unit_cost" :disabled="!canManageItems" type="number" min="0" step="0.01" class="w-24 rounded-lg border border-slate-300 bg-white px-2 py-1 text-right text-xs outline-none disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950" />
                  </td>
                  <td class="px-2 py-2">
                    <div class="flex items-center justify-center">
                      <button type="button" @click="decQty(item)" :disabled="!canManageItems" class="h-6 w-6 rounded-full border border-slate-300 text-xs disabled:opacity-50 dark:border-slate-700">-</button>
                      <input v-model.number="item.quantity" :disabled="!canManageItems" type="number" min="1" class="mx-2 w-14 rounded-lg border border-slate-300 bg-white px-1 py-1 text-center text-xs outline-none disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950" />
                      <button type="button" @click="incQty(item)" :disabled="!canManageItems" class="h-6 w-6 rounded-full border border-slate-300 text-xs disabled:opacity-50 dark:border-slate-700">+</button>
                    </div>
                  </td>
                  <td class="px-2 py-2">
                    <input v-model="item.manufactured_at" :disabled="!canManageItems" type="date" class="w-32 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs outline-none disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950" />
                  </td>
                  <td class="px-2 py-2">
                    <input v-model="item.expires_at" :disabled="!canManageItems" type="date" class="w-32 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs outline-none disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950" />
                  </td>
                  <td class="px-2 py-2 text-right">LKR {{ toMoney(Number(item.quantity || 0) * Number(item.unit_cost || 0)) }}</td>
                  <td class="px-2 py-2 text-right">
                    <button type="button" @click="removeItem(idx)" :disabled="!canManageItems" class="text-rose-600 hover:text-rose-700 disabled:opacity-40">Remove</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="rounded-xl bg-slate-50 px-3 py-2 text-xs dark:bg-slate-800/70">
            <p class="flex items-center justify-between"><span>Total Qty</span><span class="font-semibold">{{ totalQty }}</span></p>
            <p class="mt-1 flex items-center justify-between"><span>Total Amount</span><span class="font-semibold">LKR {{ toMoney(totalAmount) }}</span></p>
          </div>

          <div class="sticky bottom-0 flex items-center justify-end gap-2 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
            <button type="button" @click="closeDrawer" class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button>
            <button v-if="!isViewMode" type="submit" :disabled="saving" class="rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black disabled:opacity-50 dark:bg-white dark:text-slate-900">{{ saving ? 'Saving...' : (form.id ? 'Update GRN' : 'Save GRN') }}</button>
            <button v-if="form.id && form.status === 'draft' && !isViewMode" type="button" @click="postGrn(form)" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-700">Post GRN</button>
          </div>
        </form>
      </aside>
    </transition>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const grns = ref([])
const suppliers = ref([])
const variants = ref([])
const saving = ref(false)
const isViewMode = ref(false)
const drawerOpen = ref(false)

const productQuery = ref('')
const productDdOpen = ref(false)
const activeIdx = ref(-1)
const productInputRef = ref(null)

const filters = reactive({
  search: '',
  status: 'all',
  page: 1,
  per_page: 10,
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0,
})

const form = reactive({
  id: null,
  grn_no: '',
  supplier_id: null,
  purchase_order_id: null,
  received_date: '',
  received_time: '',
  notes: '',
  status: 'draft',
  items: [],
})

let searchDebounce = null

const emptyItem = () => ({
  variant_id: null,
  quantity: 1,
  unit_cost: 0,
  lot_number: null,
  manufactured_at: null,
  expires_at: null,
  _title: '',
  _sku: '',
  _variantText: '',
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const totalQty = computed(() => form.items.reduce((sum, row) => sum + Number(row.quantity || 0), 0))
const totalAmount = computed(() => form.items.reduce((sum, row) => sum + (Number(row.quantity || 0) * Number(row.unit_cost || 0)), 0))
const canManageItems = computed(() => Boolean(form.supplier_id) && !isViewMode.value)

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

const compactVariant = (variant) => {
  const attrs = variant?.attributes || {}
  const pairs = Object.entries(attrs)
    .map(([k, v]) => `${k}:${stringifyAttrValue(v)}`)
    .filter((text) => !text.endsWith(':'))
  return pairs.join(', ')
}

const normalizeDateInput = (value) => {
  if (!value) return null
  const text = String(value)
  if (text.length >= 10) return text.slice(0, 10)
  return text
}

const suggestionVariants = computed(() => {
  const q = (productQuery.value || '').trim().toLowerCase()
  if (!q) return variants.value.slice(0, 60)

  return variants.value
    .filter((v) => {
      const title = (v?.product?.title || '').toLowerCase()
      const sku = (v?.sku || '').toLowerCase()
      const attrs = compactVariant(v).toLowerCase()
      return title.includes(q) || sku.includes(q) || attrs.includes(q)
    })
    .slice(0, 60)
})

const statusClass = (status) => {
  if (status === 'posted') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
  if (status === 'cancelled') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'
  return 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

const formatDate = (date, time) => {
  if (!date) return '-'
  const dateText = String(date).slice(0, 10)
  const timeText = time ? String(time).slice(0, 5) : ''
  return `${dateText}${timeText ? ` ${timeText}` : ''}`
}

const normalizeItems = (items = []) => items.map((item) => {
  const variant = item?.variant || {}
  const product = variant?.product || {}
  return {
    variant_id: item.variant_id ?? null,
    quantity: Number(item.quantity || 1),
    unit_cost: Number(item.unit_cost || 0),
    lot_number: item.lot_number || null,
    manufactured_at: normalizeDateInput(item.manufactured_at),
    expires_at: normalizeDateInput(item.expires_at),
    _title: product.title || item._title || 'Product',
    _sku: variant.sku || item._sku || '',
    _variantText: compactVariant(variant) || item._variantText || '-',
  }
})

const resetForm = () => {
  form.id = null
  form.grn_no = ''
  form.supplier_id = null
  form.purchase_order_id = null
  form.received_date = new Date().toISOString().slice(0, 10)
  form.received_time = new Date().toTimeString().slice(0, 5)
  form.notes = ''
  form.status = 'draft'
  form.items = []
  productQuery.value = ''
  variants.value = []
  activeIdx.value = -1
  productDdOpen.value = false
  isViewMode.value = false
}

const closeDrawer = () => {
  drawerOpen.value = false
  productDdOpen.value = false
}

const openCreate = async () => {
  resetForm()
  await fetchOptions(null)
  drawerOpen.value = true
}

const fetchOptions = async (supplierId = null) => {
  try {
    const { data } = await axios.get('/api/grns/options', {
      params: { supplier_id: supplierId || undefined },
    })
    suppliers.value = data?.suppliers || []
    variants.value = data?.variants || []
  } catch {
    toast.error('Failed to load GRN options.')
  }
}

const onSupplierChanged = async () => {
  if (isViewMode.value) return

  if (form.items.length > 0) {
    const ok = confirm('Changing supplier will clear current items. Continue?')
    if (!ok) return
  }

  await fetchOptions(form.supplier_id)
  productQuery.value = ''
  productDdOpen.value = false
  activeIdx.value = -1
  form.items = []
}

const addItemFromVariant = (variant) => {
  if (!canManageItems.value) return

  const variantId = Number(variant?.id)
  if (!variantId) return

  const found = form.items.find((row) => Number(row.variant_id) === variantId)
  if (found) {
    found.quantity = Number(found.quantity || 1) + 1
  } else {
    form.items.push({
      variant_id: variantId,
      quantity: 1,
      unit_cost: Number(variant?.price || 0),
      lot_number: null,
      manufactured_at: null,
      expires_at: null,
      _title: variant?.product?.title || 'Product',
      _sku: variant?.sku || '',
      _variantText: compactVariant(variant) || '-',
    })
  }

  productQuery.value = ''
  activeIdx.value = -1
  productDdOpen.value = true

  if (productInputRef.value) {
    productInputRef.value.focus()
  }
}

const incQty = (item) => {
  if (!canManageItems.value) return
  item.quantity = Math.max(1, Number(item.quantity || 1) + 1)
}

const decQty = (item) => {
  if (!canManageItems.value) return
  item.quantity = Math.max(1, Number(item.quantity || 1) - 1)
}

const removeItem = (idx) => {
  if (!canManageItems.value) return
  form.items.splice(idx, 1)
}

const moveActive = (dir) => {
  if (!suggestionVariants.value.length || !productDdOpen.value) return
  let next = activeIdx.value + dir
  next = Math.max(0, Math.min(suggestionVariants.value.length - 1, next))
  activeIdx.value = next
}

const selectActive = () => {
  if (!productDdOpen.value) return
  const row = suggestionVariants.value[activeIdx.value]
  if (row) addItemFromVariant(row)
}

const onProductBlur = () => {
  setTimeout(() => {
    productDdOpen.value = false
    activeIdx.value = -1
  }, 120)
}

const fetchGrns = async (page = 1) => {
  try {
    const { data } = await axios.get('/api/grns', {
      params: {
        page,
        per_page: filters.per_page,
        search: filters.search || undefined,
        status: filters.status || 'all',
      },
    })

    grns.value = data?.grns || []
    pagination.current_page = data?.pagination?.current_page || 1
    pagination.last_page = data?.pagination?.last_page || 1
    pagination.per_page = data?.pagination?.per_page || filters.per_page
    pagination.total = data?.pagination?.total || 0
    pagination.from = data?.pagination?.from || 0
    pagination.to = data?.pagination?.to || 0
  } catch {
    toast.error('Failed to fetch GRNs.')
  }
}

const debounceSearch = () => {
  clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => fetchGrns(1), 300)
}

const openEdit = async (grn) => {
  try {
    const { data } = await axios.get(`/api/grns/${grn.id}`)
    const row = data?.grn
    if (!row) return

    await fetchOptions(row.supplier_id)

    form.id = row.id
    form.grn_no = row.grn_no
    form.supplier_id = row.supplier_id
    form.purchase_order_id = row.purchase_order_id
    form.received_date = row.received_date
    form.received_time = row.received_time ? String(row.received_time).slice(0, 5) : ''
    form.notes = row.notes || ''
    form.status = row.status
    form.items = normalizeItems(row.items || [])

    productQuery.value = ''
    activeIdx.value = -1
    productDdOpen.value = false

    isViewMode.value = row.status !== 'draft'
    drawerOpen.value = true
  } catch {
    toast.error('Failed to load GRN details.')
  }
}

const validateBeforeSave = () => {
  if (!form.supplier_id) return 'Select a supplier first.'
  if (!form.items.length) return 'Add at least one item.'

  for (const row of form.items) {
    if (!row.variant_id) return 'Select variant for each row.'
    if (!Number(row.quantity) || Number(row.quantity) <= 0) return 'Quantity must be greater than 0.'
    if (Number(row.unit_cost) < 0) return 'Unit cost cannot be negative.'
  }

  return ''
}

const saveGrn = async () => {
  const error = validateBeforeSave()
  if (error) {
    toast.error(error)
    return
  }

  saving.value = true

  const payload = {
    supplier_id: form.supplier_id,
    purchase_order_id: form.purchase_order_id,
    received_date: form.received_date,
    received_time: form.received_time,
    notes: form.notes || null,
    items: normalizeItems(form.items),
  }

  try {
    if (form.id) {
      await axios.put(`/api/grns/${form.id}`, payload)
      toast.success('GRN updated successfully.')
    } else {
      await axios.post('/api/grns', payload)
      toast.success('GRN created successfully.')
    }

    await fetchGrns(form.id ? pagination.current_page : 1)
    closeDrawer()
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to save GRN.')
  } finally {
    saving.value = false
  }
}

const postGrn = async (grn) => {
  const id = grn.id || form.id
  if (!id) return

  try {
    await axios.post(`/api/grns/${id}/post`)
    toast.success('GRN posted successfully.')
    await fetchGrns(pagination.current_page)

    if (form.id === id) {
      await openEdit({ id })
    }
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to post GRN.')
  }
}

const unpostGrn = async (grn) => {
  try {
    await axios.post(`/api/grns/${grn.id}/unpost`)
    toast.success('GRN unposted successfully.')
    await fetchGrns(pagination.current_page)
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to unpost GRN.')
  }
}

const deleteGrn = async (grn) => {
  if (!confirm(`Delete ${grn.grn_no}?`)) return

  try {
    await axios.delete(`/api/grns/${grn.id}`)
    toast.success('GRN deleted successfully.')
    await fetchGrns(1)

    if (form.id === grn.id) {
      closeDrawer()
    }
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to delete GRN.')
  }
}

onMounted(async () => {
  resetForm()
  await fetchOptions(null)
  await fetchGrns(1)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-right-enter-active,
.slide-right-leave-active {
  transition: transform 0.22s ease;
}

.slide-right-enter-from,
.slide-right-leave-to {
  transform: translateX(100%);
}
</style>
