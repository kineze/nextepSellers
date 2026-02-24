<template>
  <div class="mt-3 space-y-4">
    <template v-if="hasVariants">
      <div v-for="attributeKey in attributeKeys" :key="attributeKey">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ attributeKey }}</p>

        <div v-if="isColorAttribute(attributeKey)" class="flex flex-wrap gap-2">
          <button
            v-for="option in attributeOptions[attributeKey]"
            :key="`${attributeKey}-${option.value}`"
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-2 py-1 text-xs text-slate-700 hover:border-blue-500 dark:border-slate-700 dark:text-slate-200"
            :class="isSelected(attributeKey, option.value) ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200' : ''"
            @click="selectValue(attributeKey, option.value)"
          >
            <span class="inline-block h-4 w-4 rounded-full border border-white/70" :style="{ backgroundColor: option.color }"></span>
            <span>{{ option.label }}</span>
          </button>
        </div>

        <select
          v-else
          v-model="selected[attributeKey]"
          class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
        >
          <option value="">Select {{ attributeKey }}</option>
          <option v-for="option in attributeOptions[attributeKey]" :key="`${attributeKey}-${option.value}`" :value="option.value">{{ option.label }}</option>
        </select>
      </div>
    </template>

    <p v-else class="text-sm text-slate-500 dark:text-slate-400">This product has no variant selections.</p>

    <form method="GET" :action="orderUrl" class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900/60">
      <input type="hidden" name="product_id" :value="productId">
      <input type="hidden" name="variant_id" :value="selectedVariant?.id || ''">

      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Quantity</p>
          <div class="mt-1 inline-flex items-center rounded-xl border border-slate-300 dark:border-slate-700">
            <button type="button" class="px-3 py-1.5 text-sm font-bold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800" @click="decreaseQty">-</button>
            <input
              name="quantity"
              type="number"
              min="1"
              :value="qty"
              @input="onQtyInput"
              class="w-16 border-x border-slate-300 bg-transparent px-2 py-1.5 text-center text-sm text-slate-900 outline-none dark:border-slate-700 dark:text-slate-100"
            >
            <button type="button" class="px-3 py-1.5 text-sm font-bold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800" @click="increaseQty">+</button>
          </div>
        </div>

        <button
          type="submit"
          class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="hasVariants && !selectedVariant"
        >
          <i class="fas fa-cart-plus"></i>
          Place Order
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'

const props = defineProps({
  productId: { type: Number, required: true },
  hasVariants: { type: Boolean, default: false },
  variants: { type: Array, default: () => [] },
  orderUrl: { type: String, required: true },
})

const qty = ref(1)

const attributeOptions = computed(() => {
  const map = {}

  for (const variant of props.variants) {
    const attrs = variant?.attributes || {}
    for (const [key, meta] of Object.entries(attrs)) {
      if (!map[key]) map[key] = []
      const exists = map[key].some((item) => item.value === meta.value)
      if (!exists) {
        map[key].push({ value: meta.value, label: meta.label, color: meta.color || null })
      }
    }
  }

  return map
})

const attributeKeys = computed(() => Object.keys(attributeOptions.value))

const selected = reactive({})
for (const key of attributeKeys.value) {
  const first = attributeOptions.value[key]?.[0]?.value || ''
  selected[key] = first
}

const isColorAttribute = (key) => {
  const options = attributeOptions.value[key] || []
  return options.length > 0 && options.every((opt) => !!opt.color)
}

const isSelected = (key, value) => selected[key] === value

const selectValue = (key, value) => {
  selected[key] = value
}

const selectedVariant = computed(() => {
  if (!props.hasVariants) return null
  if (!attributeKeys.value.length) return null

  for (const key of attributeKeys.value) {
    if (!selected[key]) return null
  }

  return props.variants.find((variant) => {
    const attrs = variant?.attributes || {}
    return attributeKeys.value.every((key) => (attrs[key]?.value || '') === (selected[key] || ''))
  }) || null
})

const decreaseQty = () => {
  qty.value = Math.max(1, Number(qty.value || 1) - 1)
}

const increaseQty = () => {
  qty.value = Math.max(1, Number(qty.value || 1) + 1)
}

const onQtyInput = (event) => {
  const value = Number(event.target.value || 1)
  qty.value = Number.isFinite(value) && value >= 1 ? value : 1
  event.target.value = String(qty.value)
}
</script>
