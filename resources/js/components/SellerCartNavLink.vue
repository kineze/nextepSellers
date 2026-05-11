<template>
  <a
    :href="href"
    class="seller-cart-link relative flex items-center justify-between gap-2 rounded-xl border px-2 py-2 text-[0.78rem] font-semibold leading-none transition"
    :class="isActive ? 'border-blue-200 bg-blue-50 text-blue-800 shadow-sm shadow-blue-100/60 dark:border-blue-500/30 dark:bg-blue-500/15 dark:text-blue-200 dark:shadow-none' : 'border-transparent text-slate-600 hover:-translate-y-0.5 hover:border-blue-100 hover:bg-blue-50/70 hover:text-blue-700 dark:text-slate-300 dark:hover:border-blue-500/20 dark:hover:bg-slate-800/80 dark:hover:text-blue-200'"
  >
    <span class="seller-cart-link-label inline-flex items-center gap-2">
      <span class="seller-sidebar-icon inline-flex aspect-square h-7 w-7 min-h-7 min-w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[0.78rem] text-slate-600 transition dark:bg-slate-800 dark:text-slate-300">
        <i class="fas fa-basket-shopping"></i>
      </span>
      <span class="seller-sidebar-label">Cart</span>
    </span>

    <span
      class="seller-cart-link-count min-w-5 rounded-full px-1.5 py-0.5 text-center text-[0.65rem]"
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
