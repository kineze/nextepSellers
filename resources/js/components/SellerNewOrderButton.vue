<template>
  <div class="pointer-events-none fixed bottom-5 right-5 z-[9998]">
    <button
      type="button"
      class="pointer-events-auto inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/30 transition hover:-translate-y-0.5 hover:bg-black dark:bg-white dark:text-slate-900"
      @click="isOpen = true"
    >
      <i class="fas fa-plus"></i>
      New Order
    </button>

    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="pointer-events-auto fixed inset-0 z-[9999] flex items-end justify-center bg-slate-950/45 p-4 backdrop-blur-sm sm:items-center"
        @click.self="isOpen = false"
      >
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[0.7rem] font-semibold uppercase tracking-[0.22em] text-blue-600 dark:text-blue-300">New Order</p>
              <h2 class="mt-1 text-xl font-bold text-slate-950 dark:text-white">Choose order upload type</h2>
            </div>
            <button
              type="button"
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
              aria-label="Close new order modal"
              @click="isOpen = false"
            >
              <i class="fas fa-xmark"></i>
            </button>
          </div>

          <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <a
              :href="singleOrderUrl"
              class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-950/70 dark:hover:border-blue-500/40 dark:hover:bg-blue-500/10"
            >
              <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-300">
                <i class="fas fa-file-circle-plus"></i>
              </span>
              <p class="mt-4 text-base font-bold text-slate-950 dark:text-white">Single Order Upload</p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Create one customer order.</p>
            </a>

            <a
              :href="bulkOrderUrl"
              class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-950/70 dark:hover:border-blue-500/40 dark:hover:bg-blue-500/10"
            >
              <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-300">
                <i class="fas fa-table-list"></i>
              </span>
              <p class="mt-4 text-base font-bold text-slate-950 dark:text-white">Bulk Order Upload</p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Upload multiple orders together.</p>
            </a>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

defineProps({
  singleOrderUrl: { type: String, required: true },
  bulkOrderUrl: { type: String, required: true },
})

const isOpen = ref(false)

const openModal = () => {
  isOpen.value = true
}

onMounted(() => {
  window.addEventListener('seller-new-order-open', openModal)
})

onBeforeUnmount(() => {
  window.removeEventListener('seller-new-order-open', openModal)
})
</script>
