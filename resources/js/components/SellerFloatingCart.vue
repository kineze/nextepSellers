<template>
  <div class="pointer-events-none fixed bottom-5 right-5 z-[1000]">
    <button
      type="button"
      class="pointer-events-auto inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/30 hover:bg-black dark:bg-white dark:text-slate-900"
      @click="isOpen = !isOpen"
    >
      <i class="fas fa-basket-shopping"></i>
      Cart ({{ totalItems }})
    </button>

    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 translate-y-1 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-1 scale-95"
    >
      <div
        v-if="isOpen"
        class="pointer-events-auto mt-3 w-[min(92vw,380px)] rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-2xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/95"
      >
        <div class="flex items-center justify-between">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cart Summary</p>
          <button type="button" class="text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white" @click="isOpen = false">Close</button>
        </div>

        <p v-if="!items.length" class="mt-4 text-sm text-slate-500 dark:text-slate-400">Your cart is empty.</p>

        <div v-else class="mt-3 space-y-3">
          <div v-for="item in items" :key="item.key" class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900/70">
            <div class="flex gap-3">
              <img
                v-if="item.image"
                :src="item.image"
                :alt="item.title"
                class="h-14 w-14 rounded-lg object-cover"
              >
              <div v-else class="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-100 text-[10px] text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                No image
              </div>

              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ item.title }}</p>
                <p v-if="item.sku" class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">SKU {{ item.sku }}</p>
                <p v-if="item.productCode" class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ item.productCode }}</p>
                <p v-if="attributeText(item.attributes)" class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ attributeText(item.attributes) }}</p>
                <p v-if="item.price !== null" class="mt-1 text-xs font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(item.price) }}</p>
              </div>
            </div>

            <div class="mt-2 flex items-center justify-between gap-2">
              <div class="inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-700">
                <button type="button" class="px-2 py-1 text-xs" @click="updateItemQty(item.key, Math.max(1, item.qty - 1))">-</button>
                <input
                  type="number"
                  min="1"
                  :value="item.qty"
                  class="w-12 border-x border-slate-300 bg-transparent px-1 py-1 text-center text-xs outline-none dark:border-slate-700"
                  @input="onQtyInput(item.key, $event)"
                >
                <button type="button" class="px-2 py-1 text-xs" @click="updateItemQty(item.key, item.qty + 1)">+</button>
              </div>

              <button type="button" class="text-xs font-semibold text-rose-600 hover:text-rose-700" @click="removeItem(item.key)">Remove</button>
            </div>
          </div>

          <div class="rounded-xl bg-slate-50 px-3 py-2 text-sm dark:bg-slate-800/70">
            <p class="flex items-center justify-between font-semibold">
              <span>Subtotal</span>
              <span>LKR {{ toMoney(subtotal) }}</span>
            </p>
          </div>

          <div class="flex gap-2">
            <button
              type="button"
              class="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="clear"
            >
              Clear Cart
            </button>
            <a
              :href="ordersUrl"
              class="flex-1 rounded-xl bg-blue-600 px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700"
            >
              Go to Orders
            </a>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useSellerCart } from '../composables/useSellerCart'

defineProps({
  ordersUrl: { type: String, required: true },
})

const isOpen = ref(false)

const {
  items,
  totalItems,
  subtotal,
  removeItem,
  updateItemQty,
  clear,
} = useSellerCart()

const toMoney = (value) => Number(value || 0).toFixed(2)

const attributeText = (attributes) => {
  if (!attributes || typeof attributes !== 'object') return ''

  const entries = Object.entries(attributes)
    .map(([key, meta]) => {
      const label = String(meta?.label || meta?.value || '').trim()
      return label ? `${key}: ${label}` : ''
    })
    .filter(Boolean)

  return entries.join(', ')
}

const onQtyInput = (itemKey, event) => {
  const value = Number(event.target.value || 1)
  updateItemQty(itemKey, Number.isFinite(value) && value > 0 ? value : 1)
}
</script>
