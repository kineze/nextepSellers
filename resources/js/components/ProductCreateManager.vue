<template>
  <div class="p-6 w-full">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
      <div>
        <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Inventory</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ isEditing ? 'Edit Product' : 'New Product' }}</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Create products with rich description, images, and smart variant combinations.</p>
      </div>
      <button
        @click="goToProducts"
        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
      >
        Back to Products
      </button>
    </div>

    <form @submit.prevent="saveProduct" class="space-y-5">
      <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="grid gap-4 lg:grid-cols-2">
          <div class="grid gap-4">
            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Product Title</label>
                <input v-model="form.title" required type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
              </div>
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Product Code</label>
                <input v-model="form.product_code" required type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
              </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Category</label>
                <select v-model="form.category_id" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                  <option :value="null">Select category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">SKU (for no variants)</label>
                <input v-model="form.sku" :disabled="form.hasVariants === 'yes'" type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition disabled:opacity-50 focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
              </div>
            </div>

            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Small Description</label>
              <input v-model="form.small_description" required type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
            </div>

            <div class="rounded-xl border border-slate-200/70 bg-slate-50/70 p-3 dark:border-slate-700 dark:bg-slate-800/50">
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

              <div v-if="form.images.length" class="mt-3 grid grid-cols-4 gap-3">
                <div v-for="(image, index) in form.images" :key="image.preview" class="relative rounded-lg border border-slate-200 p-1 dark:border-slate-700">
                  <img
                    :src="image.preview"
                    class="h-20 w-full rounded-md object-cover"
                    :class="image.is_primary ? 'ring-2 ring-emerald-500' : ''"
                    @click="setPrimary(index)"
                  />
                  <button type="button" @click="removeImage(index)" class="absolute right-1 top-1 rounded bg-black/60 px-1 text-xs text-white">✖</button>
                  <span v-if="image.is_primary" class="absolute bottom-1 left-1 rounded bg-emerald-600/90 px-1 text-[10px] text-white">Primary</span>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Long Description</label>
            <div class="mb-2 flex flex-wrap gap-2">
              <button type="button" @click="execEditor('bold')" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold dark:border-slate-700">Bold</button>
              <button type="button" @click="execEditor('italic')" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold dark:border-slate-700">Italic</button>
              <button type="button" @click="execEditor('insertUnorderedList')" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold dark:border-slate-700">Bullets</button>
              <button type="button" @click="execEditor('insertOrderedList')" class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold dark:border-slate-700">Numbered</button>
              <button type="button" @click="clearDescription" class="rounded-lg border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600 dark:border-rose-700">Clear</button>
            </div>
            <div
              ref="editorRef"
              contenteditable="true"
              @input="syncDescription"
              class="min-h-[330px] w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            ></div>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="mb-4">
          <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Has Variants?</label>
          <div class="flex gap-6 text-sm text-slate-700 dark:text-slate-200">
            <label class="inline-flex items-center gap-2"><input type="radio" value="yes" v-model="form.hasVariants" /> Yes</label>
            <label class="inline-flex items-center gap-2"><input type="radio" value="no" v-model="form.hasVariants" /> No</label>
          </div>
        </div>

        <div v-if="form.hasVariants === 'no'" class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div><label class="mb-1 block text-xs font-semibold">Price</label><input type="number" min="0" step="0.01" v-model.number="form.price" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" /></div>
          <div><label class="mb-1 block text-xs font-semibold">Min Price</label><input type="number" min="0" step="0.01" v-model.number="form.min_price" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" /></div>
          <div><label class="mb-1 block text-xs font-semibold">Max Price</label><input type="number" min="0" step="0.01" v-model.number="form.max_price" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" /></div>
          <div><label class="mb-1 block text-xs font-semibold">Cost</label><input type="number" min="0" step="0.01" v-model.number="form.cost" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" /></div>
          <div><label class="mb-1 block text-xs font-semibold">Stock Quantity</label><input type="number" min="0" v-model.number="form.stock_quantity" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" /></div>
          <div><label class="mb-1 block text-xs font-semibold">Reorder Level</label><input type="number" min="0" v-model.number="form.reorder_level" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" /></div>
        </div>

        <div v-else class="space-y-4">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div v-for="attribute in attributes" :key="attribute.id" class="rounded-xl border border-slate-200/70 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/50">
              <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ attribute.name }}</p>
              <p class="mb-2 text-xs text-slate-500">{{ attribute.type }}</p>
              <div class="max-h-40 space-y-2 overflow-y-auto pr-1">
                <label
                  v-for="val in normalizedAttributeValues(attribute)"
                  :key="`${attribute.id}-${val.key}`"
                  class="flex cursor-pointer items-center gap-2 text-xs"
                >
                  <input type="checkbox" :checked="isSelected(attribute.slug, val.key)" @change="toggleValue(attribute.slug, val, $event.target.checked)" />
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
                  <th class="px-2 py-2 text-left">Min</th>
                  <th class="px-2 py-2 text-left">Max</th>
                  <th class="px-2 py-2 text-left">Cost</th>
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
                  <td class="px-2 py-2"><input type="text" v-model="variant.sku" class="w-28 rounded-lg border border-slate-200 px-2 py-1 dark:border-slate-700 dark:bg-slate-800" /></td>
                  <td class="px-2 py-2"><input type="number" min="0" step="0.01" v-model.number="variant.price" class="w-20 rounded-lg border border-slate-200 px-2 py-1 dark:border-slate-700 dark:bg-slate-800" /></td>
                  <td class="px-2 py-2"><input type="number" min="0" step="0.01" v-model.number="variant.min_price" class="w-20 rounded-lg border border-slate-200 px-2 py-1 dark:border-slate-700 dark:bg-slate-800" /></td>
                  <td class="px-2 py-2"><input type="number" min="0" step="0.01" v-model.number="variant.max_price" class="w-20 rounded-lg border border-slate-200 px-2 py-1 dark:border-slate-700 dark:bg-slate-800" /></td>
                  <td class="px-2 py-2"><input type="number" min="0" step="0.01" v-model.number="variant.cost" class="w-20 rounded-lg border border-slate-200 px-2 py-1 dark:border-slate-700 dark:bg-slate-800" /></td>
                  <td class="px-2 py-2"><input type="number" min="0" v-model.number="variant.stock_quantity" class="w-20 rounded-lg border border-slate-200 px-2 py-1 dark:border-slate-700 dark:bg-slate-800" /></td>
                  <td class="px-2 py-2"><input type="number" min="0" v-model.number="variant.reorder_level" class="w-20 rounded-lg border border-slate-200 px-2 py-1 dark:border-slate-700 dark:bg-slate-800" /></td>
                  <td class="px-2 py-2"><button type="button" @click="variants.splice(i, 1)" class="rounded bg-rose-600 px-2 py-1 text-xs text-white">Remove</button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
          <input type="checkbox" v-model="form.is_active" class="rounded border-slate-300 dark:border-slate-700" />
          Active Product
        </label>
        <button type="submit" :disabled="saving" class="rounded-xl bg-slate-900 px-8 py-2.5 text-xs font-bold uppercase tracking-wide text-white hover:bg-black disabled:opacity-50 dark:bg-white dark:text-slate-900">
          {{ saving ? 'Saving...' : 'Save Product' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const isEditing = false

const categories = ref([])
const attributes = ref([])
const imageInputRef = ref(null)
const editorRef = ref(null)
const saving = ref(false)

const selectedValues = ref({})
const variants = ref([])

const form = ref({
  title: '',
  product_code: '',
  sku: '',
  category_id: null,
  small_description: '',
  long_description: '',
  hasVariants: 'no',
  is_active: true,
  images: [],
  price: null,
  min_price: null,
  max_price: null,
  cost: null,
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
  } catch {
    toast.error('Failed to load categories and attributes.')
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
    is_primary: form.value.images.length === 0,
  })
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

const setPrimary = (index) => {
  form.value.images.forEach((image, i) => {
    image.is_primary = i === index
  })
}

const removeImage = (index) => {
  form.value.images.splice(index, 1)
  if (form.value.images.length && !form.value.images.some((image) => image.is_primary)) {
    form.value.images[0].is_primary = true
  }
}

const execEditor = (command) => {
  editorRef.value?.focus()
  document.execCommand(command, false)
  syncDescription()
}

const syncDescription = () => {
  form.value.long_description = editorRef.value?.innerHTML || ''
}

const clearDescription = () => {
  if (editorRef.value) editorRef.value.innerHTML = ''
  form.value.long_description = ''
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
      min_price: null,
      max_price: null,
      cost: null,
      stock_quantity: 0,
      reorder_level: 0,
      is_active: true,
    }
  })
}

