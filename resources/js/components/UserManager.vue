<template>
  <div class="p-6 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Settings</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">System Users</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
        Manage access, roles, and security for your workspace.
      </p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-4 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
            <i class="fas fa-users"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">User Role Manager</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">Filter and manage user roles</p>
          </div>
        </div>

        <form class="w-full lg:w-auto" @submit.prevent>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <!-- Role Dropdown -->
            <div class="relative">
              <button
                id="dropdown-button"
                type="button"
                @click="showRoleDropdown = !showRoleDropdown"
                class="inline-flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
              >
                {{ selectedRoleLabel }}
                <svg class="h-2.5 w-2.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 4 4 4-4" />
                </svg>
              </button>

              <!-- Dropdown List -->
              <div
                v-if="showRoleDropdown"
                class="absolute z-20 mt-2 w-48 rounded-xl border border-slate-200 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-900"
              >
                <ul class="text-sm text-slate-700 dark:text-slate-200">
                  <li>
                    <button @click="selectRole('')" type="button"
                            class="inline-flex w-full rounded-lg px-3 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-800">
                      All Roles
                    </button>
                  </li>
                  <li v-for="role in roles" :key="role.id">
                    <button @click="selectRole(role.name)" type="button"
                            class="inline-flex w-full rounded-lg px-3 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-800">
                      {{ role.name }}
                    </button>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Search Input -->
            <div class="relative w-full sm:w-80">
              <input
                v-model="search"
                @input="debounceSearch"
                type="search"
                id="search-users"
                placeholder="Search name or email..."
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-slate-400"
              />
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                <i class="fas fa-search"></i>
              </div>
            </div>

            <button
              @click="openDrawer"
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black dark:bg-white dark:text-slate-900"
            >
              <i class="fas fa-plus"></i> New User
            </button>
          </div>
        </form>
      </div>

      <!-- Table -->
      <div class="p-4 overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">Name</th>
              <th class="px-3 py-3">Email</th>
              <th class="px-3 py-3">Role</th>
              <th class="px-3 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
            <tr v-if="users.length === 0">
              <td colspan="4" class="text-center py-6 text-slate-500 dark:text-slate-400">No users found</td>
            </tr>

            <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4 font-medium text-slate-900 dark:text-white">{{ user.name }}</td>
              <td class="px-3 py-4">{{ user.email }}</td>
              <td class="px-3 py-4">
                <span
                  v-if="user.roles.length"
                  :class="[
                    'text-[0.65rem] font-semibold px-2.5 py-1 rounded-full uppercase tracking-wide',
                    user.roles[0].name === 'Admin'
                      ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'
                      : user.roles[0].name === 'Vendor'
                      ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200'
                      : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
                  ]"
                >
                  {{ user.roles[0].name }}
                </span>
                <span v-else class="text-slate-400 italic">No Role</span>
                <span
                  v-if="user.is_blocked"
                  class="ml-2 text-[0.65rem] font-semibold px-2.5 py-1 rounded-full uppercase tracking-wide bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200"
                >
                  Blocked
                </span>
              </td>
              <td class="px-3 py-4 text-right space-x-3">
                <button @click="editUser(user)" class="btn-icon text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="openPasswordModal(user.id)" class="btn-icon text-amber-500 hover:text-amber-600">
                  <i class="fas fa-key"></i>
                </button>
                <button
                  v-if="!user.is_blocked"
                  @click="openBlockModal(user)"
                  class="btn-icon text-orange-500 hover:text-orange-600"
                  title="Block user"
                >
                  <i class="fas fa-user-slash"></i>
                </button>
                <button
                  v-else
                  @click="openBlockModal(user)"
                  class="btn-icon text-emerald-500 hover:text-emerald-600"
                  title="Unblock user"
                >
                  <i class="fas fa-user-check"></i>
                </button>
                <button @click="openDeleteModal(user.id)" class="btn-icon text-rose-500 hover:text-rose-600">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
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
          <button
            class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
            :disabled="pagination.current_page <= 1"
            @click="changePage(pagination.current_page - 1)"
          >
            Prev
          </button>

          <div class="flex items-center gap-1">
            <button
              v-for="page in pageNumbers"
              :key="page"
              class="min-w-[36px] px-2 py-1.5 rounded-lg border text-center"
              :class="page === pagination.current_page
                ? 'border-slate-900 bg-slate-900 text-white dark:border-white dark:bg-white dark:text-slate-900'
                : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'"
              @click="changePage(page)"
            >
              {{ page }}
            </button>
          </div>

          <button
            class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
            :disabled="pagination.current_page >= pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Drawer: Create/Edit User -->
  <transition name="fade">
      <div v-if="showDrawer" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex justify-end z-[1300]">
        <div class="bg-white/95 dark:bg-slate-900/95 w-full max-w-md p-6 shadow-2xl overflow-y-auto border-l border-slate-200/70 dark:border-slate-800/70">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
            {{ editingId ? 'Edit User' : 'Create New User' }}
            </h3>
            <button @click="closeDrawer" class="h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white">
              ✖
            </button>
          </div>

            <form @submit.prevent="saveUser" class="space-y-5">
                <div class="relative z-0 w-full group">
                    <input type="text" v-model="form.name" required class="floating-input peer" placeholder=" " />
                    <label class="floating-label">Name</label>
                </div>

                <div class="relative z-0 w-full group">
                    <input type="email" v-model="form.email" required class="floating-input peer" placeholder=" " />
                    <label class="floating-label">Email</label>
                </div>

                <!-- Password Input + Info -->
                <div class="relative z-0 w-full group">
                    <input type="password" v-model="form.password" class="floating-input peer" placeholder=" " />
                    <label class="floating-label">Password</label>
                </div>
                <div
                    class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                >
                    <i class="fas fa-info-circle mr-2"></i>
                    Leave the password field blank to auto-generate a secure password. It will be emailed to the user automatically.
                </div>

                <!-- Role Selection (custom radio list) -->
                <div>
                    <h4 class="text-sm font-semibold mb-2 text-slate-700 dark:text-slate-300">Assign Role</h4>
                    <ul class="grid w-full gap-4">
                    <li v-for="role in roles" :key="role.id">
                        <input
                        type="radio"
                        :id="'role-' + role.id"
                        name="role"
                        :value="role.name"
                        v-model="form.role"
                        class="hidden peer"
                        />
                        <label
                        :for="'role-' + role.id"
                        class="inline-flex items-center justify-between w-full p-4 text-slate-500 bg-white border border-slate-200 rounded-xl cursor-pointer
                                dark:hover:text-slate-200 dark:border-slate-700 dark:peer-checked:text-sky-200
                                peer-checked:border-sky-500 dark:peer-checked:border-sky-400 peer-checked:text-sky-600
                                hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:bg-slate-800 dark:hover:bg-slate-700 transition"
                        >
                        <div>
                            <div class="text-lg font-semibold capitalize">{{ role.name }}</div>
                            <div class="text-sm">System Role</div>
                        </div>
                        <i class="fa-solid fa-circle mt-0.5"></i>
                        </label>
                    </li>
                    </ul>
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 bg-slate-900 text-sm text-white rounded-xl hover:bg-black dark:bg-white dark:text-slate-900"
                >
                    {{ editingId ? 'Update User' : 'Save User' }}
                </button>
            </form>

        </div>
      </div>
    </transition>
  </div>

  <!-- Delete Confirmation Modal -->
  <transition name="fade">
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-[1500] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center"
    >
      <div class="bg-white/95 dark:bg-slate-900/95 p-6 rounded-2xl w-full max-w-md shadow-2xl border border-slate-200/70 dark:border-slate-800/70">
        <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white">
          Confirm Delete
        </h3>
        <p class="text-slate-600 dark:text-slate-300 mb-6">
          Are you sure you want to delete this user? This action cannot be undone.
        </p>

        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          >
            Cancel
          </button>

          <button
            @click="deleteUser"
            class="px-4 py-2 rounded-xl bg-rose-600 text-white hover:bg-rose-700"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </transition>

  <!-- Reset Password Modal -->
  <transition name="fade">
    <div
      v-if="showPasswordModal"
      class="fixed inset-0 z-[1500] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center"
    >
      <div class="bg-white/95 dark:bg-slate-900/95 p-6 rounded-2xl w-full max-w-md shadow-2xl border border-slate-200/70 dark:border-slate-800/70">
        <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white">
          Reset Password
        </h3>
        <p class="text-slate-600 dark:text-slate-300 mb-6">
          Are you sure you want to reset this user's password?  
          A new password will be generated and emailed to the user.
        </p>

        <div class="flex justify-end space-x-3">
          <button
            @click="showPasswordModal = false"
            class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          >Cancel</button>

         <button
            @click="confirmResetPassword"
            class="px-4 py-2 rounded-xl bg-amber-500 text-white hover:bg-amber-600"
          >Reset</button>

        </div>
      </div>
    </div>
  </transition>


  <!-- Block Confirmation Modal -->
  <transition name="fade">
    <div
      v-if="showBlockModal"
      class="fixed inset-0 z-[1500] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center"
    >
      <div class="bg-white/95 dark:bg-slate-900/95 p-6 rounded-2xl w-full max-w-md shadow-2xl border border-slate-200/70 dark:border-slate-800/70">
        <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white">
          {{ blockAction === 'block' ? 'Block User' : 'Unblock User' }}
        </h3>

        <p class="text-slate-600 dark:text-slate-300 mb-3">
          {{ blockAction === 'block'
            ? 'Please provide a reason for blocking this user. This reason will be sent by email.'
            : 'Are you sure you want to unblock this user?' }}
        </p>

        <textarea
          v-if="blockAction === 'block'"
          v-model="blockReason"
          rows="4"
          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          placeholder="Enter blocking reason..."
        ></textarea>

        <div class="flex justify-end space-x-3">
          <button
            @click="showBlockModal = false"
            class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          >
            Cancel
          </button>

          <button
            @click="confirmBlockUser"
            class="px-4 py-2 rounded-xl bg-amber-500 text-white hover:bg-amber-600"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>
  </transition>


