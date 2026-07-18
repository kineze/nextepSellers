<template>
  <div class="space-y-8">
    <header class="overflow-hidden rounded-3xl bg-slate-950 px-5 py-6 text-white shadow-xl shadow-slate-950/10 sm:px-7">
      <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.24em] text-amber-300">Product marketplace</p>
          <h1 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">Find your next winning product</h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Browse proven bestsellers, fresh arrivals, and the complete active catalog.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <a
            :href="categoryUrl(null)"
            class="rounded-full border px-4 py-2 text-xs font-bold uppercase tracking-wide transition"
            :class="!selectedCategoryId ? 'border-white bg-white text-slate-950' : 'border-slate-700 text-slate-200 hover:border-slate-500'"
          >
            All
          </a>
          <a
            v-for="category in categories"
            :key="category.id"
            :href="categoryUrl(category.id)"
            class="rounded-full border px-4 py-2 text-xs font-bold uppercase tracking-wide transition"
            :class="Number(selectedCategoryId) === Number(category.id) ? 'border-white bg-white text-slate-950' : 'border-slate-700 text-slate-200 hover:border-slate-500'"
          >
            {{ category.name }}
          </a>
        </div>
      </div>
    </header>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <button
        type="button"
        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
        :aria-expanded="showAdvancedFilters"
        @click="showAdvancedFilters = !showAdvancedFilters"
      >
        <span class="flex items-center gap-3">
          <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-950 text-white dark:bg-white dark:text-slate-950">
            <i class="fas fa-sliders"></i>
          </span>
          <span>
            <span class="block text-sm font-black text-slate-950 dark:text-white">Advanced filters</span>
            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Category, product collection, date order, and available attributes</span>
          </span>
        </span>
        <span class="flex items-center gap-3">
          <span v-if="activeFilterCount" class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
            {{ activeFilterCount }} active
          </span>
          <i class="fas fa-chevron-down text-xs text-slate-400 transition" :class="showAdvancedFilters ? 'rotate-180' : ''"></i>
        </span>
      </button>

      <div v-show="showAdvancedFilters" class="border-t border-slate-200 px-5 py-5 dark:border-slate-800">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div>
            <label for="catalog-category" class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Category</label>
            <select id="catalog-category" v-model="filterForm.category_id" class="filter-select">
              <option value="">All categories</option>
              <option v-for="category in categories" :key="category.id" :value="String(category.id)">{{ category.name }}</option>
            </select>
          </div>

          <div>
            <label for="catalog-sort" class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Date order</label>
            <select id="catalog-sort" v-model="filterForm.sort" class="filter-select">
              <option value="latest">Latest to oldest</option>
              <option value="oldest">Oldest to latest</option>
            </select>
          </div>

          <div>
            <label for="catalog-collection" class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Collection</label>
            <select id="catalog-collection" v-model="filterForm.collection" class="filter-select">
              <option value="all">All products</option>
              <option value="best_selling">Best selling</option>
              <option value="new_arrivals">New arrivals (30 days)</option>
            </select>
          </div>

          <div v-for="attribute in filterAttributes" :key="attribute.slug">
            <label :for="`catalog-attribute-${attribute.slug}`" class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ attribute.name }}</label>
            <select :id="`catalog-attribute-${attribute.slug}`" v-model="filterForm.attributes[attribute.slug]" class="filter-select">
              <option value="">Any {{ attribute.name.toLowerCase() }}</option>
              <option v-for="option in attribute.options" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
          </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-end gap-2">
          <button type="button" class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" @click="clearFilters">
            Clear filters
          </button>
          <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-slate-950 px-5 py-2.5 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-black dark:bg-white dark:text-slate-950" @click="applyFilters">
            <i class="fas fa-filter"></i> Apply filters
          </button>
        </div>
      </div>
    </section>

    <section aria-labelledby="best-sellers-heading">
      <div class="mb-4 flex items-end justify-between gap-4">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-600 dark:text-amber-400">Trending now</p>
          <h2 id="best-sellers-heading" class="mt-1 text-xl font-black text-slate-950 dark:text-white">Best sellers</h2>
        </div>
        <div v-if="bestSellers.length > 1" class="flex gap-2">
          <button type="button" class="carousel-button" aria-label="Previous best sellers" @click="moveCarousel(-1)">
            <i class="fas fa-arrow-left"></i>
          </button>
          <button type="button" class="carousel-button" aria-label="Next best sellers" @click="moveCarousel(1)">
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>

      <div v-if="bestSellers.length" class="relative">
        <div
          ref="carouselTrack"
          class="catalog-scrollbar flex snap-x snap-mandatory gap-4 overflow-x-auto pb-3"
          @mouseenter="pauseCarousel"
          @mouseleave="startCarousel"
          @focusin="pauseCarousel"
          @focusout="startCarousel"
        >
          <a
            v-for="product in bestSellers"
            :key="`best-${product.id}`"
            :href="product.detail_url"
            class="group relative min-w-[82%] snap-start overflow-hidden rounded-3xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-white to-orange-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-amber-500/20 dark:from-amber-500/10 dark:via-slate-900 dark:to-orange-500/10 sm:min-w-[420px]"
          >
            <div class="flex min-h-48 gap-4">
              <div class="relative w-2/5 shrink-0 overflow-hidden rounded-2xl bg-white dark:bg-slate-800">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.title" class="h-full w-full object-contain transition duration-500 group-hover:scale-105" />
                <div v-else class="flex h-full items-center justify-center text-xs text-slate-400">No image</div>
                <span class="absolute left-2 top-2 rounded-full bg-amber-400 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-amber-950">Best seller</span>
              </div>
              <div class="flex min-w-0 flex-1 flex-col py-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">{{ product.category }}</span>
                <h3 class="mt-2 line-clamp-2 text-lg font-black leading-6 text-slate-950 dark:text-white">{{ product.title }}</h3>
                <p class="mt-2 line-clamp-2 text-xs leading-5 text-slate-600 dark:text-slate-300">{{ product.small_description }}</p>
                <div class="mt-2 flex items-center gap-1.5 text-xs">
                  <span class="flex gap-0.5 text-amber-400" :aria-label="`${product.rating} out of 5 stars`">
                    <i v-for="star in 5" :key="star" :class="starIcon(product.rating, star)"></i>
                  </span>
                  <span class="font-bold text-slate-700 dark:text-slate-200">{{ ratingLabel(product) }}</span>
                </div>
                <div class="mt-auto pt-3">
                  <p class="text-sm font-black text-slate-950 dark:text-white">{{ priceLabel(product) }}</p>
                  <p class="mt-1 text-xs font-bold text-emerald-700 dark:text-emerald-400">{{ commissionLabel(product) }}</p>
                </div>
              </div>
            </div>
          </a>
        </div>
      </div>
      <div v-else class="rounded-3xl border border-dashed border-slate-300 bg-white/70 px-6 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
        No best sellers have been selected for this category yet.
      </div>
    </section>

    <div class="grid items-start gap-7 xl:grid-cols-[minmax(0,1fr)_320px]">
      <main aria-labelledby="all-products-heading" class="min-w-0">
        <div class="mb-4 flex items-end justify-between gap-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Full collection</p>
            <h2 id="all-products-heading" class="mt-1 text-xl font-black text-slate-950 dark:text-white">All products</h2>
          </div>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ total }} products</p>
        </div>

        <div v-if="products.length" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
          <article
            v-for="product in products"
            :key="product.id"
            class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
          >
            <a :href="product.detail_url" class="relative block aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-800">
              <img v-if="product.image_url" :src="product.image_url" :alt="product.title" loading="lazy" class="h-full w-full object-contain transition duration-500 group-hover:scale-105" />
              <div v-else class="flex h-full items-center justify-center text-[10px] text-slate-400">No image</div>
              <span class="absolute left-2 top-2 max-w-[70%] truncate rounded-full bg-white/90 px-2 py-1 text-[9px] font-bold uppercase tracking-wide text-slate-700 shadow-sm dark:bg-slate-950/90 dark:text-slate-200">{{ product.category }}</span>
              <span v-if="product.isbestseller" class="absolute right-2 top-2 rounded-full bg-amber-400 p-1.5 text-amber-950 shadow-sm" title="Best seller">
                <i class="fas fa-fire text-[10px]"></i>
              </span>
            </a>
            <div class="flex flex-1 flex-col p-3">
              <h3 class="line-clamp-2 min-h-8 text-xs font-black leading-4 text-slate-950 dark:text-white">{{ product.title }}</h3>
              <p class="mt-1.5 line-clamp-2 min-h-8 text-[10px] leading-4 text-slate-500 dark:text-slate-400">{{ product.small_description }}</p>
              <div class="mt-1.5 flex min-w-0 items-center gap-1 text-[9px]">
                <span class="flex shrink-0 gap-px text-amber-400" :aria-label="`${product.rating} out of 5 stars`">
                  <i v-for="star in 5" :key="star" :class="starIcon(product.rating, star)"></i>
                </span>
                <span class="truncate font-bold text-slate-600 dark:text-slate-300">{{ ratingLabel(product) }}</span>
              </div>
              <div class="mt-3 border-t border-slate-100 pt-2.5 dark:border-slate-800">
                <p class="truncate text-xs font-black text-slate-950 dark:text-white" :title="priceLabel(product)">{{ priceLabel(product) }}</p>
                <p class="mt-1 truncate text-[10px] font-bold text-emerald-700 dark:text-emerald-400" :title="commissionLabel(product)">{{ commissionLabel(product) }}</p>
              </div>
              <a :href="product.detail_url" class="mt-3 inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-950 px-2 py-2 text-[10px] font-bold uppercase tracking-wide text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">
                View product <i class="fas fa-arrow-right text-[9px]"></i>
              </a>
            </div>
          </article>
        </div>
        <div v-else class="rounded-3xl border border-dashed border-slate-300 bg-white/70 p-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
          No active products found in this category.
        </div>

        <div ref="loadTrigger" class="flex min-h-24 items-center justify-center" aria-live="polite">
          <div v-if="loadingMore" class="flex items-center gap-3 text-sm font-semibold text-slate-500 dark:text-slate-400">
            <i class="fas fa-circle-notch fa-spin"></i> Loading more products...
          </div>
          <p v-else-if="loadError" class="text-center text-sm text-rose-600 dark:text-rose-400">
            {{ loadError }}
            <button type="button" class="ml-2 font-bold underline" @click="loadMore">Try again</button>
          </p>
          <p v-else-if="products.length && !nextPageUrl" class="text-xs font-bold uppercase tracking-wider text-slate-400">You’ve reached the end</p>
        </div>
      </main>

      <aside aria-labelledby="new-arrivals-heading" class="xl:sticky xl:top-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-400">Just added</p>
              <h2 id="new-arrivals-heading" class="mt-1 text-lg font-black text-slate-950 dark:text-white">New arrivals</h2>
            </div>
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
              <i class="fas fa-sparkles"></i>
            </span>
          </div>

          <div v-if="newArrivals.length" class="space-y-3">
            <a
              v-for="product in newArrivals"
              :key="`new-${product.id}`"
              :href="product.detail_url"
              class="group flex gap-3 rounded-2xl border border-slate-100 p-2.5 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-slate-800 dark:hover:border-indigo-500/30 dark:hover:bg-indigo-500/5"
            >
              <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.title" loading="lazy" class="h-full w-full object-contain transition duration-300 group-hover:scale-105" />
                <div v-else class="flex h-full items-center justify-center text-[10px] text-slate-400">No image</div>
              </div>
              <div class="min-w-0 py-1">
                <p class="truncate text-[10px] font-bold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">{{ product.category }}</p>
                <h3 class="mt-1 line-clamp-2 text-xs font-black leading-4 text-slate-950 dark:text-white">{{ product.title }}</h3>
                <div class="mt-1 flex items-center gap-1 text-[9px]">
                  <span class="flex shrink-0 gap-px text-amber-400" :aria-label="`${product.rating} out of 5 stars`">
                    <i v-for="star in 5" :key="star" :class="starIcon(product.rating, star)"></i>
                  </span>
                  <span class="truncate font-bold text-slate-500 dark:text-slate-400">{{ ratingLabel(product) }}</span>
                </div>
                <p class="mt-2 truncate text-xs font-bold text-slate-700 dark:text-slate-200">{{ priceLabel(product) }}</p>
              </div>
            </a>
          </div>
          <p v-else class="py-8 text-center text-xs text-slate-500 dark:text-slate-400">No new arrivals found.</p>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref } from 'vue'

