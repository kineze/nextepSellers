<template>
  <div class="p-6 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Levels</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Level Manager</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Create and manage level rules by points.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-4 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
            <i class="fas fa-layer-group"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Level Settings</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">Define level number, points, and description</p>
          </div>
        </div>

        <form class="w-full lg:w-auto" @submit.prevent>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative w-full sm:w-80">
              <input
                v-model="search"
                @input="debounceSearch"
                type="search"
                placeholder="Search level no, name, points..."
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              />
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                <i class="fas fa-search"></i>
              </div>
            </div>

            <button
              @click="openDrawer"
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black dark:bg-white dark:text-slate-900"
            >
              <i class="fas fa-plus"></i> New Level
            </button>
          </div>
        </form>
      </div>

      <div class="p-4 overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">Level No</th>
              <th class="px-3 py-3">Icon</th>
              <th class="px-3 py-3">Level Name</th>
              <th class="px-3 py-3">Points</th>
              <th class="px-3 py-3">Description</th>
              <th class="px-3 py-3">Default</th>
              <th class="px-3 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
            <tr v-if="levels.length === 0">
              <td colspan="7" class="text-center py-6 text-slate-500 dark:text-slate-400">No levels found</td>
            </tr>

            <tr v-for="level in levels" :key="level.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4 font-medium text-slate-900 dark:text-white">{{ level.level_no }}</td>
              <td class="px-3 py-4">
                <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                  <img v-if="level.icon_url" :src="level.icon_url" :alt="`${level.level_name} icon`" class="aspect-square h-full w-full object-cover" />
                  <i v-else class="fas fa-medal text-slate-400"></i>
                </div>
              </td>
              <td class="px-3 py-4">{{ level.level_name }}</td>
              <td class="px-3 py-4">{{ level.points }}</td>
              <td class="px-3 py-4">{{ level.description || '-' }}</td>
              <td class="px-3 py-4">
                <button
                  v-if="!level.is_default"
                  @click="openDefaultConfirm(level)"
                  class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-black dark:bg-white dark:text-slate-900"
                  :disabled="settingDefaultId === level.id"
                >
                  {{ settingDefaultId === level.id ? 'Setting...' : 'Make Default' }}
                </button>
                <span
                  v-else
                  class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200"
                >
                  Default
                </span>
              </td>
              <td class="px-3 py-4 text-right space-x-3">
                <button @click="editLevel(level)" class="btn-icon text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="openDeleteModal(level.id)" class="btn-icon text-rose-500 hover:text-rose-600">
                  <i class="fas fa-trash-alt"></i>
                </button>
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
          <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">Prev</button>
          <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">Next</button>
        </div>
      </div>
    </div>

    <transition name="fade">
      <div v-if="showDrawer" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex justify-end z-[1300]">
        <div class="bg-white/95 dark:bg-slate-900/95 w-full max-w-md p-6 shadow-2xl overflow-y-auto border-l border-slate-200/70 dark:border-slate-800/70">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ editingId ? 'Edit Level' : 'Create Level' }}</h3>
            <button @click="closeDrawer" class="h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300">✖</button>
          </div>

          <form @submit.prevent="saveLevel" class="space-y-5">
            <div class="relative z-0 w-full group">
              <input type="number" min="1" v-model="form.level_no" required class="floating-input peer" placeholder=" " />
              <label class="floating-label">Level No</label>
            </div>

            <div class="relative z-0 w-full group">
              <input type="text" v-model="form.level_name" required class="floating-input peer" placeholder=" " />
              <label class="floating-label">Level Name</label>
            </div>

            <div class="relative z-0 w-full group">
              <input type="number" min="0" v-model="form.points" required class="floating-input peer" placeholder=" " />
              <label class="floating-label">Points</label>
            </div>

            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Description (Optional)</label>
              <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
            </div>

            <div>
              <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">Level Icon</label>
              <label class="group flex cursor-pointer items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 transition hover:border-slate-500 hover:bg-white dark:border-slate-700 dark:bg-slate-800/70 dark:hover:border-slate-500 dark:hover:bg-slate-800">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                  <img v-if="iconPreview" :src="iconPreview" alt="Level icon preview" class="aspect-square h-full w-full object-cover" />
                  <i v-else class="fas fa-image text-lg text-slate-400"></i>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-bold text-slate-900 dark:text-white">Upload icon</p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">PNG, JPG, WEBP, or SVG. Square icons work best.</p>
                  <p v-if="form.icon" class="mt-2 truncate text-xs font-semibold text-slate-700 dark:text-slate-200">{{ form.icon.name }}</p>
                </div>
                <span class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white dark:bg-white dark:text-slate-900">
                  Choose
                </span>
                <input type="file" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="hidden" @change="onIconChange" />
              </label>
            </div>

            <button type="submit" class="w-full py-2.5 bg-slate-900 text-sm text-white rounded-xl hover:bg-black dark:bg-white dark:text-slate-900">
              {{ editingId ? 'Update Level' : 'Save Level' }}
            </button>
          </form>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showDeleteModal" class="fixed inset-0 z-[1500] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white/95 dark:bg-slate-900/95 p-6 rounded-2xl w-full max-w-md shadow-2xl border border-slate-200/70 dark:border-slate-800/70">
          <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white">Confirm Delete</h3>
          <p class="text-slate-600 dark:text-slate-300 mb-6">Are you sure you want to delete this level?</p>
          <div class="flex justify-end space-x-3">
            <button @click="showDeleteModal = false" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button>
            <button @click="deleteLevel" class="px-4 py-2 rounded-xl bg-rose-600 text-white hover:bg-rose-700">Delete</button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showDefaultConfirmModal" class="fixed inset-0 z-[1500] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white/95 dark:bg-slate-900/95 p-6 rounded-2xl w-full max-w-md shadow-2xl border border-slate-200/70 dark:border-slate-800/70">
          <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white">Set Default Level</h3>
          <p class="text-slate-600 dark:text-slate-300 mb-6">
            Are you sure you want to make
            <span class="font-semibold text-slate-900 dark:text-white">
              {{ selectedDefaultLevel ? `${selectedDefaultLevel.level_name} (Level ${selectedDefaultLevel.level_no})` : 'this level' }}
            </span>
            the default level?
          </p>
          <div class="flex justify-end space-x-3">
            <button @click="closeDefaultConfirm" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button>
            <button @click="confirmSetDefault" class="px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-black dark:bg-white dark:text-slate-900">
              Confirm
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const levels = ref([])
const search = ref('')
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 })
const settingDefaultId = ref(null)
const showDefaultConfirmModal = ref(false)
const selectedDefaultLevel = ref(null)