</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue"
import axios from "axios"
import { useToast } from "vue-toastification"

const toast = useToast()

// STATE
const users = ref([])
const roles = ref([])
const search = ref("")
const selectedRole = ref("")
const showRoleDropdown = ref(false)
const selectedRoleLabel = computed(() => selectedRole.value || "All Roles")
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
})

const pageNumbers = computed(() => {
  const total = pagination.value.last_page || 1
  const current = pagination.value.current_page || 1
  const windowSize = 5
  let start = Math.max(1, current - Math.floor(windowSize / 2))
  let end = Math.min(total, start + windowSize - 1)
  start = Math.max(1, end - windowSize + 1)
  const pages = []
  for (let i = start; i <= end; i += 1) pages.push(i)
  return pages
})

// Create/Edit drawer
const showDrawer = ref(false)
const form = ref({ name: "", email: "", password: "", role: "" })
const editingId = ref(null)

// Delete modal
const showDeleteModal = ref(false)
const deletingId = ref(null)

// Reset Password modal
const showPasswordModal = ref(false)
const passwordResetUserId = ref(null)

// Block modal
const showBlockModal = ref(false)
const blockUserId = ref(null)
const blockAction = ref("block")
const blockReason = ref("")

let searchTimeout = null

// Load users + roles
const fetchUsers = async (page = 1) => {
  try {
    const res = await axios.get("/api/users", {
      params: { search: search.value, role: selectedRole.value, page, per_page: pagination.value.per_page }
    })
    users.value = res.data.users
    roles.value = res.data.roles
    pagination.value = res.data.pagination || pagination.value
  } catch (error) {
    toast.error("Failed to load users")
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchUsers(1), 400)
}