const props = defineProps({
  initialCatalog: { type: Object, required: true },
  productsUrl: { type: String, required: true },
})

const products = ref(Array.isArray(props.initialCatalog.products) ? props.initialCatalog.products : [])
const bestSellers = ref(Array.isArray(props.initialCatalog.best_sellers) ? props.initialCatalog.best_sellers : [])
const newArrivals = ref(Array.isArray(props.initialCatalog.new_arrivals) ? props.initialCatalog.new_arrivals : [])
const categories = Array.isArray(props.initialCatalog.categories) ? props.initialCatalog.categories : []
const filterAttributes = Array.isArray(props.initialCatalog.filter_attributes) ? props.initialCatalog.filter_attributes : []
const initialFilters = props.initialCatalog.filters || {}
const selectedCategoryId = props.initialCatalog.selected_category_id
const filterForm = reactive({
  category_id: initialFilters.category_id ? String(initialFilters.category_id) : '',
  sort: initialFilters.sort || 'latest',
  collection: initialFilters.collection || 'all',
  attributes: Object.fromEntries(filterAttributes.map((attribute) => [
    attribute.slug,
    initialFilters.attributes?.[attribute.slug] || '',
  ])),
})
const showAdvancedFilters = ref(false)
const total = ref(Number(props.initialCatalog.pagination?.total || products.value.length))
const nextPageUrl = ref(props.initialCatalog.pagination?.next_page_url || null)
const loadingMore = ref(false)
const loadError = ref('')
const carouselTrack = ref(null)
const loadTrigger = ref(null)
let carouselTimer = null
let observer = null

