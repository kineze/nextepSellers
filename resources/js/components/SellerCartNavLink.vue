<template>
  <a
    :href="href"
    class="realtive seller-cart-link flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition"
    :class="isActive ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800'"
  >
    <span class="seller-cart-link-label inline-flex items-center gap-2">
      <i class="seller-sidebar-icon fas fa-basket-shopping w-4"></i>
      <span class="seller-sidebar-label">Cart</span>
    </span>

    <span
      class="seller-cart-link-count min-w-6 rounded-full px-2 py-0.5 text-center text-xs"
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
  isActive: { type: Boolean, default: false },
})

const { totalItems } = useSellerCart()
</script>
