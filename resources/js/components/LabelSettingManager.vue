<template>
  <div class="p-3 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">System Configuration</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Label Settings</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Manage the sender name and phone details printed on labels.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-3 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="relative w-full lg:w-80">
          <input
            v-model="search"
            @input="debouncedFetch"
            type="search"
            placeholder="Search by name or phone..."
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
          <i class="fas fa-plus"></i> Add Label Setting
        </button>
      </div>

      <div class="overflow-x-auto p-4">
        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">Name</th>
              <th class="px-3 py-3">Phone</th>
              <th class="px-3 py-3">Active</th>
              <th class="px-3 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
            <tr v-if="items.length === 0">
              <td colspan="4" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No label settings found</td>
            </tr>
            <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4 font-semibold text-slate-900 dark:text-white">
                {{ item.name }}
              </td>
              <td class="px-3 py-4 text-slate-600 dark:text-slate-300">
                {{ item.phone }}
              </td>
              <td class="px-3 py-4">
                <button
                  type="button"
                  :disabled="activeTogglingId === item.id"
                  @click="toggleActive(item)"
                  class="inline-flex h-7 w-16 items-center rounded-full p-1 transition disabled:opacity-60"
                  :class="item.is_active ? 'justify-end bg-emerald-500' : 'justify-start bg-slate-300 dark:bg-slate-700'"
                  :aria-label="item.is_active ? 'Deactivate label setting' : 'Activate label setting'"
                >
                  <span class="h-5 w-5 rounded-full bg-white shadow-sm"></span>
                </button>
                <span
                  class="ml-2 align-middle text-[0.7rem] font-semibold uppercase tracking-wide"
                  :class="item.is_active ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400'"
                >
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-3 py-4 text-right space-x-3">
                <button @click="openDrawer(item)" class="text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="openDelete(item)" class="text-rose-500 hover:text-rose-700">
                  <i class="fas fa-trash-alt"></i>
                </button>
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
        <div class="flex h-screen w-full max-w-md flex-col border-l border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ editingId ? 'Edit Label Setting' : 'Create Label Setting' }}</h3>
            <button @click="closeDrawer" class="h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300">X</button>
          </div>

          <form @submit.prevent="save" class="space-y-5">
            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="Sender name"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Phone</label>
              <input
                v-model="form.phone"
                type="text"
                required
                placeholder="Phone number"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
            </div>

            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border border-default-medium bg-neutral-secondary-medium text-brand-medium focus:ring-2 focus:ring-brand-soft"
              />
              Set as active label setting
            </label>

            <button type="submit" :disabled="saving" class="w-full rounded-xl bg-slate-900 py-2.5 text-sm text-white hover:bg-black disabled:opacity-60 dark:bg-white dark:text-slate-900">
              {{ saving ? 'Saving...' : editingId ? 'Update Label Setting' : 'Create Label Setting' }}
            </button>
          </form>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showDeleteModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Delete Label Setting</h3>
          <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">Are you sure you want to delete this label setting?</p>
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
const activeTogglingId = ref(null)
const showDrawer = ref(false)
const showDeleteModal = ref(false)
const editingId = ref(null)
const deletingItem = ref(null)
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 })
const form = ref({ name: '', phone: '', is_active: false })
let searchTimeout = null

const fetchItems = async (page = 1) => {
  try {
    const { data } = await axios.get('/api/label-settings', {
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
    toast.error(error?.response?.data?.message || 'Failed to load label settings.')
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
    editingId.value = item.id
    form.value = {
      name: item.name || '',
      phone: item.phone || '',
      is_active: !!item.is_active,
    }
  } else {
    editingId.value = null
    form.value = { name: '', phone: '', is_active: false }
  }
  showDrawer.value = true
}

const closeDrawer = () => {
  showDrawer.value = false
  editingId.value = null
  form.value = { name: '', phone: '', is_active: false }
}

const save = async () => {
  saving.value = true
  try {
    const payload = {
      name: form.value.name,
      phone: form.value.phone,
      is_active: !!form.value.is_active,
    }

    if (editingId.value) {
      await axios.put(`/api/label-settings/${editingId.value}`, payload)
      toast.success('Label setting updated.')
    } else {
      await axios.post('/api/label-settings', payload)
      toast.success('Label setting created.')
    }

    closeDrawer()
    fetchItems(pagination.value.current_page || 1)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to save label setting.')
  } finally {
    saving.value = false
  }
}

const toggleActive = async (item) => {
  activeTogglingId.value = item.id
  try {
    await axios.post(`/api/label-settings/${item.id}/toggle-active`)
    toast.success(item.is_active ? 'Label setting deactivated.' : 'Label setting activated.')
    fetchItems(pagination.value.current_page || 1)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to update active label setting.')
  } finally {
    activeTogglingId.value = null
  }
}

const openDelete = (item) => {
  deletingItem.value = item
  showDeleteModal.value = true
}

const remove = async () => {
  if (!deletingItem.value) return
  try {
    await axios.delete(`/api/label-settings/${deletingItem.value.id}`)
    showDeleteModal.value = false
    deletingItem.value = null
    toast.success('Label setting deleted.')
    fetchItems(pagination.value.current_page || 1)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete label setting.')
  }
}

onMounted(() => fetchItems(1))
</script>
