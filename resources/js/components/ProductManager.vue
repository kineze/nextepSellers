<template>
  <div class="p-6 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Inventory</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Product Manager</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">View product catalog and manage inventory listings.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-4 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
            <i class="fas fa-box-open"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Products</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">Browse by title, code, and category</p>
          </div>
        </div>

        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto">
          <div class="relative w-full sm:w-80">
            <input
              v-model="search"
              @input="debounceSearch"
              type="search"
              placeholder="Search products..."
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            />
            <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <i class="fas fa-search"></i>
            </div>
          </div>

          <button
            @click="openCreateDrawer"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black dark:bg-white dark:text-slate-900"
          >
            <i class="fas fa-plus"></i> New Product
          </button>
        </div>
      </div>

      <div class="overflow-x-auto p-4">
        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">Title</th>
              <th class="px-3 py-3">Code</th>
              <th class="px-3 py-3">Category</th>
              <th class="px-3 py-3">Images</th>
              <th class="px-3 py-3">Status</th>
              <th class="px-3 py-3">Variants</th>
              <th class="px-3 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
            <tr v-if="products.length === 0">
              <td colspan="7" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No products found</td>
            </tr>
            <tr v-for="product in products" :key="product.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4 font-medium text-slate-900 dark:text-white">{{ product.title }}</td>
              <td class="px-3 py-4">{{ product.product_code }}</td>
              <td class="px-3 py-4">{{ product.category?.name || '-' }}</td>
              <td class="px-3 py-4">{{ product.images_count ?? 0 }}</td>
              <td class="px-3 py-4">
                <label class="inline-flex cursor-pointer items-center">
                  <input
                    type="checkbox"
                    class="peer sr-only"
                    :checked="!!product.is_active"
                    :disabled="!!togglingStatus[product.id]"
                    @change="toggleProductStatus(product, $event.target.checked)"
                  >
                  <div class="relative h-5 w-9 rounded-full bg-slate-300 transition-all peer-focus:ring-4 peer-focus:ring-green-300 dark:bg-gray-700 dark:peer-focus:ring-green-800 peer-checked:bg-green-600 dark:peer-checked:bg-green-600 after:absolute after:start-[2px] after:top-0.5 after:h-4 after:w-4 after:rounded-full after:border after:border-slate-200 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white"></div>
                  <span class="ms-3 select-none text-xs font-medium text-slate-700 dark:text-slate-200">
                    {{ product.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </label>
              </td>
              <td class="px-3 py-4">
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide"
                  :class="product.has_varients ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
                >
                  {{ product.has_varients ? 'Yes' : 'No' }}
                </span>
              </td>
              <td class="px-3 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button
                    type="button"
                    @click="viewProduct(product)"
                    class="rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                  >
                    View
                  </button>
                  <button
                    type="button"
                    @click="editProduct(product)"
                    class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-semibold text-white hover:bg-black dark:bg-white dark:text-slate-900"
                  >
                    Edit
                  </button>
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
          <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">Prev</button>
          <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">Next</button>
        </div>
      </div>
    </div>

    <transition name="fade">
      <div
        v-if="showViewModal"
        class="fixed inset-0 z-[990] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        @click.self="showViewModal = false"
      >
        <div class="w-full max-w-6xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ viewingProduct?.title || 'Product Details' }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ viewingProduct?.product_code || '-' }}</p>
            </div>
            <button
              type="button"
              @click="showViewModal = false"
              class="rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            >
              Close
            </button>
          </div>

          <div v-if="loadingView" class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">Loading product details...</div>

          <div v-else-if="viewingProduct" class="grid gap-5 text-sm lg:grid-cols-5">
            <div class="lg:col-span-2">
              <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/60">
                <img
                  v-if="selectedPreviewImage"
                  :src="selectedPreviewImage"
                  alt="Product preview"
                  class="h-80 w-full object-cover"
                />
                <div v-else class="flex h-80 items-center justify-center text-sm text-slate-400 dark:text-slate-500">
                  No images
                </div>
              </div>

              <div v-if="(viewingProduct.images || []).length" class="mt-3 grid grid-cols-5 gap-2">
                <button
                  v-for="image in viewingProduct.images"
                  :key="image.id"
                  type="button"
                  @click="selectedPreviewImage = imageUrl(image.path)"
                  class="overflow-hidden rounded-lg border-2 transition"
                  :class="selectedPreviewImage === imageUrl(image.path) ? 'border-slate-900 dark:border-white' : 'border-slate-200 dark:border-slate-700'"
                >
                  <img :src="imageUrl(image.path)" alt="Product thumbnail" class="h-14 w-full object-cover" />
                </button>
              </div>
            </div>

            <div class="space-y-3 lg:col-span-3">
              <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                  <p class="text-xs text-slate-500 dark:text-slate-400">Category</p>
                  <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ viewingProduct.category?.name || '-' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                  <p class="text-xs text-slate-500 dark:text-slate-400">Status</p>
                  <p class="mt-1 font-semibold" :class="viewingProduct.is_active ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400'">
                    {{ viewingProduct.is_active ? 'Active' : 'Inactive' }}
                  </p>
                </div>
                <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                  <p class="text-xs text-slate-500 dark:text-slate-400">Variants</p>
                  <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ viewingProduct.has_varients ? 'Yes' : 'No' }}</p>
                </div>
              </div>

              <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                <p class="text-xs text-slate-500 dark:text-slate-400">Small Description</p>
                <p class="mt-1 text-slate-800 dark:text-slate-100">{{ viewingProduct.small_description || '-' }}</p>
              </div>

              <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                <p class="text-xs text-slate-500 dark:text-slate-400">Long Description</p>
                <div class="prose prose-sm mt-2 max-w-none dark:prose-invert" v-html="viewingProduct.long_description || '<p>-</p>'"></div>
              </div>

              <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                <div class="mb-2 flex items-center justify-between">
                  <p class="text-xs text-slate-500 dark:text-slate-400">Variants & Pricing</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ (viewingProduct.varients || []).length }} row(s)</p>
                </div>
                <div class="max-h-52 overflow-auto rounded-lg border border-slate-200 dark:border-slate-700">
                  <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                      <tr>
                        <th class="px-2 py-2 text-left">SKU</th>
                        <th class="px-2 py-2 text-left">Attributes</th>
                        <th class="px-2 py-2 text-left">Price</th>
                        <th class="px-2 py-2 text-left">Stock</th>
                        <th class="px-2 py-2 text-left">Reorder</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="variant in (viewingProduct.varients || [])"
                        :key="variant.id"
                        class="border-t border-slate-200 dark:border-slate-700"
                      >
                        <td class="px-2 py-2 font-medium text-slate-900 dark:text-slate-100">{{ variant.sku || '-' }}</td>
                        <td class="px-2 py-2">
                          <span v-if="variant.attributes && Object.keys(variant.attributes).length">
                            {{ formatVariantAttributes(variant.attributes) }}
                          </span>
                          <span v-else>-</span>
                        </td>
                        <td class="px-2 py-2">{{ variant.price ?? '-' }}</td>
                        <td class="px-2 py-2">{{ variant.stock_quantity ?? 0 }}</td>
                        <td class="px-2 py-2">{{ variant.reorder_level ?? 0 }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                <div class="mb-2 flex items-center justify-between">
                  <p class="text-xs text-slate-500 dark:text-slate-400">Level Design</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ (viewingProduct.product_levels || []).length }} level(s)</p>
                </div>
                <div v-if="!(viewingProduct.product_levels || []).length" class="text-xs text-slate-500 dark:text-slate-400">
                  No level commission data.
                </div>
                <div v-else class="grid gap-2 sm:grid-cols-2">
                  <div
                    v-for="row in viewingProduct.product_levels"
                    :key="row.id"
                    class="rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700"
                  >
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                      {{ row.level?.level_name || `Level ${row.level?.level_no || ''}` }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                      {{ row.type === 'percentage' ? `${row.value}%` : `LKR ${row.value}` }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const products = ref([])
const search = ref('')
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 })
const showViewModal = ref(false)
const viewingProduct = ref(null)
const loadingView = ref(false)
const selectedPreviewImage = ref('')
const togglingStatus = ref({})
let searchTimeout = null

