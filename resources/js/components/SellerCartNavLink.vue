<template>
  <a
    :href="href"
    class="flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
  >
    <span class="inline-flex items-center gap-2">
      <i class="fas fa-basket-shopping w-4"></i>
      Cart
    </span>

    <span
      class="min-w-6 rounded-full px-2 py-0.5 text-center text-xs"
      :class="totalItems > 0 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
    >
      {{ totalItems }}
    </span>
  </a>
</template>

<script setup>
import { useSellerCart } from '../composables/useSellerCart'

defineProps({
  href: { type: String, required: true },
})

const { totalItems } = useSellerCart()
</script>
