<template>
  <div>
    <div v-if="!query" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Start typing to search products.</div>
    <div v-else-if="loading && !products.length" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400"><i class="fas fa-circle-notch fa-spin mr-2"></i>Searching products...</div>
    <div v-else-if="!products.length" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No products found for “{{ query }}”.</div>
    <div v-else class="divide-y divide-slate-100 dark:divide-slate-800">
      <a v-for="product in products" :key="product.id" :href="`${productsUrl}/${product.id}`" class="flex items-center gap-3 px-4 py-3 transition hover:bg-slate-50 dark:hover:bg-slate-800/70" @click="$emit('select')">
        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
          <img v-if="product.image" :src="product.image" :alt="product.title" class="h-full w-full object-contain" />
          <span v-else class="flex h-full items-center justify-center text-slate-400"><i class="fas fa-box"></i></span>
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2">
            <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ product.title }}</p>
            <span v-if="product.pricing_model === 'reseller'" class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[8px] font-black uppercase text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Negotiable</span>
          </div>
          <p class="mt-0.5 truncate text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ product.product_code || 'No product code' }}</p>
          <p class="mt-1 text-xs font-bold text-slate-700 dark:text-slate-200">{{ priceLabel(product) }}</p>
        </div>
        <i class="fas fa-chevron-right text-xs text-slate-300"></i>
      </a>
    </div>
  </div>
</template>

<script setup>
defineProps({
  products: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  query: { type: String, default: '' },
  productsUrl: { type: String, required: true },
})

defineEmits(['select'])

const money = (value) => Number(value || 0).toLocaleString('en-LK', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
})

const priceLabel = (product) => {
  const variants = Array.isArray(product?.variants) ? product.variants : []
  if (!variants.length) return 'Price unavailable'

  if (product.pricing_model === 'reseller') {
    const minimum = Math.min(...variants.map((variant) => Number(variant.reseller_price || 0)))
    const maximum = Math.max(...variants.map((variant) => Number(variant.maximum_selling_price || 0)))
    return `LKR ${money(minimum)} – ${money(maximum)}`
  }

  const prices = variants.map((variant) => Number(variant.price || 0))
  const minimum = Math.min(...prices)
  const maximum = Math.max(...prices)
  return minimum === maximum ? `LKR ${money(minimum)}` : `LKR ${money(minimum)} – ${money(maximum)}`
}
</script>
