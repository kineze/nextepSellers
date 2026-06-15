<template>
  <div class="w-full p-3">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">System Configuration</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Sales Targets</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Maintain yearly, quarterly, and monthly targets with linked penalties.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-3 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="grid gap-3 sm:grid-cols-[180px_1fr] sm:items-end">
          <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Target Year</label>
            <input
              v-model.number="year"
              type="number"
              min="2000"
              max="2100"
              class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              @change="fetchTargets"
            />
          </div>
          <div class="text-xs text-slate-500 dark:text-slate-400">
            Records are generated from the selected year and fiscal quarter setup. Once saved, rows remain available for update.
          </div>
        </div>

        <button
          type="button"
          :disabled="loading || saving"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-900"
          @click="save"
        >
          <i class="fas fa-save"></i>
          {{ saving ? 'Saving...' : 'Save Targets' }}
        </button>
      </div>

      <div v-if="loading" class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
        Loading sales targets...
      </div>

      <div v-else class="space-y-5 p-4">
        <section
          v-for="frequency in frequencies"
          :key="frequency.value"
          class="rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800"
        >
          <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ frequency.label }}</h3>
          </div>

          <div class="p-4">
            <div class="overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
              <div class="flex items-center justify-between bg-slate-50 px-3 py-2 dark:bg-slate-900/70">
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ frequency.label }} Targets</h4>
                <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                  One type per period
                </span>
              </div>

              <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                  <thead class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    <tr>
                      <th class="w-48 px-3 py-2">Period</th>
                      <th class="w-44 px-3 py-2">Target Type</th>
                      <th class="w-40 px-3 py-2">Target Value</th>
                      <th class="px-3 py-2">Penalties</th>
                      <th class="w-24 px-3 py-2">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-200 text-slate-700 dark:divide-slate-700 dark:text-slate-200">
                    <tr v-for="row in tableRows(frequency.value)" :key="rowKey(row)" class="align-top">
                      <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">
                        {{ row.period_label }}
                      </td>
                      <td class="px-3 py-3">
                        <select
                          v-model="row.target_type"
                          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        >
                          <option v-for="type in targetTypes" :key="type.value" :value="type.value">
                            {{ type.label }}
                          </option>
                        </select>
                      </td>
                      <td class="px-3 py-3">
                        <input
                          v-model="row.target_value"
                          type="number"
                          min="0"
                          step="0.01"
                          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                      </td>
                      <td class="px-3 py-3">
                        <div class="relative" :data-picker="rowKey(row)">
                          <button
                            type="button"
                            class="flex min-h-10 w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            @click.stop="togglePenaltyPicker(row, $event)"
                          >
                            <span class="line-clamp-1">{{ selectedPenaltyText(row) }}</span>
                            <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                          </button>
                        </div>
                      </td>
                      <td class="px-3 py-3">
                        <span
                          class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide"
                          :class="row.is_created ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200'"
                        >
                          {{ row.is_created ? 'Saved' : 'New' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <teleport to="body">
    <div
      v-if="activePickerRow"
      :data-picker="activePickerKey"
      :style="pickerStyle"
      class="fixed z-[2200] rounded-xl border border-slate-200 bg-white p-3 shadow-2xl dark:border-slate-700 dark:bg-slate-900"
      @click.stop
    >
      <div class="relative mb-2">
        <input
          v-model="penaltySearch"
          type="search"
          placeholder="Search penalties..."
          class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 pr-9 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          @input="debouncedFetchPenalties"
        />
        <i class="fas fa-search pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
      </div>

      <div class="max-h-56 space-y-1 overflow-y-auto">
        <label
          v-for="penalty in penaltyOptions"
          :key="penalty.id"
          class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
        >
          <input
            :checked="activePickerRow.penalty_type_ids.includes(penalty.id)"
            type="checkbox"
            class="h-4 w-4 rounded border border-default-medium bg-neutral-secondary-medium text-brand-medium focus:ring-2 focus:ring-brand-soft"
            @change="togglePenalty(activePickerRow, penalty.id)"
          />
          <span class="flex-1">{{ penalty.penalty }}</span>
          <span
            class="rounded-full px-2 py-0.5 text-[0.6rem] font-semibold uppercase tracking-wide"
            :class="penalty.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
          >
            {{ penalty.is_active ? 'Active' : 'Inactive' }}
          </span>
        </label>
        <div v-if="penaltyOptions.length === 0" class="px-2 py-4 text-center text-sm text-slate-500 dark:text-slate-400">
          No penalties found
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const currentYear = new Date().getFullYear()
const year = ref(currentYear)
const loading = ref(false)
const saving = ref(false)
const targets = ref({})
const penaltyOptions = ref([])
const penaltySearch = ref('')
const activePickerKey = ref('')
const activePickerRow = ref(null)
const pickerStyle = ref({})
let penaltySearchTimeout = null

const frequencies = [
  { value: 'yearly', label: 'Yearly' },
  { value: 'quarterly', label: 'Quarterly' },
  { value: 'monthly', label: 'Monthly' },
]

const targetTypes = [
  { value: 'qty', label: 'Quantity' },
  { value: 'sales_amount', label: 'Sales Amount' },
]

const rowKey = (row) => `${row.frequency}:${row.period_number}`

const tableRows = (frequency) => targets.value?.[frequency] || []

const fetchTargets = async () => {
  loading.value = true
  activePickerKey.value = ''
  try {
    const { data } = await axios.get('/api/sales-targets', {
      params: { year: year.value || currentYear },
    })
    year.value = data.data?.year || year.value
    targets.value = data.data?.targets || {}
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load sales targets.')
  } finally {
    loading.value = false
  }
}

const fetchPenaltyOptions = async () => {
  try {
    const { data } = await axios.get('/api/sales-targets/penalty-options', {
      params: { search: penaltySearch.value || undefined },
    })
    penaltyOptions.value = data.data || []
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load penalties.')
  }
}

const debouncedFetchPenalties = () => {
  clearTimeout(penaltySearchTimeout)
  penaltySearchTimeout = setTimeout(fetchPenaltyOptions, 250)
}

const allRows = () => {
  return frequencies.flatMap((frequency) => {
    return tableRows(frequency.value)
  })
}

const save = async () => {
  saving.value = true
  try {
    await axios.post('/api/sales-targets', {
      year: year.value,
      targets: allRows().map((row) => ({
        frequency: row.frequency,
        target_type: row.target_type,
        period_number: row.period_number,
        target_value: row.target_value || 0,
        penalty_type_ids: row.penalty_type_ids || [],
      })),
    })

    toast.success('Sales targets saved.')
    fetchTargets()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to save sales targets.')
  } finally {
    saving.value = false
  }
}

const selectedPenaltyText = (row) => {
  const selected = (row.penalty_type_ids || [])
    .map((id) => penaltyOptions.value.find((penalty) => penalty.id === id)?.penalty || row.penalty_types?.find((penalty) => penalty.id === id)?.penalty)
    .filter(Boolean)

  if (selected.length === 0) return 'Select penalties'
  if (selected.length <= 2) return selected.join(', ')
  return `${selected.slice(0, 2).join(', ')} +${selected.length - 2}`
}

const closePenaltyPicker = () => {
  activePickerKey.value = ''
  activePickerRow.value = null
  pickerStyle.value = {}
}

const togglePenaltyPicker = (row, event) => {
  const key = rowKey(row)
  if (activePickerKey.value === key) {
    closePenaltyPicker()
    return
  }

  const rect = event.currentTarget.getBoundingClientRect()
  const dropdownHeight = 330
  const top = rect.top > dropdownHeight + 16
    ? rect.top - dropdownHeight - 8
    : rect.bottom + 8

  activePickerKey.value = key
  activePickerRow.value = row
  pickerStyle.value = {
    left: `${Math.max(12, rect.left)}px`,
    top: `${Math.max(12, top)}px`,
    width: `${Math.max(rect.width, 320)}px`,
  }
  penaltySearch.value = ''
  fetchPenaltyOptions()
}

const togglePenalty = (row, penaltyId) => {
  const ids = row.penalty_type_ids || []
  row.penalty_type_ids = ids.includes(penaltyId)
    ? ids.filter((id) => id !== penaltyId)
    : [...ids, penaltyId]
}

const onDocumentClick = (event) => {
  if (!activePickerKey.value) return
  if (event.target.closest(`[data-picker="${activePickerKey.value}"]`)) return
  closePenaltyPicker()
}

onMounted(() => {
  fetchTargets()
  fetchPenaltyOptions()
  document.addEventListener('click', onDocumentClick)
})

onBeforeUnmount(() => {
  clearTimeout(penaltySearchTimeout)
  document.removeEventListener('click', onDocumentClick)
})
</script>
