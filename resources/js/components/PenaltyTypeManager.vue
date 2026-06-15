<template>
  <div class="p-3 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">System Configuration</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Penalty Configuration</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Create and maintain penalty types with their effective percentages.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-3 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="relative w-full lg:w-80">
          <input
            v-model="search"
            @input="debouncedFetch"
            type="search"
            placeholder="Search penalties..."
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          />
          <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
            <i class="fas fa-search"></i>
          </div>
        </div>

        <button
          @click="openDrawer()"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black dark:bg-white dark:text-slate-900"
        >
          <i class="fas fa-plus"></i> Add Penalty Type
        </button>
      </div>

      <div class="overflow-x-auto p-4">
        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">Penalty</th>
              <th class="px-3 py-3">Type</th>
              <th class="px-3 py-3">Description</th>
              <th class="px-3 py-3">Effective Areas</th>
              <th class="px-3 py-3">Restrictions</th>
              <th class="px-3 py-3">Effective %</th>
              <th class="px-3 py-3">Status</th>
              <th class="px-3 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
            <tr v-if="items.length === 0">
              <td colspan="8" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No penalty types found</td>
            </tr>
            <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4 font-semibold text-slate-900 dark:text-white">{{ item.penalty }}</td>
              <td class="px-3 py-4">
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide"
                  :class="item.trigger_type === 'sales_target' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200' : 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-200'"
                >
                  {{ triggerTypeLabel(item.trigger_type) }}
                </span>
              </td>
              <td class="max-w-md px-3 py-4 text-slate-600 dark:text-slate-300">
                <span class="line-clamp-2">{{ item.description || '-' }}</span>
              </td>
              <td class="px-3 py-4">
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="area in item.effective_areas || []"
                    :key="area"
                    class="rounded-full bg-amber-100 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide text-amber-700 dark:bg-amber-500/20 dark:text-amber-200"
                  >
                    {{ areaLabel(area) }}
                  </span>
                </div>
              </td>
              <td class="max-w-md px-3 py-4 text-xs text-slate-600 dark:text-slate-300">
                <div class="space-y-1">
                  <div v-for="rule in ruleSummary(item)" :key="rule">{{ rule }}</div>
                  <div v-if="ruleSummary(item).length === 0">-</div>
                </div>
              </td>
              <td class="px-3 py-4 font-semibold text-slate-900 dark:text-white">
                {{ item.trigger_type === 'delivery_score' ? formatPercentage(item.effective_percentage) : '-' }}
              </td>
              <td class="px-3 py-4">
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide"
                  :class="item.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'"
                >
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-3 py-4 text-right space-x-3">
                <button @click="openDrawer(item)" class="text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white"><i class="fas fa-edit"></i></button>
                <button @click="openDelete(item)" class="text-rose-500 hover:text-rose-700"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t border-slate-200/70 px-4 py-4 text-sm text-slate-600 dark:border-slate-800/70 dark:text-slate-300">
        <div>
          Showing
          <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.from || 0 }}</span>
          to
          <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.to || 0 }}</span>
          of
          <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.total || 0 }}</span>
        </div>
        <div class="flex items-center gap-2">
          <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">Prev</button>
          <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">Next</button>
        </div>
      </div>
    </div>

    <transition name="fade">
      <div v-if="showDrawer" class="fixed inset-0 z-[1300] flex justify-end bg-slate-900/60 backdrop-blur-sm">
        <div class="flex h-screen w-full max-w-md flex-col overflow-y-auto border-l border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ editingId ? 'Edit Penalty Type' : 'Create Penalty Type' }}</h3>
            <button @click="closeDrawer" class="h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300">✖</button>
          </div>

          <form @submit.prevent="save" class="space-y-5">
            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Penalty</label>
              <input
                v-model="form.penalty"
                type="text"
                required
                placeholder="Example: Late delivery"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Description</label>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="Describe when this penalty applies"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              ></textarea>
            </div>

            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Trigger Type</label>
              <select
                v-model="form.trigger_type"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option value="delivery_score">Delivery Score</option>
                <option value="sales_target">Sales Target</option>
              </select>
            </div>

            <div v-if="form.trigger_type === 'delivery_score'">
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Effective Percentage</label>
              <input
                v-model="form.effective_percentage"
                type="number"
                min="0"
                max="100"
                step="0.01"
                required
                placeholder="0.00"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Effective Areas</label>
              <div class="space-y-2 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-800">
                <label
                  v-for="option in effectiveAreaOptions"
                  :key="option.value"
                  class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300"
                >
                  <input
                    v-model="form.effective_areas"
                    type="checkbox"
                    :value="option.value"
                    class="h-4 w-4 rounded border border-default-medium bg-neutral-secondary-medium text-brand-medium focus:ring-2 focus:ring-brand-soft"
                  />
                  {{ option.label }}
                </label>
              </div>
            </div>

            <div v-if="hasArea('orders')" class="rounded-xl border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-500/20 dark:bg-blue-500/10">
              <h4 class="mb-3 text-sm font-semibold text-blue-800 dark:text-blue-200">Order Restrictions</h4>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Daily Order Limit</label>
              <input
                v-model="form.rules.orders.daily_order_limit"
                type="number"
                min="0"
                step="1"
                required
                placeholder="Example: 10"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div v-if="hasArea('return_charges')" class="rounded-xl border border-rose-100 bg-rose-50/60 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
              <h4 class="mb-3 text-sm font-semibold text-rose-800 dark:text-rose-200">Return Charge Rules</h4>
              <div class="space-y-4">
                <div>
                  <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Charge Type</label>
                  <select
                    v-model="form.rules.return_charges.charge_type"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                  >
                    <option value="fixed">Fixed Amount</option>
                    <option value="percentage">Percentage</option>
                  </select>
                </div>

                <div>
                  <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    {{ form.rules.return_charges.charge_type === 'percentage' ? 'Charge Percentage' : 'Charge Amount' }}
                  </label>
                  <input
                    v-model="form.rules.return_charges.charge_amount"
                    type="number"
                    min="0"
                    :max="form.rules.return_charges.charge_type === 'percentage' ? 100 : undefined"
                    step="0.01"
                    required
                    :placeholder="form.rules.return_charges.charge_type === 'percentage' ? 'Example: 5' : 'Example: 500.00'"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                  />
                </div>
              </div>
            </div>

            <div v-if="hasArea('account')" class="rounded-xl border border-amber-100 bg-amber-50/60 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
              <h4 class="mb-3 text-sm font-semibold text-amber-800 dark:text-amber-200">Account Restrictions</h4>
              <div class="space-y-2">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                  <input
                    v-model="form.rules.account.block_withdrawals"
                    type="checkbox"
                    class="h-4 w-4 rounded border border-default-medium bg-neutral-secondary-medium text-brand-medium focus:ring-2 focus:ring-brand-soft"
                  />
                  Block withdrawals
                </label>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                  <input
                    v-model="form.rules.account.block_order_placing"
                    type="checkbox"
                    class="h-4 w-4 rounded border border-default-medium bg-neutral-secondary-medium text-brand-medium focus:ring-2 focus:ring-brand-soft"
                  />
                  Block order placing
                </label>
              </div>
            </div>

            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border border-default-medium bg-neutral-secondary-medium text-brand-medium focus:ring-2 focus:ring-brand-soft"
              />
              Active penalty type
            </label>

            <button type="submit" :disabled="saving" class="w-full rounded-xl bg-slate-900 py-2.5 text-sm text-white hover:bg-black disabled:opacity-60 dark:bg-white dark:text-slate-900">
              {{ saving ? 'Saving...' : editingId ? 'Update Penalty Type' : 'Create Penalty Type' }}
            </button>
          </form>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showDeleteModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Delete Penalty Type</h3>
          <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">Are you sure you want to delete this penalty type?</p>
          <div class="flex justify-end gap-3">
            <button @click="showDeleteModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button>
            <button @click="remove" class="rounded-xl bg-rose-600 px-4 py-2 text-white hover:bg-rose-700">Delete</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const items = ref([])