const activeFilterCount = computed(() => {
  return Number(!!filterForm.category_id)
    + Number(filterForm.sort !== 'latest')
    + Number(filterForm.collection !== 'all')
    + Object.values(filterForm.attributes).filter(Boolean).length
})

showAdvancedFilters.value = activeFilterCount.value > 0

const money = (value) => Number(value || 0).toLocaleString('en-LK', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
})

const priceLabel = (product) => {
  if (product.min_price === null || product.min_price === undefined) return 'Price unavailable'
  if (Number(product.min_price) === Number(product.max_price)) return `LKR ${money(product.min_price)}`
  return `LKR ${money(product.min_price)} – ${money(product.max_price)}`
}

const commissionLabel = (product) => product.commission === null || product.commission === undefined
  ? 'Commission not configured'
  : `Earn LKR ${money(product.commission)}`

const ratingLabel = (product) => `${Number(product.rating || 0).toFixed(1)} (${Number(product.rating_user_count || 0).toLocaleString('en-LK')})`

const starIcon = (rating, star) => {
  const value = Number(rating || 0)
  if (value >= star) return 'fas fa-star'
  if (value >= star - 0.5) return 'fas fa-star-half-alt'
  return 'far fa-star'
}

const catalogUrl = (overrides = {}) => {
  const url = new URL(props.productsUrl, window.location.origin)
  const categoryId = Object.prototype.hasOwnProperty.call(overrides, 'category_id')
    ? overrides.category_id
    : filterForm.category_id

  if (categoryId) url.searchParams.set('category_id', categoryId)
  if (filterForm.sort !== 'latest') url.searchParams.set('sort', filterForm.sort)
  if (filterForm.collection !== 'all') url.searchParams.set('collection', filterForm.collection)

  Object.entries(filterForm.attributes).forEach(([slug, value]) => {
    if (value) url.searchParams.set(`attributes[${slug}]`, value)
  })

  return `${url.pathname}${url.search}`
}

