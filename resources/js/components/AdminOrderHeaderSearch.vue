<template>
  <div ref="containerRef" class="relative w-full max-w-2xl">
    <div
      class="flex min-h-11 items-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm ring-1 ring-black/[0.02] transition focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100 dark:border-slate-700 dark:bg-slate-900 dark:ring-white/[0.03] dark:focus-within:border-emerald-500 dark:focus-within:ring-emerald-500/15"
    >
      <button
        type="button"
        class="inline-flex h-11 shrink-0 items-center gap-2 border-r border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
        @click="typeOpen = !typeOpen"
      >
        <i class="fa-solid fa-filter text-[11px] text-emerald-600 dark:text-emerald-300" aria-hidden="true"></i>
        <span>{{ selectedType.label }}</span>
        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400" aria-hidden="true"></i>
      </button>

      <div class="relative flex-1">
        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i>
        <input
          v-model.trim="query"
          type="search"
          class="h-11 w-full border-0 bg-transparent pl-9 pr-10 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0 dark:text-slate-100"
          :placeholder="placeholderText"
          @input="resetAndSearch"
          @keydown.down.prevent="moveActive(1)"
          @keydown.up.prevent="moveActive(-1)"
          @keydown.enter.prevent="openActiveResult"
          @keydown.esc.prevent="closeDropdowns"
          @focus="openResultsIfReady"
        />
        <button
          v-if="query"
          type="button"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200"
          @click="clearSearch"
        >
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
          <span class="sr-only">Clear search</span>
        </button>
      </div>
    </div>

    <div
      v-if="typeOpen"
      class="absolute left-0 top-12 z-[1200] w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl ring-1 ring-black/5 dark:border-slate-700 dark:bg-slate-900"
    >
      <button
        v-for="type in searchTypes"
        :key="type.value"
        type="button"
        class="flex w-full items-center justify-between px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
        @click="selectType(type)"
      >
        <span>{{ type.label }}</span>
        <i v-if="type.value === selectedType.value" class="fa-solid fa-check text-xs text-emerald-600 dark:text-emerald-300" aria-hidden="true"></i>
      </button>
    </div>

    <div
      v-if="showResults"
      class="absolute left-0 right-0 top-[3.25rem] z-[1190] mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-slate-700 dark:bg-slate-900"
    >
      <div class="max-h-96 overflow-y-auto p-2" @scroll="onScroll">
        <button
          v-for="(result, index) in results"
          :key="result.id"
          type="button"
          class="flex w-full gap-3 rounded-xl px-3 py-3 text-left transition"
          :class="index === activeIndex ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'hover:bg-slate-50 dark:hover:bg-slate-800/80'"
          @click="openResult(result)"
        >
          <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white dark:bg-white dark:text-slate-900">
            #{{ result.id }}
          </span>
          <span class="min-w-0 flex-1">
            <span class="flex flex-wrap items-center gap-2">
              <span class="font-semibold text-slate-900 dark:text-white">{{ result.label }}</span>
              <span :class="statusClass(result.status)" class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
                {{ result.status || 'unknown' }}
              </span>
            </span>
            <span class="mt-1 block truncate text-xs text-slate-500 dark:text-slate-400">
              {{ result.customer_name || 'No customer' }}
              <span v-if="result.phone"> · {{ result.phone }}</span>
              <span v-if="result.waybill_no"> · WB {{ result.waybill_no }}</span>
            </span>
            <span class="mt-1 block truncate text-xs text-slate-400 dark:text-slate-500">
              {{ result.seller_name || 'No seller' }}
              <span v-if="result.delivery_status"> · {{ result.delivery_status }}</span>
            </span>
          </span>
          <i class="fa-solid fa-arrow-up-right-from-square mt-1 text-xs text-slate-300" aria-hidden="true"></i>
        </button>

        <div v-if="loading" class="px-3 py-4 text-center text-xs font-medium text-slate-500 dark:text-slate-400">
          Searching orders...
        </div>

        <div v-if="!loading && query.length >= 2 && !results.length" class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
          No matching orders found.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'

const STORAGE_KEY = 'nextep-admin-order-header-search-type'

