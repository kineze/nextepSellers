<template>
  <div class="contents">
    <div class="relative mx-5 hidden max-w-xl flex-1 md:block">
      <div class="relative">
        <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
        <input
          v-model.trim="query"
          type="search"
          autocomplete="off"
          placeholder="Search products, codes or SKU..."
          class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-10 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-slate-500 dark:focus:bg-slate-950"
          @focus="desktopOpen = true"
          @input="scheduleSearch"
          @keydown.esc="desktopOpen = false"
        />
        <i v-if="loading" class="fas fa-circle-notch fa-spin pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
        <button v-else-if="query" type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 hover:text-slate-800 dark:hover:text-white" aria-label="Clear product search" @click="clearSearch">
          <i class="fas fa-xmark text-xs"></i>
        </button>
      </div>

      <div v-if="desktopOpen && query" class="absolute left-0 right-0 top-full z-[1300] mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/15 dark:border-slate-700 dark:bg-slate-900">
        <search-results :products="products" :loading="loading" :query="query" :products-url="productsUrl" @select="desktopOpen = false" />
      </div>
    </div>

    <Teleport to="body">
      <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0">
        <div v-if="mobileOpen" class="fixed inset-0 z-[10000] flex items-end bg-slate-950/55 backdrop-blur-sm md:hidden" @click.self="closeMobile">
          <section class="flex max-h-[82vh] w-full flex-col rounded-t-3xl border-t border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
            <div class="mx-auto mt-2 h-1.5 w-12 rounded-full bg-slate-300 dark:bg-slate-700"></div>
            <div class="flex items-center justify-between gap-3 px-4 pb-3 pt-3">
              <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Product Search</p>
                <h2 class="mt-1 text-lg font-black text-slate-950 dark:text-white">Find products</h2>
              </div>
              <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 dark:border-slate-700 dark:text-slate-300" aria-label="Close product search" @click="closeMobile">
                <i class="fas fa-xmark"></i>
              </button>
            </div>
            <div class="border-y border-slate-200 p-4 dark:border-slate-800">
              <div class="relative">
                <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                <input
                  ref="mobileInput"
                  v-model.trim="query"
                  type="search"
                  autocomplete="off"
                  placeholder="Product name, code or SKU"
                  class="w-full rounded-2xl border border-slate-300 bg-slate-50 py-3 pl-10 pr-10 text-base text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/15 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                  @input="scheduleSearch"
                />
                <i v-if="loading" class="fas fa-circle-notch fa-spin pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
              </div>
            </div>
            <div class="min-h-48 flex-1 overflow-y-auto overscroll-contain pb-[max(1rem,env(safe-area-inset-bottom))]">
              <search-results :products="products" :loading="loading" :query="query" :products-url="productsUrl" @select="closeMobile" />
            </div>
          </section>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'
import SearchResults from './SellerProductSearchResults.vue'

const props = defineProps({
  productsUrl: { type: String, required: true },
})

const query = ref('')
const products = ref([])
const loading = ref(false)
const desktopOpen = ref(false)
const mobileOpen = ref(false)
const mobileInput = ref(null)
let timer = null
let requestId = 0
let previousOverflow = ''

const search = async () => {
  const value = query.value.trim()
  if (!value) {
    products.value = []
    loading.value = false
    return
  }

  const currentRequest = ++requestId
  loading.value = true
  try {
    const { data } = await axios.get('/api/seller/order-products', { params: { search: value, limit: 8 } })
    if (currentRequest === requestId) products.value = Array.isArray(data?.products) ? data.products : []
  } catch {
    if (currentRequest === requestId) products.value = []
  } finally {
    if (currentRequest === requestId) loading.value = false
  }
}

const scheduleSearch = () => {
  window.clearTimeout(timer)
  timer = window.setTimeout(search, 220)
}

const clearSearch = () => {
  query.value = ''
  products.value = []
}

const openMobile = async () => {
  mobileOpen.value = true
  previousOverflow = document.body.style.overflow
  document.body.style.overflow = 'hidden'
  await nextTick()
  mobileInput.value?.focus()
}

const closeMobile = () => {
  mobileOpen.value = false
  document.body.style.overflow = previousOverflow
}

const closeOnOutsideClick = (event) => {
  if (!event.target.closest('[type="search"]') && !event.target.closest('.absolute.z-\\[1300\\]')) desktopOpen.value = false
}

onMounted(() => {
  window.addEventListener('seller-product-search-open', openMobile)
  document.addEventListener('click', closeOnOutsideClick)
})

onBeforeUnmount(() => {
  window.clearTimeout(timer)
  window.removeEventListener('seller-product-search-open', openMobile)
  document.removeEventListener('click', closeOnOutsideClick)
  document.body.style.overflow = previousOverflow
})
</script>
