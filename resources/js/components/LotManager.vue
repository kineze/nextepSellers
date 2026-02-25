<template>
  <div class="space-y-5 p-6">
    <div class="rounded-2xl border border-slate-200/70 bg-gradient-to-br from-white via-slate-50 to-slate-100 p-5 shadow-sm dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Inventory</p>
          <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Lot Manager</h2>
          <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Manage lot items, damaged stock, manual quantity adjustments, and barcode ranges.</p>
        </div>
        <button
          @click="fetchLots(pagination.current_page || 1)"
          class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        >
          <i class="fas fa-rotate-right"></i>
          Refresh
        </button>
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-xl border border-indigo-100 bg-white p-4 shadow-sm dark:border-indigo-900/60 dark:bg-slate-900">
        <p class="text-xs text-slate-500 dark:text-slate-400">Total Lots</p>
        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_lots || 0 }}</p>
      </div>
      <div class="rounded-xl border border-sky-100 bg-white p-4 shadow-sm dark:border-sky-900/60 dark:bg-slate-900">
        <p class="text-xs text-slate-500 dark:text-slate-400">Total Quantity</p>
        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total_quantity || 0 }}</p>
      </div>
      <div class="rounded-xl border border-emerald-100 bg-white p-4 shadow-sm dark:border-emerald-900/60 dark:bg-slate-900">
        <p class="text-xs text-slate-500 dark:text-slate-400">Active Lots</p>
        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ stats.active_lots || 0 }}</p>
      </div>
      <div class="rounded-xl border border-amber-100 bg-white p-4 shadow-sm dark:border-amber-900/60 dark:bg-slate-900">
        <p class="text-xs text-slate-500 dark:text-slate-400">Expiring Soon</p>
        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ stats.expiring_soon || 0 }}</p>
      </div>
    </div>

    <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 md:flex-row md:items-end md:justify-between">
      <div class="flex flex-wrap items-center gap-2">
        <input
          v-model="filters.search"
          @keyup.enter="fetchLots(1)"
          type="search"
          placeholder="Search lot, barcode, product, variant..."
          class="w-72 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
        />
        <button
          @click="fetchLots(1)"
          class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
        >Filter</button>
        <button
          @click="resetFilters"
          class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        >Reset</button>
      </div>

      <div class="text-xs text-slate-500 dark:text-slate-300">
        Showing {{ pagination.total || 0 }} lots
      </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
      <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-200">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
          <tr>
            <th class="px-3 py-2">Lot</th>
            <th class="px-3 py-2">Product</th>
            <th class="px-3 py-2">Variant</th>
            <th class="px-3 py-2">Barcode Range</th>
            <th class="px-3 py-2 text-center">Qty</th>
            <th class="px-3 py-2">MFG</th>
            <th class="px-3 py-2">EXP</th>
            <th class="px-3 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="lots.length === 0">
            <td colspan="8" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No lots found.</td>
          </tr>
          <tr v-for="lot in lots" :key="lot.id" class="border-t border-slate-100 transition hover:bg-slate-50/80 dark:border-slate-800 dark:hover:bg-slate-800/60">
            <td class="px-3 py-2">
              <span class="inline-flex rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                {{ lot.lot_number }}
              </span>
            </td>
            <td class="px-3 py-2">
              <p class="font-medium text-slate-900 dark:text-white">{{ lot.variant?.product?.title || '-' }}</p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ lot.variant?.product?.product_code || '-' }}</p>
            </td>
            <td class="px-3 py-2 text-xs text-slate-600 dark:text-slate-300">{{ variantLabel(lot.variant) }}</td>
            <td class="px-3 py-2">
              <div v-if="lot.barcode_start" class="flex items-center gap-2">
                <span class="rounded bg-indigo-50 px-2 py-1 font-mono text-xs text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ lot.barcode_start }} → {{ lot.barcode_end }}</span>
                <button @click="copyRange(lot)" class="rounded bg-indigo-600 px-2 py-1 text-[11px] font-semibold text-white hover:bg-indigo-700">Copy</button>
              </div>
              <span v-else class="text-slate-400">-</span>
            </td>
            <td class="px-3 py-2 text-center">
              <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ lot.quantity }}</span>
            </td>
            <td class="px-3 py-2">{{ formatDate(lot.manufactured_at) }}</td>
            <td class="px-3 py-2" :class="expiringClass(lot.expires_at)">{{ formatDate(lot.expires_at) }}</td>
            <td class="px-3 py-2">
              <div class="flex justify-end gap-2">
                <button @click="openItemsDrawer(lot)" class="rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">Items</button>
                <button @click="openAddItemsModal(lot)" class="rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Add</button>
                <button @click="openAdjustModal(lot)" class="rounded-lg bg-amber-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">Adjust</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

      <div class="flex flex-wrap items-center justify-between border-t border-slate-200 px-3 py-3 text-sm dark:border-slate-700">
        <div class="text-slate-500 dark:text-slate-300">Page {{ pagination.current_page }} of {{ pagination.last_page }}</div>
        <div class="flex items-center gap-1">
          <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="rounded border border-slate-300 px-3 py-1 disabled:opacity-50 dark:border-slate-700">Prev</button>
          <button v-for="p in pageNumbers" :key="p" @click="goToPage(p)" class="rounded border px-3 py-1" :class="p === pagination.current_page ? 'border-slate-800 bg-slate-800 text-white dark:border-slate-200 dark:bg-slate-200 dark:text-slate-900' : 'border-slate-300 dark:border-slate-700'">{{ p }}</button>
          <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="rounded border border-slate-300 px-3 py-1 disabled:opacity-50 dark:border-slate-700">Next</button>
        </div>
      </div>
    </div>

    <div v-if="showItemsDrawer" class="fixed inset-0 z-[990] flex">
      <div class="absolute inset-0 bg-black/45" @click="closeItemsDrawer"></div>
      <div class="relative ml-auto flex h-full w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-center justify-between bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-3 text-white">
          <div>
            <h3 class="text-base font-semibold">Lot {{ drawerLot?.lot_number }}</h3>
            <p class="text-xs opacity-90">{{ drawerItems.length }} available items</p>
          </div>
          <button @click="closeItemsDrawer" class="rounded bg-white/20 px-2 py-1 text-xs">Close</button>
        </div>

        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
          <input
            v-model="drawerSearch"
            placeholder="Search barcode"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
          />
        </div>

        <div class="flex-1 overflow-auto p-3 space-y-2">
          <div v-if="drawerLoading" class="py-8 text-center text-sm text-slate-500">Loading items...</div>
          <div v-else-if="filteredDrawerItems.length === 0" class="py-8 text-center text-sm text-slate-500">No items found.</div>

          <div
            v-for="item in filteredDrawerItems"
            :key="item.id"
            class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
          >
            <span class="font-mono text-xs">{{ item.barcode }}</span>
            <div class="flex items-center gap-2">
              <button @click="copyText(item.barcode)" class="rounded bg-indigo-600 px-2 py-1 text-[11px] font-semibold text-white">Copy</button>
              <button @click="confirmDamage(item)" class="rounded bg-rose-100 px-2 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-200">Damage</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showDamageModal" class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/60">
      <div class="w-full max-w-sm rounded-xl bg-white p-5 dark:bg-slate-900">
        <h3 class="text-base font-semibold text-rose-600">Mark as Damaged</h3>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Barcode: <span class="font-mono font-semibold">{{ damageItem?.barcode }}</span></p>
        <div class="mt-5 flex justify-end gap-2">
          <button @click="showDamageModal = false" class="rounded bg-slate-200 px-3 py-1.5 text-sm dark:bg-slate-700">Cancel</button>
          <button @click="markItemDamaged" :disabled="damaging" class="rounded bg-rose-600 px-3 py-1.5 text-sm text-white disabled:opacity-50">{{ damaging ? 'Processing...' : 'Confirm' }}</button>
        </div>
      </div>
    </div>

    <div v-if="showAddItemsModal" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/60">
      <div class="w-full max-w-sm rounded-xl bg-white p-5 dark:bg-slate-900">
        <h3 class="text-base font-semibold text-emerald-600">Add Items to Lot</h3>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Lot: <span class="font-semibold">{{ addItemsLot?.lot_number }}</span></p>
        <label class="mt-3 block text-sm font-medium">Quantity</label>
        <input v-model.number="addItemsCount" min="1" type="number" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950" />
        <div class="mt-5 flex justify-end gap-2">
          <button @click="showAddItemsModal = false" class="rounded bg-slate-200 px-3 py-1.5 text-sm dark:bg-slate-700">Cancel</button>
          <button @click="addItemsToLot" :disabled="addingItems" class="rounded bg-emerald-600 px-3 py-1.5 text-sm text-white disabled:opacity-50">{{ addingItems ? 'Adding...' : 'Add Items' }}</button>
        </div>
      </div>
    </div>

    <div v-if="showAdjustModal" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/60">
      <div class="w-full max-w-sm rounded-xl bg-white p-5 dark:bg-slate-900">
        <h3 class="text-base font-semibold text-amber-600">Adjust Lot Quantity</h3>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Lot: <span class="font-semibold">{{ adjustLot?.lot_number }}</span></p>
        <p class="mt-1 text-xs text-slate-500">Use positive to add items, negative to reserve/remove available items.</p>
        <label class="mt-3 block text-sm font-medium">Adjustment</label>
        <input v-model.number="adjustment" type="number" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-950" />
        <div class="mt-5 flex justify-end gap-2">
          <button @click="showAdjustModal = false" class="rounded bg-slate-200 px-3 py-1.5 text-sm dark:bg-slate-700">Cancel</button>
          <button @click="adjustLotQty" :disabled="adjusting" class="rounded bg-amber-600 px-3 py-1.5 text-sm text-white disabled:opacity-50">{{ adjusting ? 'Saving...' : 'Apply' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const lots = ref([])
const stats = ref({})

const filters = reactive({
  search: '',
  per_page: 15,
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const showItemsDrawer = ref(false)
const drawerLot = ref(null)
const drawerItems = ref([])
const drawerLoading = ref(false)
const drawerSearch = ref('')

const showDamageModal = ref(false)
const damageItem = ref(null)
const damaging = ref(false)

const showAddItemsModal = ref(false)
const addItemsLot = ref(null)
const addItemsCount = ref(1)
const addingItems = ref(false)

const showAdjustModal = ref(false)
const adjustLot = ref(null)
const adjustment = ref(0)
const adjusting = ref(false)

const pageNumbers = computed(() => {
  const pages = []
  const start = Math.max(1, pagination.current_page - 2)
  const end = Math.min(pagination.last_page, pagination.current_page + 2)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const filteredDrawerItems = computed(() => {
  const q = drawerSearch.value.trim().toLowerCase()
  if (!q) return drawerItems.value
  return drawerItems.value.filter((i) => String(i.barcode || '').toLowerCase().includes(q))
})

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

const variantLabel = (variant) => {
  const attrs = variant?.attributes || {}
  const text = Object.entries(attrs)
    .map(([k, v]) => `${k}:${stringifyAttrValue(v)}`)
    .filter((v) => !v.endsWith(':'))
    .join(', ')
  return text || '-'
}

const formatDate = (value) => {
  if (!value) return '-'
  return String(value).slice(0, 10)
}

const expiringClass = (value) => {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  const days = Math.floor((date.getTime() - Date.now()) / (1000 * 60 * 60 * 24))
  if (days < 0) return 'text-rose-600'
  if (days <= 30) return 'text-amber-600'
  return ''
}

const fetchLots = async (page = 1) => {
  try {
    const { data } = await axios.get('/api/lots/filter', {
      params: {
        search: filters.search || undefined,
        page,
        per_page: filters.per_page,
      },
    })

    lots.value = data?.data || []
    stats.value = data?.stats || {}

    pagination.current_page = data?.pagination?.current_page || 1
    pagination.last_page = data?.pagination?.last_page || 1
    pagination.per_page = data?.pagination?.per_page || filters.per_page
    pagination.total = data?.pagination?.total || 0
  } catch {
    toast.error('Failed to load lots.')
  }
}

const resetFilters = async () => {
  filters.search = ''
  await fetchLots(1)
}

const goToPage = async (page) => {
  if (page < 1 || page > pagination.last_page) return
  await fetchLots(page)
}

const copyText = async (text) => {
  try {
    await navigator.clipboard.writeText(String(text || ''))
    toast.success('Copied')
  } catch {
    toast.error('Copy failed')
  }
}

const copyRange = async (lot) => {
  if (!lot?.barcode_start) return
  await copyText(`${lot.barcode_start} -> ${lot.barcode_end}`)
}

const openItemsDrawer = async (lot) => {
  drawerLot.value = lot
  drawerItems.value = []
  drawerSearch.value = ''
  drawerLoading.value = true
  showItemsDrawer.value = true

  try {
    const { data } = await axios.get(`/api/lots/${lot.id}/items`, {
      params: { status: 'available' },
    })
    drawerItems.value = data?.items || []
  } catch {
    toast.error('Failed to load lot items.')
  } finally {
    drawerLoading.value = false
  }
}

const closeItemsDrawer = () => {
  showItemsDrawer.value = false
  drawerLot.value = null
  drawerItems.value = []
  drawerSearch.value = ''
}

const confirmDamage = (item) => {
  damageItem.value = item
  showDamageModal.value = true
}

const markItemDamaged = async () => {
  if (!damageItem.value) return
  damaging.value = true
  try {
    await axios.post(`/api/lot-items/${damageItem.value.id}/mark-damaged`)
    toast.success('Item marked as damaged')
    showDamageModal.value = false
    damageItem.value = null

    if (drawerLot.value) {
      await openItemsDrawer(drawerLot.value)
    }
    await fetchLots(pagination.current_page)
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to mark damaged')
  } finally {
    damaging.value = false
  }
}

const openAddItemsModal = (lot) => {
  addItemsLot.value = lot
  addItemsCount.value = 1
  showAddItemsModal.value = true
}

const addItemsToLot = async () => {
  if (!addItemsLot.value || !addItemsCount.value || addItemsCount.value < 1) return

  addingItems.value = true
  try {
    await axios.post(`/api/lots/${addItemsLot.value.id}/add-items`, {
      quantity: addItemsCount.value,
    })
    toast.success('Items added successfully')
    showAddItemsModal.value = false
    addItemsLot.value = null

    await fetchLots(pagination.current_page)
    if (drawerLot.value && showItemsDrawer.value) {
      await openItemsDrawer(drawerLot.value)
    }
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to add items')
  } finally {
    addingItems.value = false
  }
}

const openAdjustModal = (lot) => {
  adjustLot.value = lot
  adjustment.value = 0
  showAdjustModal.value = true
}

const adjustLotQty = async () => {
  if (!adjustLot.value || !adjustment.value) return

  adjusting.value = true
  try {
    await axios.post(`/api/lots/${adjustLot.value.id}/adjust`, {
      adjustment: adjustment.value,
    })
    toast.success('Lot adjusted successfully')
    showAdjustModal.value = false
    adjustLot.value = null

    await fetchLots(pagination.current_page)
    if (drawerLot.value && showItemsDrawer.value) {
      await openItemsDrawer(drawerLot.value)
    }
  } catch (e) {
    toast.error(e?.response?.data?.message || 'Failed to adjust lot')
  } finally {
    adjusting.value = false
  }
}

onMounted(async () => {
  await fetchLots(1)
})
</script>
