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

          <div class="inline-flex self-start rounded-xl border border-slate-200 bg-slate-100 p-1 dark:border-slate-700 dark:bg-slate-800" aria-label="Product view options">
            <button
              type="button"
              class="inline-flex h-8 items-center gap-2 rounded-lg px-3 text-xs font-semibold transition"
              :class="viewMode === 'list' ? 'bg-white text-slate-950 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
              :aria-pressed="viewMode === 'list'"
              @click="setViewMode('list')"
            >
              <i class="fas fa-list"></i>
              <span class="hidden md:inline">List</span>
            </button>
            <button
              type="button"
              class="inline-flex h-8 items-center gap-2 rounded-lg px-3 text-xs font-semibold transition"
              :class="viewMode === 'card' ? 'bg-white text-slate-950 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
              :aria-pressed="viewMode === 'card'"
              @click="setViewMode('card')"
            >
              <i class="fas fa-grip"></i>
              <span class="hidden md:inline">Cards</span>
            </button>
          </div>

          <button
            @click="openCreateDrawer"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black dark:bg-white dark:text-slate-900"
          >
            <i class="fas fa-plus"></i> New Product
          </button>
        </div>
      </div>

      <div v-if="viewMode === 'list'" class="overflow-x-auto p-4">
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

      <div v-else class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <div v-if="products.length === 0" class="col-span-full py-10 text-center text-sm text-slate-500 dark:text-slate-400">
          No products found
        </div>

        <article
          v-for="product in products"
          :key="product.id"
          class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-900"
        >
          <button type="button" class="relative block aspect-[4/3] w-full overflow-hidden bg-slate-100 text-left dark:bg-slate-800" @click="viewProduct(product)">
            <img
              v-if="productImage(product)"
              :src="productImage(product)"
              :alt="product.title"
              class="h-full w-full object-contain transition duration-500 group-hover:scale-105"
            >
            <span v-else class="flex h-full items-center justify-center text-xs text-slate-400 dark:text-slate-500">No image</span>
            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-slate-700 shadow-sm dark:bg-slate-950/90 dark:text-slate-200">
              {{ product.category?.name || 'Uncategorized' }}
            </span>
            <span v-if="product.isbestseller" class="absolute right-3 top-3 rounded-full bg-amber-400 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-950 shadow-sm">
              <i class="fas fa-fire mr-1"></i> Best seller
            </span>
          </button>

          <div class="flex flex-1 flex-col p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h3 class="line-clamp-2 text-sm font-bold leading-5 text-slate-950 dark:text-white">{{ product.title }}</h3>
                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ product.product_code }}</p>
                <div class="mt-2 flex min-w-0 items-center gap-1.5 text-[10px]">
                  <span class="flex shrink-0 gap-px text-amber-400" :aria-label="`${product.rating} out of 5 stars`">
                    <i v-for="star in 5" :key="star" :class="starIcon(product.rating, star)"></i>
                  </span>
                  <span class="truncate font-bold text-slate-600 dark:text-slate-300">{{ ratingLabel(product) }}</span>
                </div>
              </div>
              <span
                class="shrink-0 rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-wide"
                :class="product.has_varients ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'"
              >
                {{ product.has_varients ? 'Variants' : 'Single' }}
              </span>
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
              <label class="inline-flex cursor-pointer items-center">
                <input
                  type="checkbox"
                  class="peer sr-only"
                  :checked="!!product.is_active"
                  :disabled="!!togglingStatus[product.id]"
                  @change="toggleProductStatus(product, $event.target.checked)"
                >
                <span class="relative h-5 w-9 rounded-full bg-slate-300 transition-all peer-checked:bg-emerald-600 peer-disabled:opacity-50 dark:bg-slate-700 after:absolute after:start-[2px] after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full"></span>
                <span class="ml-2 text-xs font-semibold" :class="product.is_active ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400'">
                  {{ product.is_active ? 'Active' : 'Inactive' }}
                </span>
              </label>
              <span class="text-xs text-slate-500 dark:text-slate-400">{{ product.images_count ?? 0 }} images</span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2">
              <button type="button" @click="viewProduct(product)" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                View
              </button>
              <button type="button" @click="editProduct(product)" class="rounded-xl bg-slate-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-black dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">
                Edit
              </button>
            </div>
          </div>
        </article>
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
        class="fixed inset-0 z-[990] flex justify-end bg-black/50 backdrop-blur-sm"
        @click.self="showViewModal = false"
      >
        <aside class="flex h-full w-full max-w-6xl flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ viewingProduct?.title || 'Product Details' }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ viewingProduct?.product_code || '-' }}</p>
            </div>
            <button
              type="button"
              @click="showViewModal = false"
              class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
              aria-label="Close product details"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto px-5 py-5">
            <div v-if="loadingView" class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">Loading product details...</div>

            <div v-else-if="viewingProduct" class="grid gap-5 text-sm lg:grid-cols-5">
              <div class="lg:col-span-2">
                <div class="aspect-square overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/60">
                  <img
                    v-if="selectedPreviewImage"
                    :src="selectedPreviewImage"
                    alt="Product preview"
                    class="h-full w-full object-contain"
                  />
                  <div v-else class="flex h-full items-center justify-center text-sm text-slate-400 dark:text-slate-500">
                    No images
                  </div>
                </div>

                <div v-if="(viewingProduct.images || []).length" class="mt-3 grid grid-cols-5 gap-2">
                  <button
                    v-for="image in viewingProduct.images"
                    :key="image.id"
                    type="button"
                    @click="selectedPreviewImage = imageUrl(image.path)"
                    class="aspect-square overflow-hidden rounded-lg border-2 bg-slate-50 transition dark:bg-slate-800/60"
                    :class="selectedPreviewImage === imageUrl(image.path) ? 'border-slate-900 dark:border-white' : 'border-slate-200 dark:border-slate-700'"
                  >
                    <img :src="imageUrl(image.path)" alt="Product thumbnail" class="h-full w-full object-contain" />
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
                  <p class="text-xs text-slate-500 dark:text-slate-400">Product Video</p>
                  <a
                    v-if="viewingProduct.product_video"
                    :href="videoUrl(viewingProduct.product_video)"
                    target="_blank"
                    rel="noopener"
                    class="mt-1 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200"
                  >
                    Open video
                  </a>
                  <p v-else class="mt-1 text-slate-800 dark:text-slate-100">-</p>
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
                      <p class="mt-1 text-xs font-semibold text-violet-700 dark:text-violet-300">
                        Affiliate: {{ row.affiliate_commission_type === 'amount' ? `LKR ${row.affiliate_commission}` : `${row.affiliate_commission}%` }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const viewStorageKey = 'admin.products.view-mode'

const initialViewMode = () => {
  try {
    const savedView = window.localStorage.getItem(viewStorageKey)
    return ['list', 'card'].includes(savedView) ? savedView : 'list'
  } catch {
    return 'list'
  }
}

const products = ref([])
const search = ref('')
const viewMode = ref(initialViewMode())
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 })
const showViewModal = ref(false)
const viewingProduct = ref(null)
const loadingView = ref(false)
const selectedPreviewImage = ref('')
const togglingStatus = ref({})
let searchTimeout = null

const setViewMode = (mode) => {
  if (!['list', 'card'].includes(mode)) return
  viewMode.value = mode
  try {
    window.localStorage.setItem(viewStorageKey, mode)
  } catch {
    // The selected view still works when browser storage is unavailable.
  }
}

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

const productImage = (product) => {
  const images = Array.isArray(product?.images) ? product.images : []
  const primary = images.find((image) => !!image.is_primary)
  return imageUrl((primary || images[0] || {}).path || '')
}

const ratingLabel = (product) => `${Number(product.rating || 0).toFixed(1)} (${Number(product.rating_user_count || 0).toLocaleString('en-LK')})`

const starIcon = (rating, star) => {
  const value = Number(rating || 0)
  if (value >= star) return 'fas fa-star'
  if (value >= star - 0.5) return 'fas fa-star-half-alt'
  return 'far fa-star'
}

const videoUrl = (path) => {
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
