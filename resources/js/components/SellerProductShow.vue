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

      <div class="grid gap-6 xl:grid-cols-5">
        <div class="xl:col-span-2 xl:sticky xl:top-24 xl:self-start">
          <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
            <img v-if="selectedImage" :src="selectedImage" :alt="product.title" class="h-[430px] w-full object-cover" />
            <div v-else class="flex h-[430px] items-center justify-center text-sm text-slate-400 dark:text-slate-500">No image available</div>
          </div>

          <div v-if="images.length" class="mt-3 grid grid-cols-5 gap-2">
            <button
              v-for="img in images"
              :key="img.id"
              type="button"
              class="overflow-hidden rounded-lg border border-slate-200 hover:border-blue-400 dark:border-slate-700"
              @click="selectedImage = imageUrl(img.path)"
            >
              <img :src="imageUrl(img.path)" :alt="product.title" class="h-16 w-full object-cover" />
            </button>
          </div>
        </div>

        <div class="space-y-4 xl:col-span-3">
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
            </div>

            <p class="mt-4 text-sm leading-relaxed text-slate-700 dark:text-slate-300">{{ product.small_description }}</p>

            <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm dark:bg-slate-800/70">
              <span v-if="priceRange.min === null" class="text-slate-500 dark:text-slate-400">Price unavailable</span>
              <span v-else-if="priceRange.min === priceRange.max" class="font-bold text-slate-900 dark:text-white">LKR {{ toMoney(priceRange.min) }}</span>
              <span v-else class="font-bold text-slate-900 dark:text-white">LKR {{ toMoney(priceRange.min) }} - {{ toMoney(priceRange.max) }}</span>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Variant Selection</h2>
            <seller-product-variant-selector
              :product-id="product.id"
              :product-title="product.title"
              :product-code="product.product_code"
              :product-image="selectedImage"
              :has-variants="hasVariantOptions"
              :variants="normalizedVariants"
            />
          </div>

          <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
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
const selectedImage = ref('')

const images = computed(() => product.value?.images || [])
const variants = computed(() => product.value?.varients || [])
const hasVariantOptions = computed(() => !!product.value?.has_varients && variants.value.length > 0)
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
  const vals = variants.value
    .map((v) => v?.price)
    .filter((v) => v !== null && v !== undefined)
    .map((v) => Number(v))
    .filter((v) => Number.isFinite(v))

  if (!vals.length) return { min: null, max: null }
  return { min: Math.min(...vals), max: Math.max(...vals) }
})

const normalizedVariants = computed(() => {
  return variants.value.map((variant) => {
    const attrs = {}
    const rawAttrs = variant?.attributes || {}

    for (const [key, val] of Object.entries(rawAttrs)) {
      if (val && typeof val === 'object') {
        const rawValue = String(val.color || val.name || '').trim().toLowerCase()
        attrs[key] = {
          value: rawValue,
          label: String(val.name || val.color || '').trim(),
          color: val.color || null,
        }
      } else {
        const raw = String(val || '').trim()
        attrs[key] = {
          value: raw.toLowerCase(),
          label: raw,
          color: null,
        }
      }
    }

    return {
      id: variant.id,
      sku: variant.sku,
      price: variant.price,
      stock_quantity: variant.stock_quantity,
      reorder_level: variant.reorder_level,
      is_active: !!variant.is_active,
      attributes: attrs,
    }
  })
})

const fetchData = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(props.productDataUrl)
    product.value = data.product || null
    seller.value = data.seller || { seller_level_id: null, points: 0 }

    const imgs = product.value?.images || []
    const primary = imgs.find((img) => !!img.is_primary) || imgs[0]
    selectedImage.value = primary ? imageUrl(primary.path) : ''
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

const toMoney = (value) => Number(value).toFixed(2)

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