const showDrawer = ref(false)
const blankForm = () => ({ level_no: '', level_name: '', points: '', description: '', icon: null })
const form = ref(blankForm())
const iconPreview = ref('')
const editingId = ref(null)

const showDeleteModal = ref(false)
const deletingId = ref(null)

let searchTimeout = null

const fetchLevels = async (page = 1) => {
  try {
    const res = await axios.get('/api/levels', {
      params: { search: search.value, page, per_page: pagination.value.per_page },
    })
    levels.value = res.data.levels || []
    pagination.value = res.data.pagination || pagination.value
  } catch {
    toast.error('Failed to load levels')
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchLevels(1), 400)
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchLevels(page)
}

const openDrawer = () => {
  editingId.value = null
  form.value = blankForm()
  iconPreview.value = ''
  showDrawer.value = true
}

const closeDrawer = () => {
  showDrawer.value = false
}

const editLevel = (level) => {
  editingId.value = level.id
  form.value = {
    level_no: level.level_no,
    level_name: level.level_name || '',
    points: level.points,
    description: level.description || '',
    icon: null,
  }
  iconPreview.value = level.icon_url || ''
  showDrawer.value = true
}

const onIconChange = (event) => {
  const file = event.target.files?.[0] || null
  form.value.icon = file
  iconPreview.value = file ? URL.createObjectURL(file) : ''
}

const buildPayload = () => {
  const payload = new FormData()
  payload.append('level_no', form.value.level_no)
  payload.append('level_name', form.value.level_name)
  payload.append('points', form.value.points)
  payload.append('description', form.value.description || '')

  if (form.value.icon) {
    payload.append('icon', form.value.icon)
  }

  return payload
}

const saveLevel = async () => {
  try {
    const payload = buildPayload()

    if (editingId.value) {
      payload.append('_method', 'PUT')
      await axios.post(`/api/levels/${editingId.value}`, payload)
      toast.success('Level updated successfully')
    } else {
      await axios.post('/api/levels', payload)
      toast.success('Level created successfully')
    }

    closeDrawer()
    fetchLevels(pagination.value.current_page)
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error(err.response?.data?.message || 'Failed to save level')
    }
  }
}

const openDeleteModal = (id) => {
  deletingId.value = id
  showDeleteModal.value = true
}

const deleteLevel = async () => {
  try {
    await axios.delete(`/api/levels/${deletingId.value}`)
    toast.success('Level deleted successfully')
    showDeleteModal.value = false
    fetchLevels(pagination.value.current_page)
  } catch {
    toast.error('Failed to delete level')
  }
}

const openDefaultConfirm = (level) => {
  selectedDefaultLevel.value = level
  showDefaultConfirmModal.value = true
}

const closeDefaultConfirm = () => {
  showDefaultConfirmModal.value = false
  selectedDefaultLevel.value = null
}

const confirmSetDefault = async () => {
  if (!selectedDefaultLevel.value) return

  try {
    settingDefaultId.value = selectedDefaultLevel.value.id
    const res = await axios.post(`/api/levels/${selectedDefaultLevel.value.id}/toggle-default`, {
      is_default: true,
    })
    toast.success(res.data.message || 'Default level updated successfully')
    closeDefaultConfirm()
    fetchLevels(pagination.value.current_page)
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to update default level')
  } finally {
    settingDefaultId.value = null
  }
}

onMounted(() => {
  fetchLevels(1)
})
</script>

<style scoped>
.floating-input {
  @apply block py-2.5 px-0 w-full text-sm bg-transparent border-0 border-b-2 border-slate-300 focus:outline-none focus:ring-0 focus:border-slate-600 dark:text-white dark:border-slate-600;
}
.floating-label {
  @apply absolute text-sm text-slate-500 dark:text-slate-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-slate-600 peer-focus:dark:text-slate-300 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6;
}
.btn-icon {
  @apply inline-flex h-8 w-8 items-center justify-center rounded-lg transition;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
