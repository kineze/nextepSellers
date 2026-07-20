<template>
  <div class="space-y-6">
    <div v-if="loading" class="rounded-2xl border border-slate-200/70 bg-white/80 p-6 text-sm text-slate-500 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-400">
      Loading product details...
    </div>

    <div v-else-if="!product" class="rounded-2xl border border-slate-200/70 bg-white/80 p-6 text-sm text-slate-500 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-400">
      Product not found.
    </div>

    <template v-else>
      <div class="flex items-center justify-between">
        <a :href="productsUrl" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
          <i class="fas fa-arrow-left"></i>
          Back to Products
        </a>
      </div>

      <div class="grid min-w-0 gap-4 sm:gap-6 xl:grid-cols-5">
        <div class="min-w-0 max-w-full overflow-hidden xl:col-span-2 xl:sticky xl:top-24 xl:self-start">
          <div class="relative aspect-[4/3] w-full max-w-full overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800 sm:aspect-square">
            <video
              v-if="selectedMedia?.type === 'video'"
              :src="selectedMedia.src"
              controls
              playsinline
              preload="metadata"
              class="block h-full max-h-full w-full max-w-full bg-black object-contain"
            ></video>
            <img
              v-else-if="selectedMedia?.type === 'image'"
              :src="selectedMedia.src"
              :alt="product.title"
              class="block h-full max-h-full w-full max-w-full object-contain"
            />
            <div v-else class="flex h-full items-center justify-center text-sm text-slate-400 dark:text-slate-500">No media available</div>
            <a
              v-if="selectedMedia"
              :href="selectedMedia.src"
              :download="mediaFilename(selectedMedia)"
              class="absolute right-3 top-3 inline-flex items-center gap-2 rounded-xl bg-slate-950/90 px-3 py-2 text-xs font-bold uppercase tracking-wide text-white shadow-lg backdrop-blur transition hover:bg-black"
              :aria-label="`Download ${selectedMedia.type}`"
            >
              <i class="fas fa-download"></i>
              Download
            </a>
          </div>

          <div v-if="galleryItems.length" class="media-thumbnail-scroll mt-3 flex max-w-full gap-2 overflow-x-auto pb-1 sm:grid sm:grid-cols-5 sm:overflow-visible sm:pb-0">
            <div
              v-for="item in galleryItems"
              :key="item.key"
              class="group relative aspect-square w-20 shrink-0 sm:w-auto"
            >
              <button
                type="button"
                class="h-full w-full overflow-hidden rounded-lg border bg-slate-100 hover:border-blue-400 dark:bg-slate-800"
                :class="selectedMedia?.key === item.key ? 'border-blue-500 ring-2 ring-blue-500/30 dark:border-blue-300' : 'border-slate-200 dark:border-slate-700'"
                @click="selectedMedia = item"
              >
                <img
                  v-if="item.type === 'image'"
                  :src="item.src"
                  :alt="product.title"
                  class="h-full w-full object-contain"
                />
                <span v-else class="flex h-full w-full items-center justify-center bg-slate-900 text-white">
                  <i class="fas fa-play text-lg"></i>
                </span>
              </button>
              <a
                :href="item.src"
                :download="mediaFilename(item)"
                class="absolute bottom-1 right-1 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-950/90 text-[10px] text-white shadow-md transition hover:bg-black"
                :aria-label="`Download ${item.type}`"
                title="Download"
              >
                <i class="fas fa-download"></i>
              </a>
            </div>
          </div>
        </div>

        <div class="min-w-0 max-w-full space-y-4 overflow-hidden xl:col-span-3">
          <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <p class="text-[0.7rem] uppercase tracking-[0.24em] text-blue-600 dark:text-blue-300">Product Details</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ product.title }}</h1>
                <p class="mt-1 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ product.product_code }}</p>
              </div>
              <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-500/20 dark:text-blue-200">
                {{ product.category?.name || 'Uncategorized' }}
              </span>
              <span v-if="isReseller" class="rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-black uppercase tracking-wide text-white">Pricing Negotiable</span>
            </div>

            <p class="mt-4 text-sm leading-relaxed text-slate-700 dark:text-slate-300">{{ product.small_description }}</p>

            <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm dark:bg-slate-800/70">
              <span v-if="priceRange.min === null" class="text-slate-500 dark:text-slate-400">Price unavailable</span>
              <span v-else-if="priceRange.unlimited" class="font-bold text-slate-900 dark:text-white">LKR {{ toMoney(priceRange.min) }} or higher</span>
              <span v-else-if="priceRange.min === priceRange.max" class="font-bold text-slate-900 dark:text-white">LKR {{ toMoney(priceRange.min) }}</span>
              <span v-else class="font-bold text-slate-900 dark:text-white">LKR {{ toMoney(priceRange.min) }} - {{ toMoney(priceRange.max) }}</span>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
              <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Product Details</h2>
              <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                {{ variants.length }} variant{{ variants.length === 1 ? '' : 's' }}
              </span>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                <p class="text-xs text-slate-500 dark:text-slate-400">Delivery Fee</p>
                <p class="mt-1 font-semibold text-slate-900 dark:text-white">
                  {{ product.is_free_shipping ? 'Free shipping' : `LKR ${toMoney(Number(product.delivery_fee || 0))}` }}
                </p>
              </div>
              <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                <p class="text-xs text-slate-500 dark:text-slate-400">Variant Type</p>
                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ product.has_varients ? 'Multiple variants' : 'Single product' }}</p>
              </div>
              <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                <p class="text-xs text-slate-500 dark:text-slate-400">Stock</p>
                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ totalStock.toLocaleString() }}</p>
              </div>
            </div>

            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                  <tr>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wide">SKU</th>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wide">Attributes</th>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wide">{{ isReseller ? 'Reseller Price' : 'Price' }}</th>
                    <th v-if="isReseller" class="px-3 py-2 font-semibold uppercase tracking-wide">Maximum Price</th>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wide">Stock</th>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wide">Reorder</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                  <tr v-if="!variants.length">
                    <td :colspan="isReseller ? 6 : 5" class="px-3 py-4 text-center text-slate-500 dark:text-slate-400">No variant data available.</td>
                  </tr>
                  <tr v-for="variant in variants" :key="variant.id">
                    <td class="px-3 py-2 font-semibold text-slate-900 dark:text-white">{{ variant.sku || '-' }}</td>
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ formatAttributes(variant.attributes) }}</td>
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">LKR {{ toMoney(Number(isReseller ? variant.reseller_price : variant.price || 0)) }}</td>
                    <td v-if="isReseller" class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ variant.maximum_selling_price === null || variant.maximum_selling_price === undefined ? 'No limit' : `LKR ${toMoney(Number(variant.maximum_selling_price))}` }}</td>
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ Number(variant.stock_quantity || 0).toLocaleString() }}</td>
                    <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ Number(variant.reorder_level || 0).toLocaleString() }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-if="isReseller" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
            <h2 class="text-sm font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Margin Product</h2>
            <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">Choose any customer selling price at or above the reseller price. A maximum applies only when one is shown. Your earning is the difference between the selling and reseller prices; no level commission is added.</p>
          </div>

          <div v-else class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Seller Earnings by Level</h2>

            <p v-if="!levelRows.length" class="mt-3 text-sm text-slate-500 dark:text-slate-400">
              This product does not have seller level earnings configured yet.
            </p>

            <template v-else>
              <div v-if="currentRow" class="mt-3 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-500/30 dark:bg-blue-500/10">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">Your Current Level</p>
                <p class="mt-1 text-base font-bold text-slate-900 dark:text-white">{{ levelName(currentRow) }}</p>
                <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">
                  You currently earn
                  <span class="font-semibold text-slate-900 dark:text-white">{{ commissionLabel(currentRow) }}</span>
                  on this product.
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Current points: {{ Number(seller.points || 0).toLocaleString() }}</p>
              </div>

              <div v-else class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                <p class="text-sm text-slate-600 dark:text-slate-300">Your seller level is not mapped yet. Contact support to assign a level and unlock level-based earnings.</p>
              </div>

              <div v-if="nextRow" class="mt-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900/60">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Next Level Opportunity</p>
                <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">
                  Reach
                  <span class="font-semibold text-slate-900 dark:text-white">{{ levelName(nextRow) }}</span>
                  to get
                  <span class="font-semibold text-blue-700 dark:text-blue-300">{{ commissionLabel(nextRow) }}</span>
                  on this product.
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Target points: {{ Number(nextRow.level?.points || 0).toLocaleString() }}</p>
              </div>

              <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div
                  v-for="row in levelRows"
                  :key="row.id"
                  class="rounded-xl border px-4 py-3"
                  :class="isCurrentLevel(row)
                    ? 'border-blue-300 bg-blue-50 dark:border-blue-500/40 dark:bg-blue-500/10'
                    : 'border-slate-200 dark:border-slate-700'"
                >
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ levelName(row) }}
                    <span v-if="isCurrentLevel(row)" class="ml-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-white">Current</span>
                  </p>
                  <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ commissionLabel(row) }}</p>
                  <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Points required: {{ Number(row.level?.points || 0).toLocaleString() }}</p>
                </div>
              </div>
            </template>
          </div>

          <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Long Description</h2>
            <div class="prose prose-sm mt-3 max-w-none dark:prose-invert" v-html="product.long_description || '<p>No long description.</p>'"></div>
          </div>

          <div v-if="product.product_video" class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Product Video</h2>
            <div class="mt-3 flex flex-wrap gap-2">
              <a
                :href="videoUrl(product.product_video)"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
              >
                <i class="fas fa-play"></i> Watch video
              </a>
              <a
                :href="videoUrl(product.product_video)"
                :download="filenameFromPath(product.product_video, 'product-video')"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
              >
                <i class="fas fa-download"></i> Download video
              </a>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  productId: { type: Number, required: true },
  productDataUrl: { type: String, required: true },
  productsUrl: { type: String, required: true },
})