const categoryUrl = (categoryId) => catalogUrl({ category_id: categoryId })

const applyFilters = () => {
  window.location.href = catalogUrl()
}

const clearFilters = () => {
  window.location.href = props.productsUrl
}

const moveCarousel = (direction = 1) => {
  const track = carouselTrack.value
  if (!track || track.scrollWidth <= track.clientWidth) return

  const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 12
  const atStart = track.scrollLeft <= 12
  if (direction > 0 && atEnd) {
    track.scrollTo({ left: 0, behavior: 'smooth' })
  } else if (direction < 0 && atStart) {
    track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' })
  } else {
    track.scrollBy({ left: direction * Math.max(track.clientWidth * 0.72, 320), behavior: 'smooth' })
  }
}

const pauseCarousel = () => {
  if (carouselTimer) window.clearInterval(carouselTimer)
  carouselTimer = null
}

const startCarousel = () => {
  pauseCarousel()
  if (bestSellers.value.length > 1) {
    carouselTimer = window.setInterval(() => moveCarousel(1), 3200)
  }
}

const loadMore = async () => {
  if (!nextPageUrl.value || loadingMore.value) return
  loadingMore.value = true
  loadError.value = ''

  try {
    const { data } = await axios.get(nextPageUrl.value, {
      headers: { Accept: 'application/json' },
    })
    const incoming = Array.isArray(data?.products) ? data.products : []
    const knownIds = new Set(products.value.map((product) => product.id))
    products.value.push(...incoming.filter((product) => !knownIds.has(product.id)))
    nextPageUrl.value = data?.pagination?.next_page_url || null
    total.value = Number(data?.pagination?.total || total.value)
  } catch (error) {
    loadError.value = error?.response?.data?.message || 'Could not load more products.'
  } finally {
    loadingMore.value = false
  }
}

