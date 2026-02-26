<template>
  <div class="w-full p-6 text-slate-700 dark:text-slate-200">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
      <div>
        <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Inventory</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ isEditing ? 'Edit Product' : 'New Product' }}</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Create products with images, rich description, and variant stock setup.</p>
      </div>
      <button
        @click="goToProducts"
        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
      >
        Back to Products
      </button>
    </div>

    <form @submit.prevent="saveProduct" class="modern-form space-y-5">
      <div class="grid gap-5 lg:grid-cols-2">
        <div class="space-y-5">

          <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
            <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Product Title</label>
            <input v-model="form.title" required type="text" placeholder="Enter product title" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-500/20" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Product Code</label>
            <input v-model="form.product_code" required type="text" placeholder="e.g. PRD-1001" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-500/20" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Category</label>
            <select v-model="form.category_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-500/20">
              <option :value="null">Select category</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Delivery Fee</label>
            <select
              v-model="form.delivery_fee_id"
              :disabled="form.is_free_shipping"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition disabled:cursor-not-allowed disabled:opacity-50 focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-500/20"
            >
              <option :value="null">Use default delivery fee</option>
              <option v-for="fee in deliveryFees" :key="fee.id" :value="fee.id">
                LKR {{ Number(fee.fee || 0).toFixed(2) }}{{ fee.is_default ? ' (Default)' : '' }}
              </option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
              <input type="checkbox" v-model="form.is_free_shipping" class="rounded border-slate-300 accent-slate-700 dark:border-slate-700 dark:accent-slate-300" />
              Free Shipping
            </label>
          </div>
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">SKU (for no variants)</label>
            <input v-model="form.sku" :disabled="form.hasVariants === 'yes'" type="text" placeholder="e.g. SKU-001" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition disabled:cursor-not-allowed disabled:opacity-50 focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-500/20" />
          </div>
          <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">
              Small Description
            </label>

            <textarea
              v-model="form.small_description"
              required
              rows="3"
              placeholder="Short summary for product card/listing"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-500/20 resize-none"
            ></textarea>
          </div>
        </div>

        <div class="mt-4 rounded-xl border border-slate-200/70 bg-slate-50/70 p-3 dark:border-slate-700 dark:bg-slate-800/50">
          <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Handle Images</label>
          <div
            class="cursor-pointer rounded-xl border border-dashed border-slate-300 bg-white px-4 py-5 text-center text-sm text-slate-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300"
            @drop.prevent="handleDrop"
            @dragover.prevent
            @click="openFilePicker"
          >
            Drag & drop or click to upload
          </div>
          <input ref="imageInputRef" type="file" multiple class="hidden" @change="handleImageUpload" />

          <div v-if="form.images.length" class="mt-3 space-y-3">
            <button
              type="button"
              class="w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-900"
              @click="openImagePreview(selectedImagePreview || form.images[0]?.preview)"
            >
              <img
                :src="selectedImagePreview || form.images[0]?.preview"
                class="h-52 w-full object-cover"
                alt="Selected product image"
              />
            </button>

            <div class="grid grid-cols-4 gap-3">
              <div
                v-for="(image, index) in form.images"
                :key="image.preview"
                draggable="true"
                @dragstart="onImageDragStart(index)"
                @dragover.prevent
                @drop="onImageDrop(index)"
                class="group relative cursor-move rounded-lg border border-slate-200 p-1 dark:border-slate-700"
              >
                <img
                  :src="image.preview"
                  class="h-20 w-full rounded-md object-cover"
                  :class="[
                    image.is_primary ? 'ring-2 ring-emerald-500' : '',
                    selectedImagePreview === image.preview ? 'ring-2 ring-slate-900 dark:ring-slate-300' : ''
                  ]"
                  @click="selectedImagePreview = image.preview"
                />
                <button type="button" @click="removeImage(index)" class="absolute right-1 top-1 rounded bg-black/60 px-1 text-xs text-white">✖</button>
                <span v-if="image.is_primary" class="absolute bottom-1 left-1 rounded bg-emerald-600/90 px-1 text-[10px] text-white">Primary</span>
                <button
                  type="button"
                  @click.stop="openImagePreview(image.preview)"
                  class="absolute inset-0 hidden items-center justify-center rounded-md bg-black/40 text-[10px] font-semibold uppercase tracking-wide text-white group-hover:flex"
                >
                  View
                </button>
              </div>
            </div>
          </div>
        </div>
          </div>

          <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
            <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Long Description</label>
            <div ref="quillEditorRef" class="min-h-[300px] overflow-hidden rounded-xl border border-slate-300 bg-white dark:border-slate-700 dark:bg-slate-950 [&_.ql-toolbar.ql-snow]:border-0 [&_.ql-toolbar.ql-snow]:border-b [&_.ql-toolbar.ql-snow]:border-slate-300 [&_.ql-toolbar.ql-snow]:bg-slate-50 dark:[&_.ql-toolbar.ql-snow]:border-slate-700 dark:[&_.ql-toolbar.ql-snow]:bg-slate-900 [&_.ql-container.ql-snow]:border-0 [&_.ql-editor]:text-slate-900 dark:[&_.ql-editor]:text-slate-100"></div>
          </div>

          <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
            <div class="mb-4">
              <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Has Variants?</label>
              <div class="flex gap-6 text-sm text-slate-700 dark:text-slate-200">
                <label class="inline-flex items-center gap-2"><input class="accent-slate-700 dark:accent-slate-300" type="radio" value="yes" v-model="form.hasVariants" /> Yes</label>
                <label class="inline-flex items-center gap-2"><input class="accent-slate-700 dark:accent-slate-300" type="radio" value="no" v-model="form.hasVariants" /> No</label>
              </div>
            </div>

            <div v-if="form.hasVariants === 'no'" class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-xs font-semibold">Price</label>
                <input type="number" min="0" step="0.01" v-model.number="form.price" placeholder="0.00" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold">Stock Quantity</label>
                <input type="number" min="0" v-model.number="form.stock_quantity" placeholder="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold">Reorder Level</label>
                <input type="number" min="0" v-model.number="form.reorder_level" placeholder="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" />
              </div>
            </div>

            <div v-else class="space-y-4">
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="attribute in attributes" :key="attribute.id" class="rounded-xl border border-slate-200/70 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/50">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ attribute.name }}</p>
                  <p class="mb-2 text-xs text-slate-500">{{ attribute.type }}</p>
                  <div class="max-h-36 space-y-2 overflow-y-auto pr-1">
                    <label
                      v-for="val in normalizedAttributeValues(attribute)"
                      :key="`${attribute.id}-${val.key}`"
                      class="flex cursor-pointer items-center gap-2 text-xs"
                    >
                      <input class="accent-slate-700 dark:accent-slate-300" type="checkbox" :checked="isSelected(attribute.slug, val.key)" @change="toggleValue(attribute.slug, val, $event.target.checked)" />
                      <template v-if="attribute.type === 'color'">
                        <span class="inline-block h-4 w-4 rounded border" :style="{ backgroundColor: val.color }"></span>
                        <span>{{ val.name }}</span>
                      </template>
                      <template v-else>
                        <span>{{ val.label }}</span>
                      </template>
                    </label>
                  </div>
                </div>
              </div>

              <button
                type="button"
                @click="generateCombinations"
                class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
              >
                Generate Variant Rows
              </button>

              <div v-if="variants.length" class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <tr>
                      <th v-for="key in variantKeys" :key="key" class="px-2 py-2 text-left capitalize">{{ key }}</th>
                      <th class="px-2 py-2 text-left">SKU</th>
                      <th class="px-2 py-2 text-left">Price</th>
                      <th class="px-2 py-2 text-left">Stock</th>
                      <th class="px-2 py-2 text-left">Reorder</th>
                      <th class="px-2 py-2 text-left">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(variant, i) in variants" :key="variant.key || i" class="border-b border-slate-200 dark:border-slate-700">
                      <td v-for="key in variantKeys" :key="key" class="px-2 py-2">
                        <span v-if="isColorValue(variant.attributes[key])" class="inline-flex items-center gap-2">
                          <span class="h-4 w-4 rounded border" :style="{ backgroundColor: variant.attributes[key].color }"></span>
                          <span>{{ variant.attributes[key].name }}</span>
                        </span>
                        <span v-else>{{ variant.attributes[key] || '—' }}</span>
                      </td>
                      <td class="px-2 py-2"><input type="text" v-model="variant.sku" placeholder="SKU" class="w-28 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" /></td>
                      <td class="px-2 py-2"><input type="number" min="0" step="0.01" v-model.number="variant.price" placeholder="0.00" class="w-20 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" /></td>
                      <td class="px-2 py-2"><input type="number" min="0" v-model.number="variant.stock_quantity" placeholder="0" class="w-20 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" /></td>
                      <td class="px-2 py-2"><input type="number" min="0" v-model.number="variant.reorder_level" placeholder="0" class="w-20 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" /></td>
                      <td class="px-2 py-2"><button type="button" @click="variants.splice(i, 1)" class="rounded bg-rose-600 px-2 py-1 text-xs text-white">Remove</button></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="min-h-[640px] rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
          <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-[0.7rem] uppercase tracking-[0.25em] text-slate-400 dark:text-slate-500">Level Design</p>
              <h3 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">Commissions by Level</h3>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Set commission values for each level and apply one mode for all levels.</p>
            </div>

            <div class="inline-flex rounded-xl border border-slate-300 bg-white p-1 dark:border-slate-700 dark:bg-slate-950">
              <button
                type="button"
                @click="setLevelMode('percentage')"
                class="rounded-lg px-3 py-1.5 text-xs font-semibold uppercase tracking-wide transition"
                :class="levelMode === 'percentage'
                  ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                  : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
              >
                Percentage
              </button>
              <button
                type="button"
                @click="setLevelMode('amount')"
                class="rounded-lg px-3 py-1.5 text-xs font-semibold uppercase tracking-wide transition"
                :class="levelMode === 'amount'
                  ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                  : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
              >
                Amount
              </button>
            </div>
          </div>

          <div v-if="!levelRows.length" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-400">
            No levels found. Create levels first.
          </div>

          <div v-else class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="w-full text-sm">
              <thead class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <tr>
                  <th class="px-3 py-2 text-left">Level</th>
                  <th class="px-3 py-2 text-left">{{ levelMode === 'percentage' ? 'Commission %' : 'Commission Amount' }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in levelRows"
                  :key="row.level_id"
                  class="border-t border-slate-200 dark:border-slate-700"
                >
                  <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                    {{ row.level_label }}
                  </td>
                  <td class="px-3 py-2">
                    <div class="relative w-40">
                      <input
                        v-model.number="row.value"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pr-8 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                      />
                      <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-500 dark:text-slate-400">
                        {{ levelMode === 'percentage' ? '%' : 'LKR' }}
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
          <input type="checkbox" v-model="form.is_active" class="rounded border-slate-300 accent-slate-700 dark:border-slate-700 dark:accent-slate-300" />
          Active Product
        </label>
        <button type="submit" :disabled="saving" class="rounded-xl bg-slate-900 px-8 py-2.5 text-xs font-bold uppercase tracking-wide text-white hover:bg-black disabled:opacity-50 dark:bg-white dark:text-slate-900">
          {{ saving ? 'Saving...' : 'Save Product' }}
        </button>
      </div>
    </form>
  </div>

  <transition name="fade">
    <div
      v-if="showImagePreviewModal"
      class="fixed inset-0 z-[999] flex items-center justify-center bg-black/80 p-4"
      @click.self="showImagePreviewModal = false"
    >
      <div class="relative max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-xl">
        <img :src="previewModalImage" class="max-h-[90vh] w-full object-contain" alt="Full product image" />
        <button
          type="button"
          @click="showImagePreviewModal = false"
          class="absolute right-3 top-3 rounded bg-black/70 px-2 py-1 text-xs font-semibold text-white"
        >
          Close
        </button>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed, nextTick, onMounted, onBeforeUnmount, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const productId = ref(null)
const isEditing = computed(() => productId.value !== null)

const categories = ref([])
const attributes = ref([])
const levels = ref([])
const deliveryFees = ref([])
const defaultDeliveryFeeId = ref(null)
const imageInputRef = ref(null)
const quillEditorRef = ref(null)
const quillInstance = ref(null)
const saving = ref(false)
const levelMode = ref('percentage')
const levelRows = ref([])
const selectedImagePreview = ref('')
const dragImageIndex = ref(null)
const showImagePreviewModal = ref(false)
const previewModalImage = ref('')

const selectedValues = ref({})
const variants = ref([])

const form = ref({
  title: '',
  product_code: '',
  sku: '',
  category_id: null,
  delivery_fee_id: null,
  is_free_shipping: false,
  small_description: '',
  long_description: '',
  hasVariants: 'no',
  is_active: true,
  images: [],
  price: null,
  stock_quantity: 0,
  reorder_level: 0,
})

const goToProducts = () => {
  window.location.href = '/products'
}

const loadOptions = async () => {
  try {
    const { data } = await axios.get('/api/products/create-options')
    categories.value = data.categories || []
    attributes.value = data.attributes || []
    levels.value = data.levels || []
    deliveryFees.value = data.delivery_fees || []
    defaultDeliveryFeeId.value = data.default_delivery_fee_id || null
    if (!form.value.delivery_fee_id && defaultDeliveryFeeId.value) {
      form.value.delivery_fee_id = defaultDeliveryFeeId.value
    }
    initializeLevelRows()
  } catch {
    toast.error('Failed to load categories, attributes, and levels.')
  }
}

const detectEditMode = () => {
  const match = window.location.pathname.match(/^\/products\/(\d+)\/edit$/)
  productId.value = match ? Number(match[1]) : null
}

const initializeLevelRows = () => {
  const existingByLevelId = new Map((levelRows.value || []).map((row) => [row.level_id, row]))

  levelRows.value = levels.value.map((level) => {
    const existing = existingByLevelId.get(level.id)
    return {
      level_id: level.id,
      level_label: level.level_name || `Level ${level.level_no}`,
      type: levelMode.value,
      value: existing ? Number(existing.value || 0) : 0,
    }
  })
}

const setLevelMode = (mode) => {
  if (!['percentage', 'amount'].includes(mode)) return
  levelMode.value = mode
  levelRows.value = levelRows.value.map((row) => ({
    ...row,
    type: mode,
  }))
}

const ensureQuillAssets = () => {
  return new Promise((resolve, reject) => {
    if (window.Quill) {
      resolve()
      return
    }

    const existingCss = document.querySelector('link[data-quill="1"]')
    if (!existingCss) {
      const css = document.createElement('link')
      css.rel = 'stylesheet'
      css.href = 'https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css'
      css.setAttribute('data-quill', '1')
      document.head.appendChild(css)
    }

    const existingScript = document.querySelector('script[data-quill="1"]')
    if (existingScript) {
      existingScript.addEventListener('load', () => resolve())
      existingScript.addEventListener('error', () => reject(new Error('Failed to load Quill script')))
      return
    }

    const script = document.createElement('script')
    script.src = 'https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js'
    script.async = true
    script.setAttribute('data-quill', '1')
    script.onload = () => resolve()
    script.onerror = () => reject(new Error('Failed to load Quill script'))
    document.body.appendChild(script)
  })
}

const initQuill = async () => {
  try {
    await ensureQuillAssets()
    if (!quillEditorRef.value) return

    quillInstance.value = new window.Quill(quillEditorRef.value, {
      theme: 'snow',
      modules: {
        toolbar: [
          [{ header: [1, 2, false] }],
          ['bold', 'italic', 'underline'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          ['link', 'clean'],
        ],
      },
      placeholder: 'Write detailed product description...',
    })

    quillInstance.value.on('text-change', () => {
      form.value.long_description = quillInstance.value.root.innerHTML
    })

    quillInstance.value.root.innerHTML = form.value.long_description || ''
  } catch {
    toast.error('Failed to initialize Quill editor.')
  }
}

const openFilePicker = () => {
  imageInputRef.value?.click()
}

const uploadOneImage = async (file) => {
  const fd = new FormData()
  fd.append('image', file)
  const { data } = await axios.post('/api/products/upload-image', fd)

  form.value.images.push({
    preview: URL.createObjectURL(file),
    path: data.path,
    is_primary: false,
  })
  ensureFirstImagePrimary()
}

const handleImageUpload = async (event) => {
  const files = Array.from(event.target?.files || [])
  for (const file of files) {
    try {
      await uploadOneImage(file)
    } catch {
      toast.error(`Failed to upload ${file.name}`)
    }
  }
  event.target.value = ''
}

const handleDrop = async (event) => {
  const files = Array.from(event.dataTransfer?.files || [])
  for (const file of files) {
    try {
      await uploadOneImage(file)
    } catch {
      toast.error(`Failed to upload ${file.name}`)
    }
  }
}

const ensureFirstImagePrimary = () => {
  form.value.images.forEach((image, i) => {
    image.is_primary = i === 0
  })

  if (!selectedImagePreview.value && form.value.images.length) {
    selectedImagePreview.value = form.value.images[0].preview
  }

  if (selectedImagePreview.value && !form.value.images.some((img) => img.preview === selectedImagePreview.value)) {
    selectedImagePreview.value = form.value.images[0]?.preview || ''
  }
}

const onImageDragStart = (index) => {
  dragImageIndex.value = index
}

const onImageDrop = (dropIndex) => {
  if (dragImageIndex.value === null || dragImageIndex.value === dropIndex) return

  const moved = form.value.images.splice(dragImageIndex.value, 1)[0]
  form.value.images.splice(dropIndex, 0, moved)
  dragImageIndex.value = null
  ensureFirstImagePrimary()
}

const removeImage = (index) => {
  form.value.images.splice(index, 1)
  ensureFirstImagePrimary()
}

const openImagePreview = (image) => {
  if (!image) return
  previewModalImage.value = image
  showImagePreviewModal.value = true
}

const normalizedAttributeValues = (attribute) => {
  const values = Array.isArray(attribute?.values) ? attribute.values : []
  if (attribute.type === 'color') {
    return values
      .map((item) => {
        const name = String(item?.name || '').trim()
        const color = String(item?.color || '').trim()
        if (!name || !color) return null
        return { key: `${name}__${color}`, name, color, label: name }
      })
      .filter(Boolean)
  }

  return values
    .map((value) => String(value || '').trim())
    .filter(Boolean)
    .map((value) => ({ key: value, label: value }))
}

const isSelected = (slug, key) => {
  const current = selectedValues.value[slug] || []
  return current.some((item) => item.key === key)
}

const toggleValue = (slug, value, checked) => {
  const current = selectedValues.value[slug] || []
  if (checked) {
    selectedValues.value[slug] = [...current, value]
  } else {
    selectedValues.value[slug] = current.filter((item) => item.key !== value.key)
  }
}

const cartesian = (arr) => arr.reduce((a, b) => a.flatMap((d) => b.map((e) => [...d, e])), [[]])

const variantKeys = computed(() => {
  const keys = new Set()
  variants.value.forEach((variant) => {
    Object.keys(variant.attributes || {}).forEach((k) => keys.add(k))
  })
  return Array.from(keys)
})

const isColorValue = (value) => typeof value === 'object' && value !== null && 'color' in value

const generateCombinations = () => {
  const groups = attributes.value
    .map((attribute) => {
      const selected = selectedValues.value[attribute.slug] || []
      if (!selected.length) return null
      return selected.map((value) => ({ attribute, value }))
    })
    .filter(Boolean)

  if (!groups.length) {
    toast.error('Select at least one attribute value.')
    return
  }

  const combinations = cartesian(groups)
  const prefix = (form.value.product_code || 'PRD').replace(/[^A-Za-z0-9]/g, '').toUpperCase()

  variants.value = combinations.map((combo, i) => {
    const attrs = {}
    combo.forEach(({ attribute, value }) => {
      attrs[attribute.slug] = attribute.type === 'color'
        ? { name: value.name, color: value.color }
        : value.label
    })

    return {
      key: JSON.stringify(attrs),
      attributes: attrs,
      sku: `${prefix}-${i + 1}`,
      price: 0,
      stock_quantity: 0,
      reorder_level: 0,
      is_active: true,
    }
  })
}

const saveProduct = async () => {
  if (quillInstance.value) {
    form.value.long_description = quillInstance.value.root.innerHTML
  }

  if (form.value.hasVariants === 'no' && !form.value.sku) {
    toast.error('SKU is required when variants are disabled.')
    return
  }

  if (form.value.hasVariants === 'yes' && !variants.value.length) {
    toast.error('Generate at least one variant row.')
    return
  }

  const payload = {
    title: form.value.title,
    small_description: form.value.small_description,
    long_description: form.value.long_description || null,
    category_id: form.value.category_id,
    product_code: form.value.product_code,
    is_active: form.value.is_active ? 1 : 0,
    has_varients: form.value.hasVariants === 'yes' ? 1 : 0,
    delivery_fee_id: form.value.is_free_shipping
      ? null
      : (form.value.delivery_fee_id || defaultDeliveryFeeId.value || null),
    is_free_shipping: form.value.is_free_shipping ? 1 : 0,
    sku: form.value.hasVariants === 'no' ? form.value.sku : null,
    price: form.value.hasVariants === 'no' ? form.value.price : null,
    stock_quantity: form.value.hasVariants === 'no' ? form.value.stock_quantity : null,
    reorder_level: form.value.hasVariants === 'no' ? form.value.reorder_level : null,
    images: form.value.images.map((img) => ({ path: img.path, is_primary: img.is_primary ? 1 : 0 })),
    levels: levelRows.value.map((row) => ({
      level_id: row.level_id,
      type: row.type,
      value: Number(row.value || 0),
    })),
    varients: form.value.hasVariants === 'yes'
      ? variants.value.map((v) => ({
          id: v.id || null,
          sku: v.sku,
          attributes: v.attributes,
          price: v.price,
          stock_quantity: v.stock_quantity,
          reorder_level: v.reorder_level,
          is_active: v.is_active ? 1 : 0,
        }))
      : [],
  }

  try {
    saving.value = true
    if (isEditing.value && productId.value) {
      await axios.put(`/api/products/${productId.value}`, payload)
      toast.success('Product updated successfully.')
    } else {
      await axios.post('/api/products', payload)
      toast.success('Product created successfully.')
    }
    goToProducts()
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors || {}
      Object.values(errors).flat().forEach((message) => toast.error(message))
    } else {
      toast.error(error.response?.data?.message || 'Failed to save product.')
    }
  } finally {
    saving.value = false
  }
}

const loadProduct = async () => {
  if (!isEditing.value || !productId.value) return

  try {
    const { data } = await axios.get(`/api/products/${productId.value}`)

    form.value.title = data.title || ''
    form.value.product_code = data.product_code || ''
    form.value.sku = data.has_varients ? '' : (data.varients?.[0]?.sku || '')
    form.value.category_id = data.category_id ?? null
    form.value.is_free_shipping = !!data.is_free_shipping
    form.value.small_description = data.small_description || ''
    form.value.long_description = data.long_description || ''
    form.value.hasVariants = data.has_varients ? 'yes' : 'no'
    form.value.is_active = !!data.is_active
    form.value.images = (data.images || []).map((img) => ({
      preview: `/storage/${img.path}`,
      path: img.path,
      is_primary: !!img.is_primary,
    }))
    form.value.images.sort((a, b) => Number(b.is_primary) - Number(a.is_primary))
    ensureFirstImagePrimary()

    if (form.value.hasVariants === 'no') {
      const base = data.varients?.[0] || {}
      form.value.price = base.price ?? null
      form.value.stock_quantity = base.stock_quantity ?? 0
      form.value.reorder_level = base.reorder_level ?? 0
      variants.value = []
    } else {
      form.value.price = null
      form.value.stock_quantity = 0
      form.value.reorder_level = 0
      variants.value = (data.varients || []).map((variant) => ({
        id: variant.id || null,
        key: JSON.stringify(variant.attributes || {}),
        attributes: variant.attributes || {},
        sku: variant.sku || '',
        price: Number(variant.price || 0),
        stock_quantity: Number(variant.stock_quantity || 0),
        reorder_level: Number(variant.reorder_level || 0),
        is_active: variant.is_active !== false,
      }))
    }

    const productDeliveryFee = Number(data.delivery_fee || 0)
    const matchedFee = deliveryFees.value.find((fee) => Number(fee.fee || 0) === productDeliveryFee)
    form.value.delivery_fee_id = matchedFee?.id || defaultDeliveryFeeId.value || null

    const productLevels = data.product_levels || []
    if (productLevels.length) {
      levelMode.value = productLevels[0]?.type === 'amount' ? 'amount' : 'percentage'
      const mapped = new Map(productLevels.map((item) => [item.level_id, item]))
      levelRows.value = levels.value.map((level) => {
        const item = mapped.get(level.id)
        return {
          level_id: level.id,
          level_label: level.level_name || `Level ${level.level_no}`,
          type: item?.type || levelMode.value,
          value: Number(item?.value || 0),
        }
      })
    }
  } catch {
    toast.error('Failed to load product for editing.')
  }
}

onMounted(async () => {
  detectEditMode()
  await loadOptions()
  await loadProduct()
  await nextTick()
  await initQuill()
})

onBeforeUnmount(() => {
  quillInstance.value = null
})
</script>