const loading = ref(true)
const product = ref(null)
const seller = ref({ seller_level_id: null, points: 0 })
const selectedMedia = ref(null)

const images = computed(() => product.value?.images || [])
const variants = computed(() => product.value?.varients || [])
const isReseller = computed(() => product.value?.pricing_model === 'reseller')
const galleryItems = computed(() => {
  const items = []

  if (product.value?.product_video) {
    items.push({
      key: `video-${product.value.product_video}`,
      type: 'video',
      src: videoUrl(product.value.product_video),
      is_primary: false,
    })
  }

  items.push(...images.value.map((img) => ({
    key: `image-${img.id || img.path}`,
    type: 'image',
    src: imageUrl(img.path),
    is_primary: !!img.is_primary,
  })))

  return items
})
const totalStock = computed(() => {
  return variants.value.reduce((sum, variant) => sum + Number(variant?.stock_quantity || 0), 0)
})
const levelRows = computed(() => {
  const rows = product.value?.product_levels || []
  return [...rows]
    .filter((r) => !!r.level)
    .sort((a, b) => (a.level?.level_no || 0) - (b.level?.level_no || 0))
})

const currentRow = computed(() => {
  const levelId = Number(seller.value?.seller_level_id || 0)
  if (!levelId) return null
  return levelRows.value.find((row) => Number(row.level_id) === levelId) || null
})