onMounted(async () => {
  startCarousel()
  await nextTick()
  observer = new IntersectionObserver((entries) => {
    if (entries.some((entry) => entry.isIntersecting)) loadMore()
  }, { rootMargin: '500px 0px' })
  if (loadTrigger.value) observer.observe(loadTrigger.value)
})

onBeforeUnmount(() => {
  pauseCarousel()
  observer?.disconnect()
})
</script>

<style scoped>
.carousel-button {
  display: inline-flex;
  height: 2.5rem;
  width: 2.5rem;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  border: 1px solid rgb(203 213 225);
  color: rgb(51 65 85);
  transition: all 150ms ease;
}

.carousel-button:hover {
  border-color: rgb(15 23 42);
  background: rgb(15 23 42);
  color: white;
}

.catalog-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.catalog-scrollbar::-webkit-scrollbar {
  display: none;
}

.filter-select {
  width: 100%;
  border: 1px solid rgb(203 213 225);
  border-radius: 0.75rem;
  background: white;
  padding: 0.65rem 2.25rem 0.65rem 0.75rem;
  color: rgb(15 23 42);
  font-size: 0.75rem;
  outline: none;
  transition: border-color 150ms ease, box-shadow 150ms ease;
}

.filter-select:focus {
  border-color: rgb(71 85 105);
  box-shadow: 0 0 0 3px rgb(100 116 139 / 12%);
}

:global(.dark) .filter-select {
  border-color: rgb(51 65 85);
  background: rgb(2 6 23);
  color: rgb(241 245 249);
}
</style>