const search = ref('')
const saving = ref(false)
const showDrawer = ref(false)
const showDeleteModal = ref(false)
const editingId = ref(null)
const deletingItem = ref(null)
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 })
let searchTimeout = null

const effectiveAreaOptions = [
  { value: 'orders', label: 'Orders' },
  { value: 'return_charges', label: 'Return Charges' },
  { value: 'account', label: 'Account' },
]

const triggerTypeLabel = (value) => {
  return value === 'sales_target' ? 'Sales Target' : 'Delivery Score'
}

const defaultRules = () => ({
  orders: {
    daily_order_limit: '',
  },
  return_charges: {
    charge_type: 'fixed',
    charge_amount: '',
  },
  account: {
    block_withdrawals: false,
    block_order_placing: false,
  },
})

const emptyForm = () => ({
  penalty: '',
  description: '',
  trigger_type: 'delivery_score',
  effective_areas: [],
  rules: defaultRules(),
  effective_percentage: '',
  is_active: true,
})

const form = ref(emptyForm())

const formatPercentage = (value) => {
  const numeric = Number(value || 0)
  return `${numeric.toFixed(2)}%`
}

const hasArea = (area) => form.value.effective_areas.includes(area)

const areaLabel = (value) => {
  return effectiveAreaOptions.find((option) => option.value === value)?.label || value
}