const nextRow = computed(() => {
  if (!currentRow.value) return null
  const currentNo = Number(currentRow.value.level?.level_no || 0)
  return levelRows.value.find((row) => Number(row.level?.level_no || 0) > currentNo) || null
})

const priceRange = computed(() => {
  const minimumVals = variants.value
    .map((v) => isReseller.value ? v?.reseller_price : v?.price)
    .filter((v) => v !== null && v !== undefined)
    .map((v) => Number(v))
    .filter((v) => Number.isFinite(v))

  const maximumVals = variants.value
    .map((v) => isReseller.value ? v?.maximum_selling_price : v?.price)
    .filter((v) => v !== null && v !== undefined)
    .map((v) => Number(v))
    .filter((v) => Number.isFinite(v))

  if (!minimumVals.length) return { min: null, max: null, unlimited: false }
  const unlimited = isReseller.value && variants.value.some((variant) => variant?.maximum_selling_price === null || variant?.maximum_selling_price === undefined)
  return {
    min: Math.min(...minimumVals),
    max: maximumVals.length ? Math.max(...maximumVals) : null,
    unlimited,
  }
})

const fetchData = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(props.productDataUrl)
    product.value = data.product || null
    seller.value = data.seller || { seller_level_id: null, points: 0 }

    selectedMedia.value = galleryItems.value.find((item) => item.type === 'video')
      || galleryItems.value.find((item) => item.is_primary)
      || galleryItems.value[0]
      || null
  } catch {
    product.value = null
  } finally {
    loading.value = false
  }
}

const imageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return `/storage/${path}`
}

const videoUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return `/storage/${path}`
}

const filenameFromPath = (path, fallback = 'product-media') => {
  if (!path) return fallback

  try {
    const pathname = new URL(path, window.location.origin).pathname
    return decodeURIComponent(pathname.split('/').filter(Boolean).pop() || fallback)
  } catch {
    return String(path).split('/').filter(Boolean).pop() || fallback
  }
}

const mediaFilename = (media) => filenameFromPath(
  media?.src,
  media?.type === 'video' ? 'product-video' : 'product-image',
)

const toMoney = (value) => Number(value).toFixed(2)

const formatAttributes = (attributes) => {
  const entries = Object.entries(attributes || {})
  if (!entries.length) return '-'

  return entries
    .map(([key, value]) => {
      const label = String(key || '').replace(/_/g, ' ')
      if (value && typeof value === 'object') {
        return `${label}: ${value.name || value.color || '-'}`
      }

      return `${label}: ${value || '-'}`
    })
    .join(', ')
}

const commissionLabel = (row) => {
  if (!row) return '-'
  return row.type === 'percentage' ? `${row.value}%` : `LKR ${row.value}`
}

const levelName = (row) => {
  if (!row?.level) return 'Level -'
  return row.level.level_name || `Level ${row.level.level_no || '-'}`
}

const isCurrentLevel = (row) => Number(row.level_id) === Number(seller.value?.seller_level_id || 0)

onMounted(fetchData)
</script>

<style scoped>
.media-thumbnail-scroll {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.media-thumbnail-scroll::-webkit-scrollbar {
  display: none;
}
</style>
