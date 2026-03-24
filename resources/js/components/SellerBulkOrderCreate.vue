<template>
  <div
    ref="bulkRoot"
    :class="[
      'space-y-6',
      isFullscreen ? 'fixed inset-0 z-[1300] overflow-y-auto bg-slate-50 p-4 dark:bg-slate-950 md:p-6' : '',
    ]"
  >
    <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-7 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-blue-600 dark:text-blue-300">Bulk Orders</p>
          <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white">Bulk Order Creation</h1>
          <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
            Select a product and variant, generate customer rows, fill details, then submit everything at once.
          </p>
        </div>

        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="toggleFullscreen"
        >
          <i :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
          {{ isFullscreen ? 'Exit Full Screen' : 'Full Screen' }}
        </button>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-6 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
      <div class="grid gap-4 lg:grid-cols-12">
        <div class="lg:col-span-5">
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Search Product</label>
          <div class="relative">
            <input
              v-model.trim="productQuery"
              type="search"
              placeholder="Search by product / code"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @focus="openProductDropdown"
              @input="onProductInput"
              @keydown.down.prevent="moveActiveProduct(1)"
              @keydown.up.prevent="moveActiveProduct(-1)"
              @keydown.enter.prevent="pickActiveProduct"
              @blur="closeProductDropdownWithDelay"
            />

            <div v-if="productDdOpen" class="absolute z-20 mt-2 max-h-64 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
              <button
                v-for="(product, idx) in suggestedProducts"
                :key="product.id"
                type="button"
                class="flex w-full items-start justify-between gap-3 border-b border-slate-100 px-3 py-2 text-left last:border-b-0 dark:border-slate-800"
                :class="idx === activeProductIndex ? 'bg-slate-100 dark:bg-slate-800' : ''"
                @mousemove="activeProductIndex = idx"
                @mousedown.prevent="chooseProduct(product)"
              >
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ product.title }}</p>
                  <p class="truncate text-xs text-slate-500 dark:text-slate-300">{{ product.product_code || 'No code' }}</p>
                </div>
                <span class="shrink-0 text-[11px] text-slate-500 dark:text-slate-300">{{ product.variants?.length || 0 }} variants</span>
              </button>

              <div v-if="!suggestedProducts.length" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                <span v-if="loadingProducts">Loading products...</span>
                <span v-else>No matching products.</span>
              </div>
            </div>
          </div>

          <p v-if="draftProduct" class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
            Selected: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ draftProduct.title }}</span>
          </p>
        </div>

        <div class="lg:col-span-5">
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Variant Selection</label>

          <div v-if="draftProduct" class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/60">
            <div v-if="variantAttributeKeys.length" class="grid gap-3 sm:grid-cols-2">
              <div v-for="attrKey in variantAttributeKeys" :key="attrKey">
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ attrKey }}</p>

                <div v-if="isColorAttribute(attrKey)" class="flex flex-wrap gap-2">
                  <button
                    v-for="option in variantAttributeOptions[attrKey]"
                    :key="`${attrKey}-${option.value}`"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-2 py-1 text-xs text-slate-700 hover:border-blue-500 dark:border-slate-700 dark:text-slate-200"
                    :class="selectedAttributes[attrKey] === option.value ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200' : ''"
                    @click="selectedAttributes[attrKey] = option.value"
                  >
                    <span class="inline-block h-4 w-4 rounded-full border border-white/70" :style="{ backgroundColor: option.color }"></span>
                    <span>{{ option.label }}</span>
                  </button>
                </div>

                <select
                  v-else
                  v-model="selectedAttributes[attrKey]"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                >
                  <option value="">Select {{ attrKey }}</option>
                  <option v-for="option in variantAttributeOptions[attrKey]" :key="`${attrKey}-${option.value}`" :value="option.value">{{ option.label }}</option>
                </select>
              </div>
            </div>

            <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">
              <span v-if="selectedVariant">
                Selected: <span class="font-semibold">{{ formatVariantLabel(selectedVariant) }}</span> | LKR {{ toMoney(selectedVariant.price) }}
              </span>
              <span v-else>Select all variant keys.</span>
            </p>
          </div>

          <p v-else class="rounded-xl border border-dashed border-slate-300 px-3 py-2 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
            Select a product first to choose variant keys.
          </p>
        </div>

        <div class="lg:col-span-2">
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Rows</label>
          <div class="flex gap-2">
            <input
              v-model.number="draft.rowsToGenerate"
              type="number"
              min="1"
              max="100"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            />
            <button
              type="button"
              class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700 disabled:opacity-50"
              :disabled="!canCreateBatch"
              @click="createBatch"
            >
              Add
            </button>
          </div>
          <div class="mt-2">
            <input
              ref="csvFileInput"
              type="file"
              accept=".csv,text/csv"
              class="hidden"
              @change="onCsvSelected"
            />
            <button
              type="button"
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              :disabled="!draftProduct || !selectedVariant || importingCsv"
              @click="openCsvPicker"
            >
              <span v-if="importingCsv">Importing...</span>
              <span v-else>Import CSV</span>
            </button>
            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">CSV headers: name, phone, address, city, quantity, notes</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!batches.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/80 p-10 text-center text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-400">
      Add a product batch to start bulk order creation.
    </div>

    <div v-else class="space-y-5">
      <article
        v-for="(batch, batchIndex) in batches"
        :key="batch.id"
        class="rounded-3xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80"
      >
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ batch.product_title }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ batch.variant_label }} | Price: LKR {{ toMoney(batch.price) }}</p>
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="appendRows(batch, 1)"
            >
              + Row
            </button>
            <button
              type="button"
              class="rounded-xl border border-rose-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-rose-700 hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-300 dark:hover:bg-rose-500/10"
              @click="removeBatch(batchIndex)"
            >
              Remove Batch
            </button>
          </div>
        </div>

        <div class="mt-4 overflow-x-auto">
          <table class="min-w-[1100px] w-full text-left">
            <thead>
              <tr class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                <th class="px-2 py-2">#</th>
                <th class="px-2 py-2">Customer Name</th>
                <th class="px-2 py-2">Phone</th>
                <th class="px-2 py-2">Additional Phone</th>
                <th class="px-2 py-2">Address</th>
                <th class="px-2 py-2">City</th>
                <th class="px-2 py-2">Qty</th>
                <th class="px-2 py-2">Notes</th>
                <th class="px-2 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, rowIndex) in batch.rows"
                :key="row.id"
                class="border-t border-slate-200 dark:border-slate-700"
              >
                <td class="px-2 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ rowIndex + 1 }}</td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.customer_name" type="text" :class="cellClass" placeholder="Customer name" />
                </td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.phone" type="text" :class="cellClass" placeholder="+9477xxxxxxx" />
                </td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.additional_phone" type="text" :class="cellClass" placeholder="Optional" />
                </td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.address" type="text" :class="cellClass" placeholder="Address" />
                </td>
                <td class="px-2 py-2">
                  <div class="relative">
                    <input
                      v-model.trim="row.city"
                      type="text"
                      :class="[
                        cellClass,
                        row._cityInvalid ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 dark:border-rose-500' : '',
                      ]"
                      placeholder="Search city"
                      @focus="openCityDropdown(row)"
                      @input="onCityInput(row)"
                      @blur="closeCityDropdownWithDelay(row)"
                    />
                    <div
                      v-if="row._cityOpen"
                      class="absolute z-20 mt-1 max-h-52 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                    >
                      <div v-if="row._cityLoading" class="px-3 py-2 text-[11px] text-slate-500 dark:text-slate-400">
                        Loading cities...
                      </div>
                      <button
                        v-for="cityOption in row._cityOptions"
                        :key="`city-${row.id}-${cityOption.id}`"
                        type="button"
                        class="block w-full border-b border-slate-100 px-3 py-2 text-left text-xs text-slate-700 last:border-b-0 hover:bg-slate-100 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-800"
                        @mousedown.prevent="selectCityForRow(row, cityOption)"
                      >
                        {{ cityOption.name_en }}
                      </button>
                      <div v-if="!row._cityLoading && !row._cityOptions.length" class="px-3 py-2 text-[11px] text-slate-500 dark:text-slate-400">
                        No cities found
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-2 py-2">
                  <input v-model.number="row.quantity" type="number" min="1" :class="cellClass" />
                </td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.notes" type="text" :class="cellClass" placeholder="Notes (optional)" />
                </td>
                <td class="px-2 py-2">
                  <div class="flex gap-1">
                    <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="duplicateRow(batch, row)">Copy</button>
                    <button type="button" class="rounded-lg border border-rose-300 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-rose-700 hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-300 dark:hover:bg-rose-500/10" @click="removeRow(batch, rowIndex)">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </div>

    <aside class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
      <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Product Batches</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ batches.length }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Total Orders</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ totalRows }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Estimated Value</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">LKR {{ toMoney(estimatedValue) }}</p>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
        <button
          type="button"
          class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="resetAll"
        >
          Clear All
        </button>
        <button
          type="button"
          class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="submitting || !batches.length"
          @click="submitBulk"
        >
          <span v-if="submitting">Submitting...</span>
          <span v-else>Submit All Orders</span>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const bulkRoot = ref(null)