const ruleSummary = (item) => {
  const rules = item.rules || {}
  const summary = []

  if (rules.orders?.daily_order_limit !== undefined && rules.orders?.daily_order_limit !== null) {
    summary.push(`Daily orders: ${rules.orders.daily_order_limit}`)
  }

  if (rules.return_charges?.charge_amount !== undefined && rules.return_charges?.charge_amount !== null) {
    const charge = Number(rules.return_charges.charge_amount || 0).toFixed(2)
    summary.push(rules.return_charges.charge_type === 'percentage' ? `Return charge: ${charge}%` : `Return charge: LKR ${charge}`)
  }

  if (rules.account?.block_withdrawals) {
    summary.push('Withdrawals blocked')
  }

  if (rules.account?.block_order_placing) {
    summary.push('Order placing blocked')
  }

  return summary
}

const fetchItems = async (page = 1) => {
  try {
    const { data } = await axios.get('/api/penalty-types', {
      params: {
        page,
        search: search.value || undefined,
      },
    })
    items.value = data.data || []
    pagination.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
      per_page: data.per_page || 10,
      total: data.total || 0,
      from: data.from || 0,
      to: data.to || 0,
    }
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load penalty types.')
  }
}

const debouncedFetch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchItems(1), 300)
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchItems(page)
}

const openDrawer = (item = null) => {
  if (item) {
    const rules = item.rules || {}
    editingId.value = item.id
    form.value = {
      penalty: item.penalty || '',
      description: item.description || '',
      trigger_type: item.trigger_type || 'delivery_score',
      effective_areas: Array.isArray(item.effective_areas) ? [...item.effective_areas] : [],
      rules: {
        orders: {
          daily_order_limit: rules.orders?.daily_order_limit ?? '',
        },
        return_charges: {
          charge_type: rules.return_charges?.charge_type || 'fixed',
          charge_amount: rules.return_charges?.charge_amount ?? '',
        },
        account: {
          block_withdrawals: !!rules.account?.block_withdrawals,
          block_order_placing: !!rules.account?.block_order_placing,
        },
      },
      effective_percentage: Number(item.effective_percentage || 0).toFixed(2),
      is_active: !!item.is_active,
    }
  } else {
    editingId.value = null
    form.value = emptyForm()
  }
  showDrawer.value = true
}

const closeDrawer = () => {
  showDrawer.value = false
  editingId.value = null
  form.value = emptyForm()
}

const save = async () => {
  saving.value = true
  try {
    const payload = {
      penalty: form.value.penalty,
      description: form.value.description,
      trigger_type: form.value.trigger_type,
      effective_areas: form.value.effective_areas,
      rules: form.value.rules,
      effective_percentage: form.value.trigger_type === 'delivery_score' ? form.value.effective_percentage : 0,
      is_active: !!form.value.is_active,
    }

    if (editingId.value) {
      await axios.put(`/api/penalty-types/${editingId.value}`, payload)
      toast.success('Penalty type updated.')
    } else {
      await axios.post('/api/penalty-types', payload)
      toast.success('Penalty type created.')
    }

    closeDrawer()
    fetchItems(pagination.value.current_page || 1)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to save penalty type.')
  } finally {
    saving.value = false
  }
}

const openDelete = (item) => {
  deletingItem.value = item
  showDeleteModal.value = true
}

const remove = async () => {
  if (!deletingItem.value) return
  try {
    await axios.delete(`/api/penalty-types/${deletingItem.value.id}`)
    showDeleteModal.value = false
    deletingItem.value = null
    toast.success('Penalty type deleted.')
    fetchItems(pagination.value.current_page || 1)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete penalty type.')
  }
}

onMounted(() => fetchItems(1))
</script>