// Role filter
const selectRole = (roleName) => {
  selectedRole.value = roleName
  showRoleDropdown.value = false
  fetchUsers(1)
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchUsers(page)
}

// ---------------------------
// DELETE LOGIC
// ---------------------------
const openDeleteModal = (id) => {
  deletingId.value = id
  showDeleteModal.value = true
}

const deleteUser = async () => {
  try {
    await axios.delete(`/api/users/${deletingId.value}`)
    toast.success("User deleted successfully!")
    showDeleteModal.value = false
    fetchUsers(pagination.value.current_page)
  } catch {
    toast.error("Failed to delete user.")
  }
}

// ---------------------------
// RESET PASSWORD LOGIC
// ---------------------------
const openPasswordModal = (id) => {
  passwordResetUserId.value = id
  showPasswordModal.value = true
}

const confirmResetPassword = async () => {
  try {
    await axios.post(`/api/users/${passwordResetUserId.value}/reset-password`)
    toast.success("Password reset — Email sent via Brevo!")
    showPasswordModal.value = false
  } catch {
    toast.error("Failed to reset password.")
  }
}

// ---------------------------
// BLOCK / UNBLOCK LOGIC
// ---------------------------
const openBlockModal = (user) => {
  blockUserId.value = user.id
  blockAction.value = user.is_blocked ? "unblock" : "block"
  blockReason.value = ""
  showBlockModal.value = true
}

const confirmBlockUser = async () => {
  if (blockAction.value === "block" && !blockReason.value.trim()) {
    toast.error("Block reason is required.")
    return
  }

  try {
    if (blockAction.value === "block") {
      await axios.post(`/api/users/${blockUserId.value}/block`, {
        reason: blockReason.value
      })
      toast.success("User blocked successfully and email sent.")
    } else {
      await axios.post(`/api/users/${blockUserId.value}/unblock`)
      toast.success("User unblocked successfully.")
    }
    showBlockModal.value = false
    fetchUsers(pagination.value.current_page)
  } catch (err) {
    toast.error(err.response?.data?.message || "Failed to update user status.")
  }
}

// ---------------------------
// CREATE / UPDATE USER
// ---------------------------
const saveUser = async () => {
  try {
    if (editingId.value) {
      // Update user
      await axios.put(`/api/users/${editingId.value}`, form.value)
      toast.success("User updated successfully!")
    } else {
      // Create user (Brevo will send password)
      await axios.post("/api/users", form.value)
      toast.success("User created! Login details emailed.")
    }

    closeDrawer()
    fetchUsers(pagination.value.current_page)
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error("Error saving user.")
    }
  }
}

const openDrawer = () => {
  form.value = { name: "", email: "", password: "", role: "" }
  editingId.value = null
  showDrawer.value = true
}

const closeDrawer = () => {
  showDrawer.value = false
}

const editUser = (user) => {
  form.value = {
    name: user.name,
    email: user.email,
    role: user.roles[0]?.name || ""
  }
  editingId.value = user.id
  showDrawer.value = true
}

// Close dropdown on outside click
const handleClickOutside = (e) => {
  const dropdown = document.getElementById("dropdown-button")
  if (dropdown && !dropdown.contains(e.target)) {
    showRoleDropdown.value = false
  }
}

onMounted(() => {
  fetchUsers(1)
  document.addEventListener("click", handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside)
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