const isFullscreen = ref(false)
const loadingProducts = ref(false)
const importingCsv = ref(false)
const submitting = ref(false)
const productQuery = ref('')
const productDdOpen = ref(false)
const activeProductIndex = ref(-1)
let productSearchTimer = null
const csvFileInput = ref(null)
const products = ref([])
const batches = ref([])

const draft = reactive({
  productId: null,
  rowsToGenerate: 5,
})

const cellClass = 'w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100'

const selectedAttributes = reactive({})
const draftProduct = computed(() => products.value.find((p) => p.id === Number(draft.productId)) || null)
const normalizedDraftVariants = computed(() => {
  const variants = draftProduct.value?.variants || []
  return variants.map((variant) => {
    const rawAttrs = variant?.attributes || {}
    const attrs = {}

    for (const [key, rawMeta] of Object.entries(rawAttrs)) {
      if (rawMeta && typeof rawMeta === 'object') {
        const label = String(rawMeta.label || rawMeta.name || rawMeta.value || rawMeta.color || '').trim()
        const value = String(rawMeta.value || rawMeta.name || rawMeta.color || label).trim().toLowerCase()
        attrs[key] = { value, label: label || value, color: rawMeta.color || null }
      } else {
        const label = String(rawMeta || '').trim()
        attrs[key] = { value: label.toLowerCase(), label, color: null }
      }
    }

    return {
      ...variant,
      attributes: attrs,
    }
  })
})
const variantAttributeOptions = computed(() => {
  const map = {}

  for (const variant of normalizedDraftVariants.value) {
    const attrs = variant?.attributes || {}
    for (const [key, meta] of Object.entries(attrs)) {
      if (!map[key]) map[key] = []
      const exists = map[key].some((row) => row.value === meta.value)
      if (!exists) {
        map[key].push({ value: meta.value, label: meta.label, color: meta.color || null })
      }
    }
  }

  return map
})
const variantAttributeKeys = computed(() => Object.keys(variantAttributeOptions.value))
const selectedVariant = computed(() => {
  const keys = variantAttributeKeys.value
  if (!keys.length) return normalizedDraftVariants.value[0] || null

  for (const key of keys) {
    if (!selectedAttributes[key]) return null
  }

  return normalizedDraftVariants.value.find((variant) => {
    const attrs = variant?.attributes || {}
    return keys.every((key) => (attrs[key]?.value || '') === (selectedAttributes[key] || ''))
  }) || null
})
const canCreateBatch = computed(() => !!draftProduct.value && !!selectedVariant.value && Number(draft.rowsToGenerate || 0) > 0)
const suggestedProducts = computed(() => {
  const q = productQuery.value.trim().toLowerCase()
  if (!q) return products.value.slice(0, 60)

  return products.value
    .filter((product) => {
      const title = String(product?.title || '').toLowerCase()
      const code = String(product?.product_code || '').toLowerCase()
      return title.includes(q) || code.includes(q)
    })
    .slice(0, 80)
})

