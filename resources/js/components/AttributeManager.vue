<template>
  <div class="p-6 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">System Configuration</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Attribute Manager</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Create and maintain product attributes and values.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-4 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
            <i class="fas fa-sliders"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Attributes</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">String and color-based options</p>
          </div>
        </div>

        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto">
          <div class="relative w-full sm:w-80">
            <input
              v-model="search"
              @input="debounceSearch"
              type="search"
              placeholder="Search attributes..."
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
            <i class="fas fa-plus"></i> New Attribute
          </button>
        </div>
      </div>

      <div class="overflow-x-auto p-4">
        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">Name</th>
              <th class="px-3 py-3">Slug</th>
              <th class="px-3 py-3">Type</th>
              <th class="px-3 py-3">Values</th>
              <th class="px-3 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
            <tr v-if="attributes.length === 0">
              <td colspan="5" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No attributes found</td>
            </tr>
            <tr v-for="item in attributes" :key="item.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4 font-medium text-slate-900 dark:text-white">{{ item.name }}</td>
              <td class="px-3 py-4">{{ item.slug }}</td>
              <td class="px-3 py-4">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide"
                  :class="item.type === 'color' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'">
                  {{ item.type }}
                </span>
              </td>
              <td class="px-3 py-4">
                <button
                  @click="openValuesModal(item)"
                  class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                  View ({{ valueCount(item.values) }})
                </button>
              </td>
              <td class="px-3 py-4 text-right space-x-3">
                <button @click="openDrawer(item)" class="text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white"><i class="fas fa-edit"></i></button>
                <button @click="openDeleteModal(item)" class="text-rose-500 hover:text-rose-700"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200/70 px-4 py-4 text-sm text-slate-600 dark:border-slate-800/70 dark:text-slate-300 sm:flex-row sm:items-center sm:justify-between">
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
        <div class="flex h-screen w-full max-w-xl flex-col border-l border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ editingId ? 'Edit Attribute' : 'Create Attribute' }}</h3>
            <button @click="closeDrawer" class="h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300">✖</button>
          </div>

          <form @submit.prevent="saveAttribute" class="flex h-[calc(100vh-7rem)] flex-col">
            <div class="space-y-5 overflow-y-auto pr-1">
              <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Name</label>
                <input v-model="form.name" type="text" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
              </div>
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Slug (optional)</label>
                <input v-model="form.slug" type="text" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
              </div>
            </div>

              <div>
                <div>
                  <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Type</label>
                  <select v-model="form.type" @change="handleTypeChange" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="string">string</option>
                    <option value="color">color</option>
                  </select>
                </div>
              </div>

              <div>
                <div class="mb-2 flex items-center justify-between">
                  <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Values</label>
                  <button type="button" @click="addValueRow" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900">Add Value</button>
                </div>

                <div v-if="form.type === 'string'" class="max-h-64 space-y-2 overflow-y-auto pr-1">
                  <div v-for="(value, index) in form.values" :key="`string-${index}`" class="flex items-center gap-2">
                    <input v-model="form.values[index]" type="text" placeholder="Enter value" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                    <button type="button" @click="removeValueRow(index)" class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Remove</button>
                  </div>
                </div>

                <div v-else class="max-h-64 space-y-2 overflow-y-auto pr-1">
                  <div v-for="(value, index) in form.values" :key="`color-${index}`" class="grid grid-cols-1 gap-2 md:grid-cols-[120px_1fr_auto]">
                    <input v-model="value.color" type="color" class="h-10 w-full rounded-lg border border-slate-200 bg-white p-1 dark:border-slate-700 dark:bg-slate-800" />
                    <input v-model="value.name" type="text" placeholder="Color name" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                    <button type="button" @click="removeValueRow(index)" class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">Remove</button>
                  </div>
                </div>
              </div>
            </div>
            <button type="submit" class="mt-5 w-full rounded-xl bg-slate-900 py-2.5 text-sm text-white hover:bg-black dark:bg-white dark:text-slate-900">
              {{ editingId ? 'Update Attribute' : 'Save Attribute' }}
            </button>
          </form>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showValuesModal" class="fixed inset-0 z-[1450] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Attribute Values</h3>
              <p class="text-xs text-slate-500 dark:text-slate-300">
                {{ selectedValuesAttribute?.name }} ({{ selectedValuesAttribute?.type }})
              </p>
            </div>
            <button @click="closeValuesModal" class="h-8 w-8 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300">✖</button>
          </div>

          <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
            <p v-if="!selectedValues.length" class="text-sm text-slate-500 dark:text-slate-400">No values available.</p>

            <div
              v-for="(value, index) in selectedValues"
              :key="`value-row-${index}`"
              class="rounded-xl border border-slate-200/70 bg-slate-50/80 px-3 py-2 dark:border-slate-700 dark:bg-slate-800/70"
            >
              <div v-if="selectedValuesAttribute?.type === 'color'" class="flex items-center gap-3">
                <span class="h-5 w-5 rounded-full border border-slate-300 dark:border-slate-600" :style="{ backgroundColor: value?.color || '#000000' }"></span>
                <span class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ value?.name || '-' }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ value?.color || '-' }}</span>
              </div>
              <div v-else class="text-sm font-medium text-slate-800 dark:text-slate-100">
                {{ value }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showDeleteModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Delete Attribute</h3>
          <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">Are you sure you want to delete <span class="font-semibold">{{ deletingItem?.name }}</span>?</p>
          <div class="flex justify-end gap-3">
            <button @click="showDeleteModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button>
            <button @click="deleteAttribute" class="rounded-xl bg-rose-600 px-4 py-2 text-white hover:bg-rose-700">Delete</button>
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

const attributes = ref([])
const search = ref('')
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 })
let searchTimeout = null