const searchTypes = [
  { label: 'All', value: 'all' },
  { label: 'Order', value: 'order' },
  { label: 'Phone', value: 'phone' },
  { label: 'Waybill', value: 'waybill' },
  { label: 'Customer', value: 'customer' },
  { label: 'Seller', value: 'seller' },
  { label: 'Status', value: 'status' },
]

const containerRef = ref(null)
const selectedType = ref(searchTypes[0])
const typeOpen = ref(false)
const query = ref('')
const results = ref([])
const loading = ref(false)
const hasSearched = ref(false)
const page = ref(1)
const hasMore = ref(true)
const activeIndex = ref(-1)
let searchTimer = null
let abortController = null

const placeholderText = computed(() => {
  const label = selectedType.value.value
  if (label === 'order') return 'Search order ID...'
  if (label === 'phone') return 'Search phone number...'
  if (label === 'waybill') return 'Search waybill...'
  if (label === 'customer') return 'Search customer name...'
  if (label === 'seller') return 'Search seller name, email or phone...'
  if (label === 'status') return 'Search status...'
  return 'Search orders by ID, phone, waybill, customer or seller...'
})

const showResults = computed(() => query.value.length >= 2 && (results.value.length > 0 || loading.value || hasSearched.value))

const selectType = (type) => {
  selectedType.value = type
  window.localStorage?.setItem(STORAGE_KEY, type.value)
  typeOpen.value = false
  resetAndSearch()
}

const loadSavedType = () => {
  const saved = window.localStorage?.getItem(STORAGE_KEY)
  const match = searchTypes.find((type) => type.value === saved)
  if (match) selectedType.value = match
}

const resetAndSearch = () => {
  page.value = 1
  results.value = []
  hasSearched.value = false
  hasMore.value = true
  activeIndex.value = -1

  if (abortController) abortController.abort()
  if (searchTimer) window.clearTimeout(searchTimer)
  if (query.value.length < 2) {
    loading.value = false
    return
  }

  searchTimer = window.setTimeout(fetchResults, 280)
}

const fetchResults = async () => {
  if (loading.value || !hasMore.value || query.value.length < 2) return

  loading.value = true
  abortController = new AbortController()
  try {
    const { data } = await axios.get('/api/admin/orders/search', {
      params: {
        q: query.value,
        type: selectedType.value.value,
        page: page.value,
      },
      signal: abortController.signal,
    })

    const rows = Array.isArray(data?.data) ? data.data : []
    results.value = page.value === 1 ? rows : [...results.value, ...rows]
    hasMore.value = Boolean(data?.next_page_url)
  } catch (error) {
    if (!['ERR_CANCELED', 'CanceledError', 'AbortError'].includes(error?.code || error?.name)) {
      console.error('Order search failed', error)
    }
  } finally {
    hasSearched.value = true
    loading.value = false
  }
}

const onScroll = (event) => {
  const el = event.target
  if (el.scrollTop + el.clientHeight >= el.scrollHeight - 24 && hasMore.value && !loading.value) {
    page.value += 1
    fetchResults()
  }
}

const moveActive = (direction) => {
  if (!results.value.length) return
  const last = results.value.length - 1
  activeIndex.value = Math.min(last, Math.max(0, activeIndex.value + direction))
}

const openActiveResult = () => {
  if (activeIndex.value < 0 && results.value.length) {
    openResult(results.value[0])
    return
  }
  const result = results.value[activeIndex.value]
  if (result) openResult(result)
}

const openResult = (result) => {
  if (!result?.url) return
  window.location.href = result.url
}

const clearSearch = () => {
  query.value = ''
  results.value = []
  hasSearched.value = false
  activeIndex.value = -1
  if (abortController) abortController.abort()
}

const closeDropdowns = () => {
  typeOpen.value = false
  results.value = []
  hasSearched.value = false
}

const openResultsIfReady = () => {
  if (query.value.length >= 2 && !results.value.length) {
    resetAndSearch()
  }
}

const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    typeOpen.value = false
    results.value = []
    hasSearched.value = false
  }
}

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'completed') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'cancelled' || value === 'rejected') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  if (value === 'shipped') return 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'
  if (value === 'packed' || value === 'approved') return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
}

onMounted(() => {
  loadSavedType()
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  if (searchTimer) window.clearTimeout(searchTimer)
  if (abortController) abortController.abort()
})
</script>