const totalRows = computed(() => batches.value.reduce((sum, batch) => sum + batch.rows.length, 0))
const estimatedValue = computed(() => batches.value.reduce((sum, batch) => {
  return sum + batch.rows.reduce((batchSum, row) => batchSum + (Number(row.quantity || 0) * Number(batch.price || 0)), 0)
}, 0))

const rowId = () => `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`

const createEmptyRow = () => ({
  id: rowId(),
  customer_name: '',
  phone: '',
  additional_phone: '',
  email: '',
  address: '',
  city_id: null,
  city: '',
  quantity: 1,
  notes: '',
  _cityOpen: false,
  _cityLoading: false,
  _cityOptions: [],
  _cityTimer: null,
  _cityInvalid: false,
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const isColorAttribute = (key) => {
  const options = variantAttributeOptions.value[key] || []
  return options.length > 0 && options.every((opt) => !!opt.color)
}

const formatVariantLabel = (variant) => {
  const attrs = variant?.attributes && typeof variant.attributes === 'object'
    ? Object.entries(variant.attributes).map(([key, meta]) => `${key}: ${meta?.label || meta?.value || ''}`).filter(Boolean)
    : []
  const attrText = attrs.join(', ')
  const sku = variant?.sku ? `SKU ${variant.sku}` : 'Variant'
  return attrText ? `${sku} | ${attrText}` : sku
}

const fetchProducts = async (search = '') => {
  loadingProducts.value = true
  try {
    const { data } = await axios.get('/api/seller/order-products', {
      params: {
        search: search || undefined,
        limit: 100,
      },
    })
    products.value = Array.isArray(data?.products) ? data.products : []
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load products.')
  } finally {
    loadingProducts.value = false
  }
}

const openProductDropdown = () => {
  productDdOpen.value = true
}

const closeProductDropdownWithDelay = () => {
  window.setTimeout(() => {
    productDdOpen.value = false
  }, 120)
}

const chooseProduct = (product) => {
  draft.productId = product?.id || null
  productQuery.value = product?.title || ''
  productDdOpen.value = false
  activeProductIndex.value = -1
}

const moveActiveProduct = (delta) => {
  if (!productDdOpen.value) {
    productDdOpen.value = true
  }

  const list = suggestedProducts.value
  if (!list.length) return

  const current = Number(activeProductIndex.value || 0)
  const next = (current + delta + list.length) % list.length
  activeProductIndex.value = next
}

const pickActiveProduct = () => {
  const list = suggestedProducts.value
  if (!list.length) return

  const idx = activeProductIndex.value >= 0 ? activeProductIndex.value : 0
  chooseProduct(list[idx])
}

const onProductInput = () => {
  productDdOpen.value = true
  activeProductIndex.value = -1

  if (productSearchTimer) {
    window.clearTimeout(productSearchTimer)
  }
  productSearchTimer = window.setTimeout(() => {
    fetchProducts(productQuery.value.trim())
  }, 220)
}

const createBatch = () => {
  if (!canCreateBatch.value) return

  const count = Math.max(1, Number(draft.rowsToGenerate || 1))
  const selectedProduct = draftProduct.value
  const chosenVariant = selectedVariant.value

  batches.value.push({
    ...buildBatchHeader(selectedProduct, chosenVariant),
    rows: Array.from({ length: count }, () => createEmptyRow()),
  })

  draft.rowsToGenerate = 5
}

const buildBatchHeader = (selectedProduct, chosenVariant) => ({
  id: rowId(),
  product_id: selectedProduct.id,
  product_title: selectedProduct.title,
  product_code: selectedProduct.product_code || '',
  variant_id: chosenVariant.id,
  variant_label: formatVariantLabel(chosenVariant),
  price: Number(chosenVariant.price || 0),
})

const appendRows = (batch, count = 1) => {
  const rows = Array.from({ length: Math.max(1, Number(count || 1)) }, () => createEmptyRow())
  batch.rows.push(...rows)
}

const removeBatch = (batchIndex) => {
  batches.value.splice(batchIndex, 1)
}

const removeRow = (batch, rowIndex) => {
  batch.rows.splice(rowIndex, 1)
  if (!batch.rows.length) {
    batch.rows.push(createEmptyRow())
  }
}

const duplicateRow = (batch, sourceRow) => {
  batch.rows.push({
    ...sourceRow,
    id: rowId(),
    _cityOpen: false,
    _cityLoading: false,
    _cityOptions: [],
    _cityTimer: null,
    _cityInvalid: false,
  })
}

const loadCitiesForRow = async (row, search = '') => {
  row._cityLoading = true
  try {
    const { data } = await axios.get('/api/seller/cities', {
      params: {
        search: search || undefined,
        limit: 20,
      },
    })
    row._cityOptions = Array.isArray(data?.cities) ? data.cities : []
  } catch {
    row._cityOptions = []
  } finally {
    row._cityLoading = false
  }
}

const openCityDropdown = (row) => {
  row._cityOpen = true
  loadCitiesForRow(row, row.city || '')
}

const closeCityDropdownWithDelay = (row) => {
  window.setTimeout(() => {
    row._cityOpen = false
  }, 120)
}

const onCityInput = (row) => {
  row.city_id = null
  row._cityInvalid = false
  row._cityOpen = true

  if (row._cityTimer) {
    window.clearTimeout(row._cityTimer)
  }
  row._cityTimer = window.setTimeout(() => {
    loadCitiesForRow(row, row.city || '')
  }, 200)
}

const selectCityForRow = (row, cityOption) => {
  row.city_id = Number(cityOption?.id || 0) || null
  row.city = String(cityOption?.name_en || '').trim()
  row._cityInvalid = false
  row._cityOpen = false
}

const openCsvPicker = () => {
  if (!draftProduct.value || !selectedVariant.value) {
    toast.error('Select product and variant first.')
    return
  }
  csvFileInput.value?.click()
}

const normalizeHeader = (value) => String(value || '').trim().toLowerCase().replace(/[^a-z0-9]+/g, '')

const detectDelimiter = (text) => {
  const sample = String(text || '').split(/\r?\n/).find((line) => line.trim() !== '') || ''
  const candidates = [',', ';', '\t']
  let winner = ','
  let bestScore = -1

  candidates.forEach((delimiter) => {
    const score = sample.split(delimiter).length
    if (score > bestScore) {
      bestScore = score
      winner = delimiter
    }
  })

  return winner
}

const parseDelimited = (text, delimiter) => {
  const rows = []
  let current = []
  let field = ''
  let inQuotes = false

  for (let i = 0; i < text.length; i += 1) {
    const char = text[i]
    const next = text[i + 1]

    if (char === '"') {
      if (inQuotes && next === '"') {
        field += '"'
        i += 1
      } else {
        inQuotes = !inQuotes
      }
      continue
    }

    if (!inQuotes && char === delimiter) {
      current.push(field.trim())
      field = ''
      continue
    }

    if (!inQuotes && (char === '\n' || char === '\r')) {
      if (char === '\r' && next === '\n') i += 1
      current.push(field.trim())
      field = ''

      const hasData = current.some((cell) => String(cell || '').trim() !== '')
      if (hasData) rows.push(current)
      current = []
      continue
    }

    field += char
  }

  if (field !== '' || current.length) {
    current.push(field.trim())
    const hasData = current.some((cell) => String(cell || '').trim() !== '')
    if (hasData) rows.push(current)
  }

  return rows
}

const parseQuantity = (value) => {
  const parsed = Number.parseInt(String(value || '').replace(/[^0-9-]/g, ''), 10)
  return Number.isFinite(parsed) && parsed > 0 ? parsed : 1
}

const extractByAliases = (cells, indexByHeader, aliases, fallbackIndex = -1) => {
  const headerIdx = aliases.map((alias) => indexByHeader[alias]).find((idx) => Number.isInteger(idx) && idx >= 0)
  if (Number.isInteger(headerIdx) && headerIdx >= 0) return String(cells[headerIdx] || '').trim()
  if (fallbackIndex >= 0) return String(cells[fallbackIndex] || '').trim()
  return ''
}

const mapCsvRows = (matrix) => {
  if (!Array.isArray(matrix) || !matrix.length) return []

  const normalizedHeaders = matrix[0].map(normalizeHeader)
  const knownHeaderTokens = ['name', 'customername', 'phone', 'address', 'city', 'quantity', 'qty', 'notes']
  const hasHeader = normalizedHeaders.some((h) => knownHeaderTokens.includes(h))
  const dataRows = hasHeader ? matrix.slice(1) : matrix

  const indexByHeader = {}
  if (hasHeader) {
    normalizedHeaders.forEach((header, idx) => {
      if (header) indexByHeader[header] = idx
    })
  }

  return dataRows
    .map((cells) => {
      const customerName = extractByAliases(cells, indexByHeader, ['customername', 'name', 'customer'], 0)
      const phone = extractByAliases(cells, indexByHeader, ['phone', 'mobile', 'primaryphone', 'contactnumber'], 1)
      const additionalPhone = extractByAliases(cells, indexByHeader, ['additionalphone', 'altphone', 'secondaryphone'], 2)
      const email = extractByAliases(cells, indexByHeader, ['email', 'mail'], 3)
      const address = extractByAliases(cells, indexByHeader, ['address', 'customeraddress'], 4)
      const city = extractByAliases(cells, indexByHeader, ['city', 'town'], 5)
      const quantityRaw = extractByAliases(cells, indexByHeader, ['quantity', 'qty', 'q'], 6)
      const notes = extractByAliases(cells, indexByHeader, ['notes', 'note', 'remark', 'remarks'], 7)

      if (!customerName && !phone && !address) return null

      return {
        ...createEmptyRow(),
        customer_name: customerName,
        phone,
        additional_phone: additionalPhone,
        email,
        address,
        city,
        quantity: parseQuantity(quantityRaw),
        notes,
      }
    })
    .filter(Boolean)
}

const resolveCitiesForImportedRows = async (rows) => {
  const names = rows
    .map((row) => String(row.city || '').trim())
    .filter((name) => name !== '')

  if (!names.length) return

  let matches = {}
  try {
    const { data } = await axios.post('/api/seller/cities/resolve', { names })
    matches = data?.matches && typeof data.matches === 'object' ? data.matches : {}
  } catch {
    matches = {}
  }

  rows.forEach((row) => {
    const key = String(row.city || '').trim().toLowerCase()
    if (!key) return

    const matched = matches[key]
    if (matched?.id) {
      row.city_id = Number(matched.id)
      row.city = String(matched.name_en || '').trim()
      row._cityInvalid = false
    } else {
      row.city_id = null
      row.city = ''
      row._cityInvalid = true
    }
  })
}

const onCsvSelected = async (event) => {
  const file = event?.target?.files?.[0]
  if (!file) return

  if (!draftProduct.value || !selectedVariant.value) {
    toast.error('Select product and variant first.')
    event.target.value = ''
    return
  }

  importingCsv.value = true
  try {
    const text = await file.text()
    const delimiter = detectDelimiter(text)
    const matrix = parseDelimited(text, delimiter)
    const importedRows = mapCsvRows(matrix)

    if (!importedRows.length) {
      toast.error('No valid customer rows found in CSV.')
      return
    }

    await resolveCitiesForImportedRows(importedRows)

    const selectedProduct = draftProduct.value
    const chosenVariant = selectedVariant.value
    let batch = batches.value.find((b) => Number(b.product_id) === Number(selectedProduct.id) && Number(b.variant_id) === Number(chosenVariant.id))
    if (!batch) {
      batch = {
        ...buildBatchHeader(selectedProduct, chosenVariant),
        rows: [],
      }
      batches.value.push(batch)
    }

    batch.rows.push(...importedRows)
    const invalidCityCount = importedRows.filter((row) => row._cityInvalid).length
    if (invalidCityCount > 0) {
      toast.warning(`${importedRows.length} rows imported. ${invalidCityCount} city value(s) were not matched and need manual selection.`)
    } else {
      toast.success(`${importedRows.length} rows imported.`)
    }
  } catch (error) {
    toast.error('Failed to parse CSV file.')
  } finally {
    importingCsv.value = false
    if (event?.target) event.target.value = ''
  }
}

const normalizePhone = (value) => String(value || '').replace(/\s+/g, '').trim()

const validateBeforeSubmit = () => {
  if (!batches.value.length) {
    toast.error('Add at least one product batch.')
    return false
  }

  for (let bi = 0; bi < batches.value.length; bi += 1) {
    const batch = batches.value[bi]
    if (!batch.rows.length) {
      toast.error(`Batch ${bi + 1} has no rows.`)
      return false
    }

    for (let ri = 0; ri < batch.rows.length; ri += 1) {
      const row = batch.rows[ri]
      const customerName = String(row.customer_name || '').trim()
      const phone = normalizePhone(row.phone)
      const address = String(row.address || '').trim()
      const qty = Number(row.quantity || 0)
      const cityId = Number(row.city_id || 0)

      if (customerName.length < 2) {
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: customer name is required.`)
        return false
      }
      if (!/^\+?[0-9]{8,15}$/.test(phone)) {
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: phone number is invalid.`)
        return false
      }
      if (address.length < 6) {
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: address is required.`)
        return false
      }
      if (!Number.isFinite(qty) || qty < 1) {
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: quantity must be at least 1.`)
        return false
      }
      if (!Number.isInteger(cityId) || cityId <= 0) {
        row.city = ''
        row._cityInvalid = true
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: select a valid city from the list.`)
        return false
      }
    }
  }

  return true
}

const buildPayload = () => {
  const orders = []

  batches.value.forEach((batch) => {
    batch.rows.forEach((row) => {
      orders.push({
        customer: {
          name: String(row.customer_name || '').trim(),
          phone: normalizePhone(row.phone),
          additional_phone: String(row.additional_phone || '').trim() || null,
          email: String(row.email || '').trim() || null,
          address: String(row.address || '').trim(),
          city_id: Number(row.city_id || 0) || null,
          city: String(row.city || '').trim() || null,
          notes: String(row.notes || '').trim() || null,
        },
        items: [
          {
            product_id: Number(batch.product_id),
            product_variant_id: Number(batch.variant_id),
            quantity: Math.max(1, Number(row.quantity || 1)),
            price: Number(batch.price || 0),
          },
        ],
      })
    })
  })

  return { orders }
}

const submitBulk = async () => {
  if (!validateBeforeSubmit()) return

  submitting.value = true
  try {
    const { data } = await axios.post('/api/seller/orders/bulk', buildPayload())
    const createdCount = Number(data?.created_count || 0)
    toast.success(`${createdCount} orders submitted successfully.`)
    resetAll()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to submit bulk orders.')
  } finally {
    submitting.value = false
  }
}

const resetAll = () => {
  batches.value = []
}

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
}

onMounted(() => {
  fetchProducts('')
})

watch(() => draft.productId, () => {
  Object.keys(selectedAttributes).forEach((key) => {
    delete selectedAttributes[key]
  })

  const keys = variantAttributeKeys.value
  keys.forEach((key) => {
    selectedAttributes[key] = variantAttributeOptions.value[key]?.[0]?.value || ''
  })
})
</script>
