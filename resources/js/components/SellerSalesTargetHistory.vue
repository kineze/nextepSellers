<template>
  <div class="w-full p-3">
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <div>
        <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Seller Performance</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Sales Target History</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Review completed target periods and any penalties applied.</p>
      </div>
      <div class="w-full md:w-40">
        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Year</label>
        <input
          v-model.number="year"
          type="number"
          min="2000"
          max="2100"
          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
          @change="fetchItems"
        />
      </div>
    </div>

    <div class="grid gap-3 md:grid-cols-4">
      <div v-for="card in summaryCards" :key="card.label" class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ card.label }}</p>
        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ card.value }}</p>
      </div>
    </div>

    <div class="mt-5 rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div v-if="loading" class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">Loading target history...</div>

      <div v-else class="overflow-x-auto p-4">
        <table class="w-full min-w-[980px] text-left text-sm text-slate-700 dark:text-slate-200">
          <thead class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">Period</th>
              <th class="px-3 py-3">Frequency</th>
              <th class="px-3 py-3">Type</th>
              <th class="px-3 py-3 text-right">Target</th>
              <th class="px-3 py-3 text-right">Actual</th>
              <th class="px-3 py-3 text-right">Achievement</th>
              <th class="px-3 py-3">Status</th>
              <th class="px-3 py-3">Penalties</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            <tr v-if="items.length === 0">
              <td colspan="8" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No assessed target periods found.</td>
            </tr>
            <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4">
                <p class="font-semibold text-slate-900 dark:text-white">{{ item.period_label }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ formatDate(item.period_start_date) }} - {{ formatDate(item.period_end_date) }}</p>
              </td>
              <td class="px-3 py-4 capitalize">{{ item.frequency }}</td>
              <td class="px-3 py-4">{{ targetTypeLabel(item.target_type) }}</td>
              <td class="px-3 py-4 text-right font-semibold">{{ formatValue(item.target_value, item.target_type) }}</td>
              <td class="px-3 py-4 text-right font-semibold">{{ formatValue(item.actual_value, item.target_type) }}</td>
              <td class="px-3 py-4 text-right">{{ Number(item.achievement_percentage || 0).toFixed(2) }}%</td>
              <td class="px-3 py-4">
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide"
                  :class="item.is_achieved ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'"
                >
                  {{ item.is_achieved ? 'Achieved' : 'Missed' }}
                </span>
              </td>
              <td class="px-3 py-4">
                <div v-if="item.penalties.length" class="space-y-1">
                  <div v-for="penalty in item.penalties" :key="penalty.id" class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-200">
                    <span class="font-semibold">{{ penalty.penalty || 'Penalty' }}</span>
                    <span v-if="penalty.applied_at"> · {{ formatDateTime(penalty.applied_at) }}</span>
                  </div>
                </div>
                <span v-else class="text-slate-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const year = ref(new Date().getFullYear())
const loading = ref(false)
const items = ref([])
const summary = reactive({
  total: 0,
  achieved: 0,
  missed: 0,
  penalties: 0,
})

const summaryCards = computed(() => [
  { label: 'Assessed', value: summary.total },
  { label: 'Achieved', value: summary.achieved },
  { label: 'Missed', value: summary.missed },
  { label: 'Penalties', value: summary.penalties },
])

const fetchItems = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/seller/sales-targets', {
      params: { year: year.value || undefined },
    })
    items.value = data.data || []
    Object.assign(summary, data.summary || {})
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load sales target history.')
  } finally {
    loading.value = false
  }
}

const targetTypeLabel = (type) => type === 'sales_amount' ? 'Sales Amount' : 'Quantity'

const formatValue = (value, type) => {
  const numeric = Number(value || 0)
  if (type === 'sales_amount') {
    return `LKR ${numeric.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
  }
  return numeric.toLocaleString(undefined, { maximumFractionDigits: 0 })
}

const formatDate = (value) => value ? new Date(value).toLocaleDateString() : '-'
const formatDateTime = (value) => value ? new Date(value).toLocaleString() : '-'

onMounted(fetchItems)
</script>