const fetchProducts = async (page = 1) => {
  try {
    const res = await axios.get('/api/products', {
      params: { search: search.value, page, per_page: pagination.value.per_page },
    })
    products.value = res.data.products || []
    pagination.value = res.data.pagination || pagination.value
  } catch {
    toast.error('Failed to load products')
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchProducts(1), 400)
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchProducts(page)
}

const openCreateDrawer = () => {
  window.location.href = '/products/create'
}

const editProduct = (product) => {
  window.location.href = `/products/${product.id}/edit`
}

const imageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return `/storage/${path}`
}

const formatVariantAttributes = (attributes) => {
  return Object.entries(attributes)
    .map(([key, value]) => {
      if (value && typeof value === 'object') {
        return `${key}: ${value.name || value.color || '-'}`
      }
      return `${key}: ${value}`
    })
    .join(' | ')
}

const viewProduct = async (product) => {
  showViewModal.value = true
  loadingView.value = true
  viewingProduct.value = null
  selectedPreviewImage.value = ''

  try {
    const res = await axios.get(`/api/products/${product.id}`)
    viewingProduct.value = res.data
    const images = viewingProduct.value?.images || []
    const primary = images.find((img) => !!img.is_primary)
    selectedPreviewImage.value = imageUrl((primary || images[0] || {}).path || '')
  } catch {
    showViewModal.value = false
    toast.error('Failed to load product details')
  } finally {
    loadingView.value = false
  }
}

const toggleProductStatus = async (product, checked) => {
  const previous = !!product.is_active
  product.is_active = !!checked
  togglingStatus.value = { ...togglingStatus.value, [product.id]: true }

  try {
    await axios.post(`/api/products/${product.id}/toggle-active`, {
      is_active: checked ? 1 : 0,
    })
  } catch (error) {
    product.is_active = previous
    toast.error(error.response?.data?.message || 'Failed to update product status')
  } finally {
    const next = { ...togglingStatus.value }
    delete next[product.id]
    togglingStatus.value = next
  }
}

onMounted(() => {
  fetchProducts(1)
})
</script>
