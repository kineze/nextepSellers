<template>
  <div class="role-page p-6 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Settings</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Roles & Permissions</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
        Define access levels and toggle permissions per role.
      </p>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
      <!-- Left Column: Roles -->
      <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
            <i class="fas fa-user-shield"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Roles</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">Create and manage role types</p>
          </div>
        </div>

        <!-- Input + Add -->
        <div class="mt-5 flex items-center gap-2">
          <div class="relative w-full">
            <input
              type="text"
              id="role_name"
              v-model="roleForm.name"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-slate-400"
              placeholder="Create role..."
            />
          </div>
          <button
            @click="saveRole"
            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black dark:bg-white dark:text-slate-900"
          >
            Create
          </button>
        </div>

        <!-- Role Radio List -->
        <ul class="mt-5 grid w-full gap-3">
          <li v-for="role in roles" :key="role.id">
            <input
              type="radio"
              :id="'role-' + role.id"
              name="role"
              :value="role.id"
              v-model="selectedRoleId"
              class="hidden peer"
              @change="selectRole(role)"
            />
            <label
              :for="'role-' + role.id"
              class="role-option flex items-center justify-between w-full rounded-xl border border-transparent bg-white/70 px-4 py-3 text-slate-600 cursor-pointer
                     dark:bg-slate-800/70 dark:text-slate-300
                     hover:bg-slate-50 dark:hover:bg-slate-700 transition"
            >
              <div class=" outline-none border-none ">
                <div class="text-sm font-semibold capitalize text-slate-900 dark:text-white">{{ role.name }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">System Role</div>
              </div>
              <span class="text-xs text-slate-400">Select</span>
            </label>
          </li>
        </ul>

        <!-- Delete Role -->
        <div v-if="selectedRole" class="mt-5">
          <button
            @click="confirmDelete(selectedRole)"
            class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-rose-700"
          >
            Delete "{{ selectedRole.name }}"
          </button>
        </div>
      </div>

      <!-- Right Column: Permissions -->
      <div v-if="selectedRole" class="lg:col-span-2 rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Permissions</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">Role: {{ selectedRole.name }}</p>
          </div>
          <div class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Access</div>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="perm in permissions"
            :key="perm.id"
            class="perm-card flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
          >
            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">
              {{ perm.name }}
            </span>

            <label class="inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                class="sr-only peer"
                :value="perm.name"
                v-model="permissionState[perm.name]"
                @change="togglePermission(perm)"
              />
              <div
                class="relative w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 transition-all duration-300
                       after:content-[''] after:absolute after:top-0.5 after:start-[2px]
                       after:w-5 after:h-5 after:bg-white after:rounded-full after:shadow-md after:transition-all
                       peer-focus:ring-4 peer-focus:ring-slate-300 dark:peer-focus:ring-slate-800
                       peer-checked:bg-slate-900 dark:peer-checked:bg-white
                       peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full"
              ></div>
            </label>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="lg:col-span-2 rounded-2xl border border-dashed border-slate-300/60 bg-white/60 p-6 text-center text-slate-500 dark:border-slate-700/60 dark:bg-slate-900/40 dark:text-slate-300">
        <p class="text-sm">Select a role to view permissions.</p>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
      <div class="bg-white/95 dark:bg-slate-900/95 p-6 rounded-2xl shadow-2xl w-80 text-center border border-slate-200/70 dark:border-slate-800/70">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Confirm Delete</h3>
        <p class="text-slate-500 dark:text-slate-300 mb-4">
          Are you sure you want to delete <b>{{ roleToDelete?.name }}</b>?
        </p>
        <div class="flex justify-center gap-3">
          <button @click="deleteRole" class="rounded-xl bg-rose-600 px-4 py-2 text-white hover:bg-rose-700">Delete</button>
          <button
            @click="showDeleteModal = false"
            class="rounded-xl border border-slate-200 px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const roles = ref([])
const permissions = ref([])
const roleForm = ref({ name: '' })
const selectedRole = ref(null)
const selectedRoleId = ref(null)
const permissionState = ref({})
const showDeleteModal = ref(false)
const roleToDelete = ref(null)

const loadRoles = async () => {
  const res = await axios.get('/api/roles')
  roles.value = res.data
}

const loadPermissions = async () => {
  const res = await axios.get('/api/permissions')
  permissions.value = res.data
}

// Create Role
const saveRole = async () => {
  if (!roleForm.value.name) return toast.error('Role name required')
  try {
    await axios.post('/api/roles', roleForm.value)
    toast.success('Role created successfully')
    roleForm.value.name = ''
    await loadRoles()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Error creating role')
  }
}

// Select Role + load permissions state
const selectRole = async (role) => {
  const res = await axios.get(`/api/roles/${role.id}`)
  selectedRole.value = res.data.role
  const assigned = res.data.permissions
  permissionState.value = {}
  permissions.value.forEach((p) => {
    permissionState.value[p.name] = assigned.includes(p.name)
  })
}

// Toggle Permission and Auto Sync
const togglePermission = async (perm) => {
  if (!selectedRole.value) return
  const permName = perm.name
  const active = permissionState.value[permName]

  const assigned = Object.keys(permissionState.value).filter((p) => permissionState.value[p])

  try {
    await axios.post(`/api/roles/${selectedRole.value.id}/sync`, { permissions: assigned })
    toast.success(`${permName} ${active ? 'granted' : 'revoked'}`)
  } catch {
    toast.error('Error syncing permission')
  }
}

// Delete Role
const confirmDelete = (role) => {
  roleToDelete.value = role
  showDeleteModal.value = true
}
const deleteRole = async () => {
  try {
    await axios.delete(`/api/roles/${roleToDelete.value.id}`)
    toast.success('Role deleted successfully')
    showDeleteModal.value = false
    if (selectedRole.value?.id === roleToDelete.value.id) {
      selectedRole.value = null
      selectedRoleId.value = null
    }
    await loadRoles()
  } catch {
    toast.error('Error deleting role')
  }
}

onMounted(() => {
  loadRoles()
  loadPermissions()
})
</script>

<style scoped>
input[type='radio']:checked + label {
  box-shadow: none;
}
.role-option {
  position: relative;
}
.role-option::after {
  content: "";
  position: absolute;
  inset: 10px;
  border-radius: 0.75rem;
  border: 1px solid transparent;
  pointer-events: none;
}
input[type='radio']:checked + .role-option {
  background: rgba(15, 23, 42, 0.06);
  border-color: transparent;
  color: #0f172a;
}
input[type='radio']:checked + .role-option::after {
  border-color: transparent;
}
.dark input[type='radio']:checked + .role-option {
  background: rgba(56, 189, 248, 0.18);
  border-color: transparent;
  color: #ffffff;
}
.dark input[type='radio']:checked + .role-option::after {
  border-color: transparent;
}
.dark .role-option {
  box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.08);
}
.dark .perm-card {
  background: rgba(15, 23, 42, 0.7);
  border-color: rgba(148, 163, 184, 0.2);
}
.role-page {
  background:
    radial-gradient(800px 400px at 10% 0%, rgba(59, 130, 246, 0.12), transparent 60%),
    radial-gradient(700px 500px at 90% 20%, rgba(56, 189, 248, 0.1), transparent 55%),
    linear-gradient(180deg, rgba(248, 250, 252, 0.85), rgba(255, 255, 255, 0.95));
  border-radius: 1.5rem;
}
.dark .role-page {
  background:
    radial-gradient(800px 400px at 10% 0%, rgba(56, 189, 248, 0.18), transparent 60%),
    radial-gradient(700px 500px at 90% 20%, rgba(14, 116, 144, 0.18), transparent 55%),
    linear-gradient(180deg, rgba(2, 6, 23, 0.92), rgba(15, 23, 42, 0.96));
}
</style>
