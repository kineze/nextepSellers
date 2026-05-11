<template>
  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800/60">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="flex flex-wrap items-center gap-2">
        <button
          v-for="option in dateOptions"
          :key="option.value"
          type="button"
          @click="setDatePreset(option.value)"
          :class="[
            'rounded-full px-3 py-1.5 text-xs font-semibold transition',
            state.date_preset === option.value
              ? 'bg-blue-600 text-white'
              : 'bg-slate-200 text-slate-700 hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600'
          ]"
        >
          {{ option.label }}
        </button>

        <div v-if="state.date_preset === 'custom'" class="flex items-center gap-2">
          <input
            v-model="state.date_from"
            type="date"
            class="rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          />
          <span class="text-xs text-slate-500 dark:text-slate-400">to</span>
          <input
            v-model="state.date_to"
            type="date"
            class="rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          />
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <input
          v-model.trim="state.search"
          type="text"
          placeholder="Search order/customer/seller"
          class="w-56 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @keyup.enter="emitNow"
        />

        <div ref="sellerDropdownRef" class="relative">
          <button
            type="button"
            class="flex min-w-52 items-center justify-between gap-2 rounded-xl bg-blue-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-800"
            @click="toggleSeller"
          >
            <span class="truncate">{{ selectedSellerLabel || 'Select Seller' }}</span>
            <i class="fas fa-caret-down"></i>
          </button>

          <div
            v-if="sellerOpen"
            class="absolute right-0 z-30 mt-2 w-72 rounded-xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900"
          >
            <input
              v-model.trim="sellerSearch"
              type="text"
              placeholder="Search seller..."
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            />

            <ul class="mt-2 max-h-56 overflow-y-auto">
              <li>
                <button
                  type="button"
                  class="w-full rounded-lg px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                  @click="selectSeller(null)"
                >
                  All Sellers
                </button>
              </li>
              <li v-for="seller in filteredSellers" :key="seller.id">
                <button
                  type="button"
                  class="w-full rounded-lg px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                  @click="selectSeller(seller.id)"
                >
                  {{ seller.label }}
                </button>
              </li>
              <li v-if="!filteredSellers.length" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                No sellers found
              </li>
            </ul>
          </div>
        </div>

        <button
          type="button"
          class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
          @click="emitNow"
        >
          Apply
        </button>

        <button
          type="button"
          class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="clearFilters"
        >
          Clear
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  contextKey: { type: String, required: true },
})

const emit = defineEmits(['filters-changed'])

const sellerDropdownRef = ref(null)
const sellerOpen = ref(false)
const sellerSearch = ref('')
const sellers = ref([])

const state = reactive({
  search: '',
  date_preset: 'today',
  date_from: '',
  date_to: '',
  seller_id: '',
})

const storageKey = `nextep-filter-${props.contextKey}`

const dateOptions = [
  { value: 'today', label: 'Today' },
  { value: 'yesterday', label: 'Yesterday' },
  { value: 'week', label: 'Week' },
  { value: 'month', label: 'Month' },
  { value: 'year', label: 'Year' },
  { value: 'custom', label: 'Custom' },
]

const toDateInput = (date) => {
  const d = new Date(date)
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

const startOfWeek = (date) => {
  const d = new Date(date)
  const day = d.getDay()
  const diff = day === 0 ? -6 : 1 - day
  d.setDate(d.getDate() + diff)
  d.setHours(0, 0, 0, 0)
  return d
}

const endOfWeek = (date) => {
  const d = startOfWeek(date)
  d.setDate(d.getDate() + 6)
  return d
}

const applyPresetDates = () => {
  const now = new Date()

  if (state.date_preset === 'today') {
    state.date_from = toDateInput(now)
    state.date_to = toDateInput(now)
  } else if (state.date_preset === 'yesterday') {
    const y = new Date(now)
    y.setDate(y.getDate() - 1)
    state.date_from = toDateInput(y)
    state.date_to = toDateInput(y)
  } else if (state.date_preset === 'week') {
    state.date_from = toDateInput(startOfWeek(now))
    state.date_to = toDateInput(endOfWeek(now))
  } else if (state.date_preset === 'month') {
    const first = new Date(now.getFullYear(), now.getMonth(), 1)
    const last = new Date(now.getFullYear(), now.getMonth() + 1, 0)
    state.date_from = toDateInput(first)
    state.date_to = toDateInput(last)
  } else if (state.date_preset === 'year') {
    const first = new Date(now.getFullYear(), 0, 1)
    const last = new Date(now.getFullYear(), 11, 31)
    state.date_from = toDateInput(first)
    state.date_to = toDateInput(last)
  }
}

const setDatePreset = (preset) => {
  state.date_preset = preset
  if (preset !== 'custom') {
    applyPresetDates()
    emitNow()
  }
}

const loadSellers = async () => {
  try {
    const { data } = await axios.get('/api/admin/orders/filter-options')
    sellers.value = Array.isArray(data?.sellers) ? data.sellers : []
  } catch {
    sellers.value = []
  }
}

const filteredSellers = computed(() => {
  const keyword = sellerSearch.value.toLowerCase()
  if (!keyword) return sellers.value
  return sellers.value.filter((seller) => seller.label.toLowerCase().includes(keyword))
})

const selectedSellerLabel = computed(() => {
  if (!state.seller_id) return ''
  const seller = sellers.value.find((row) => String(row.id) === String(state.seller_id))
  return seller?.label || ''
})

const toggleSeller = () => {
  sellerOpen.value = !sellerOpen.value
}

const selectSeller = (id) => {
  state.seller_id = id ? String(id) : ''
  sellerOpen.value = false
  emitNow()
}

const savePreference = () => {
  window.localStorage.setItem(storageKey, JSON.stringify({ ...state }))
}

const loadPreference = () => {
  try {
    const raw = window.localStorage.getItem(storageKey)
    if (!raw) return false

    const parsed = JSON.parse(raw)
    if (!parsed || typeof parsed !== 'object') return false

    state.search = parsed.search || ''
    state.date_preset = parsed.date_preset || 'today'
    state.date_from = parsed.date_from || ''
    state.date_to = parsed.date_to || ''
    state.seller_id = parsed.seller_id || ''

    return true
  } catch {
    return false
  }
}

const emitNow = () => {
  savePreference()
  emit('filters-changed', {
    search: state.search || '',
    date_from: state.date_from || '',
    date_to: state.date_to || '',
    seller_id: state.seller_id ? Number(state.seller_id) : null,
    date_preset: state.date_preset,
  })
}

const clearFilters = () => {
  state.search = ''
  state.seller_id = ''
  sellerSearch.value = ''
  state.date_preset = 'today'
  applyPresetDates()
  emitNow()
}

const onClickOutside = (event) => {
  if (!sellerOpen.value) return
  const el = sellerDropdownRef.value
  if (el && !el.contains(event.target)) {
    sellerOpen.value = false
  }
}

watch(() => state.date_preset, () => {
  if (state.date_preset !== 'custom') {
    applyPresetDates()
  }
})

onMounted(async () => {
  await loadSellers()

  const loaded = loadPreference()
  if (!loaded) {
    applyPresetDates()
  } else if (state.date_preset !== 'custom') {
    applyPresetDates()
  }

  document.addEventListener('click', onClickOutside)
  emitNow()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutside)
})
</script>