const showDrawer = ref(false)
const editingId = ref(null)
const form = ref({
  name: '',
  slug: '',
  type: 'string',
  values: [''],
})

const showDeleteModal = ref(false)
const deletingItem = ref(null)
const showValuesModal = ref(false)
const selectedValuesAttribute = ref(null)
const selectedValues = ref([])

const resetForm = () => {
  editingId.value = null
  form.value = {
    name: '',
    slug: '',
    type: 'string',
    values: [''],
  }
}

const normalizeFormValues = () => {
  if (form.value.type === 'color') {
    return (form.value.values || [])
      .map((item) => ({
        name: String(item?.name || '').trim(),
        color: String(item?.color || '').trim(),
      }))
      .filter((item) => item.name && item.color)
  }

  return (form.value.values || [])
    .map((item) => String(item || '').trim())
    .filter(Boolean)
}

const fetchAttributes = async (page = 1) => {
  try {
    const res = await axios.get('/api/attributes', {
      params: { search: search.value, page, per_page: pagination.value.per_page },
    })
    attributes.value = res.data.attributes || []
    pagination.value = res.data.pagination || pagination.value
  } catch {
    toast.error('Failed to load attributes')
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchAttributes(1), 400)
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchAttributes(page)
}

const openDrawer = (item = null) => {
  if (!item) {
    resetForm()
    showDrawer.value = true
    return
  }

  editingId.value = item.id
  form.value = {
    name: item.name || '',
    slug: item.slug || '',
    type: item.type || 'string',
    values: item.type === 'color'
      ? (Array.isArray(item.values) ? item.values.map((value) => ({
          name: String(value?.name || ''),
          color: String(value?.color || '#000000'),
        })) : [{ name: '', color: '#000000' }])
      : (Array.isArray(item.values) ? item.values.map((value) => String(value || '')) : ['']),
  }

  if (!form.value.values.length) {
    form.value.values = form.value.type === 'color' ? [{ name: '', color: '#000000' }] : ['']
  }

  showDrawer.value = true
}

const closeDrawer = () => {
  showDrawer.value = false
}

const handleTypeChange = () => {
  form.value.values = form.value.type === 'color' ? [{ name: '', color: '#000000' }] : ['']
}

const addValueRow = () => {
  if (form.value.type === 'color') {
    form.value.values.push({ name: '', color: '#000000' })
  } else {
    form.value.values.push('')
  }
}

const removeValueRow = (index) => {
  form.value.values.splice(index, 1)
  if (!form.value.values.length) {
    addValueRow()
  }
}

const saveAttribute = async () => {
  try {
    const payload = {
      name: form.value.name,
      slug: form.value.slug || null,
      type: form.value.type,
      values: normalizeFormValues(),
    }

    if (!payload.values.length) {
      toast.error('Please add at least one valid value.')
      return
    }

    if (editingId.value) {
      await axios.put(`/api/attributes/${editingId.value}`, payload)
      toast.success('Attribute updated successfully')
    } else {
      await axios.post('/api/attributes', payload)
      toast.success('Attribute created successfully')
    }

    closeDrawer()
    fetchAttributes(pagination.value.current_page)
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error(err.response?.data?.message || 'Failed to save attribute')
    }
  }
}

const openDeleteModal = (item) => {
  deletingItem.value = item
  showDeleteModal.value = true
}

const openValuesModal = (item) => {
  selectedValuesAttribute.value = item
  selectedValues.value = Array.isArray(item?.values) ? item.values : []
  showValuesModal.value = true
}

const closeValuesModal = () => {
  showValuesModal.value = false
  selectedValuesAttribute.value = null
  selectedValues.value = []
}

const deleteAttribute = async () => {
  if (!deletingItem.value) return

  try {
    await axios.delete(`/api/attributes/${deletingItem.value.id}`)
    toast.success('Attribute deleted successfully')
    showDeleteModal.value = false
    deletingItem.value = null
    fetchAttributes(pagination.value.current_page)
  } catch {
    toast.error('Failed to delete attribute')
  }
}

const valueCount = (values) => {
  if (!Array.isArray(values)) return 0
  return values.length
}

onMounted(() => {
  fetchAttributes(1)
})
</script>