const saveProduct = async () => {
  syncDescription()

  if (form.value.hasVariants === 'no') {
    if (!form.value.sku) {
      toast.error('SKU is required when variants are disabled.')
      return
    }
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
    sku: form.value.hasVariants === 'no' ? form.value.sku : null,
    price: form.value.hasVariants === 'no' ? form.value.price : null,
    min_price: form.value.hasVariants === 'no' ? form.value.min_price : null,
    max_price: form.value.hasVariants === 'no' ? form.value.max_price : null,
    cost: form.value.hasVariants === 'no' ? form.value.cost : null,
    stock_quantity: form.value.hasVariants === 'no' ? form.value.stock_quantity : null,
    reorder_level: form.value.hasVariants === 'no' ? form.value.reorder_level : null,
    images: form.value.images.map((img) => ({ path: img.path, is_primary: img.is_primary ? 1 : 0 })),
    varients: form.value.hasVariants === 'yes'
      ? variants.value.map((v) => ({
          sku: v.sku,
          attributes: v.attributes,
          price: v.price,
          min_price: v.min_price,
          max_price: v.max_price,
          cost: v.cost,
          stock_quantity: v.stock_quantity,
          reorder_level: v.reorder_level,
          is_active: v.is_active ? 1 : 0,
        }))
      : [],
  }

  try {
    saving.value = true
    await axios.post('/api/products', payload)
    toast.success('Product created successfully.')
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

onMounted(async () => {
  await loadOptions()
  await nextTick()
})
</script>
