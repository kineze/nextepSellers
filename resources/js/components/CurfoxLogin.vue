<template>
  <div class="p-6 w-full space-y-6">
    <!-- Header -->
    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-4 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
            <i class="fas fa-truck-fast"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Royal Express Login Management</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">Securely save and refresh courier credentials</p>
          </div>
        </div>

      <div class="flex flex-wrap gap-2">
        <!-- Add Login -->
        <button 
          @click="openDrawer" 
          class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900">
          <i class="fas fa-plus mr-2"></i> New Login
        </button>

        <!-- Fetch Cities & States -->
        <button @click="openFetchModal" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-700">
          <i class="fas fa-sync mr-2"></i> Fetch Cities & States
        </button>
        <!-- Fetch Business List -->
        <button @click="fetchBusinesses" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700">
          <i class="fas fa-building mr-2"></i> Fetch Business List
        </button>
      </div>
      </div>
    </div>

    <!-- Success Message -->
    <transition name="fade">
      <div v-if="successMessage" class="flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
        <div class="text-sm font-medium">{{ successMessage }}</div>
        <button @click="successMessage = null" class="ml-auto p-1.5 text-green-500 rounded-lg hover:bg-green-200 dark:hover:bg-gray-700">✖</button>
      </div>
    </transition>

    <!-- Logins Table -->
    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
        <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <tr>
            <th class="px-1 py-3">Partner</th>
            <th class="px-1 py-3">Email</th>
            <th class="px-1 py-3">Merchant ID</th>
            <th class="px-1 py-3">Business ID</th>
            <th class="px-1 py-3">City</th>
            <th class="px-1 py-3">State</th>
            <th class="px-1 py-3">Status</th>
            <th class="px-1 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="logins.length === 0">
            <td colspan="8" class="px-1 py-4 text-center text-slate-500 dark:text-slate-400">No logins found.</td>
          </tr>
          <tr v-for="login in logins" :key="login.id" class="border-b border-slate-200/70 hover:bg-slate-50/80 dark:border-slate-800/70 dark:hover:bg-slate-800/40">
            <td class="px-1 py-4">{{ login.partner_name }}</td>
            <td class="px-1 py-4">{{ login.email }}</td>
            <td class="px-1 py-4">{{ login.merchant_id }}</td>
            <td class="px-1 py-4">{{ login.merchant_business_id }}</td>
            <td class="px-1 py-4">{{ login.city || '-' }}</td>
            <td class="px-1 py-4">{{ login.state || '-' }}</td>
            <td class="px-1 py-4">
              <span :class="login.is_active ? 'text-green-600' : 'text-red-600'">
                {{ login.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-1 py-4 text-right space-x-3">
              <button 
                @click="openLocationModal(login)" 
                class="text-blue-600 hover:text-blue-800" 
                title="Set City & State"
              >
                <i class="fas fa-map-marker-alt"></i>
              </button>
              <button @click="editLogin(login)" class="text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button @click="confirmDelete(login.id)" class="text-red-600 hover:text-red-800" title="Delete">
                <i class="fas fa-trash-alt"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- Live Business List -->
    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Royal Express Business List (live)</h3>
        <span v-if="fetchingBusinesses" class="text-xs text-slate-500 dark:text-slate-400">Loading…</span>
      </div>
      <div v-if="businessError" class="mt-3 text-sm text-rose-600">{{ businessError }}</div>
      <div v-else-if="businesses.length === 0" class="mt-3 text-sm text-slate-500 dark:text-slate-400">No businesses loaded. Click “Fetch Business List”.</div>
      <div v-else class="mt-3 overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-2 py-2">ID</th>
              <th class="px-2 py-2">Ref</th>
              <th class="px-2 py-2">Name</th>
              <th class="px-2 py-2">Default</th>
              <th class="px-2 py-2">Created</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in businesses" :key="b.id" class="border-b border-slate-200/70 dark:border-slate-800/70">
              <td class="px-2 py-2">{{ b.id }}</td>
              <td class="px-2 py-2">{{ b.ref_no }}</td>
              <td class="px-2 py-2">{{ b.business_name }}</td>
              <td class="px-2 py-2">
                <span :class="b.is_default ? 'text-green-600' : 'text-gray-500'">{{ b.is_default ? 'Yes' : 'No' }}</span>
              </td>
              <td class="px-2 py-2">{{ b.created_at }}</td>
            </tr>
          </tbody>
        </table>
        <details class="mt-3">
          <summary class="cursor-pointer text-xs text-slate-500 dark:text-slate-400">Show raw JSON</summary>
          <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-50 p-3 text-xs dark:bg-slate-950">
{{ JSON.stringify(businesses, null, 2) }}
          </pre>
        </details>
      </div>
    </div>

    <!-- Drawer for New/Edit Login -->
    <transition name="fade">
      <div v-if="isDrawerOpen" class="fixed inset-0 z-[1300] flex justify-end bg-slate-900/60 backdrop-blur-sm">
        <div class="relative w-full max-w-md overflow-y-auto border-l border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ isEditing ? 'Edit Login' : 'New Login' }}</h3>
          <button @click="closeDrawer" class="absolute right-4 top-4 h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white">✖</button>

          <form @submit.prevent="saveLogin" class="mt-6 space-y-6">
            <!-- Email -->
            <div class="relative z-0 w-full group">
              <input type="email" v-model.trim="form.email" required class="floating-input peer" placeholder=" " />
              <label class="floating-label">Email</label>
            </div>
            <!-- Password -->
            <div class="relative z-0 w-full group">
              <input type="password" v-model="form.password" :required="!isEditing" class="floating-input peer" placeholder=" " />
              <label class="floating-label">Password</label>
              <p v-if="isEditing" class="text-xs text-gray-500 mt-1">Leave blank to keep the current password.</p>
            </div>

            <button type="submit" :disabled="loading" class="w-full rounded-xl bg-slate-900 py-2.5 text-sm text-white hover:bg-black disabled:opacity-60 dark:bg-white dark:text-slate-900">
              <span v-if="loading">Saving…</span>
              <span v-else>{{ isEditing ? 'Update' : 'Connect & Save' }}</span>
            </button>
          </form>
        </div>
      </div>
    </transition>

    <!-- Origin City/State Modal -->
    <transition name="fade">
      <div v-if="showLocationModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Set Origin City & State</h3>
          <form @submit.prevent="saveLocation" class="space-y-6">
            <div class="relative z-0 w-full group">
              <input v-model="locationForm.city" type="text" required class="floating-input peer" placeholder=" " />
              <label class="floating-label">City</label>
            </div>
            <div class="relative z-0 w-full group">
              <input v-model="locationForm.state" type="text" required class="floating-input peer" placeholder=" " />
              <label class="floating-label">State</label>
            </div>
            <div class="flex justify-end space-x-3 mt-4">
              <button @click="showLocationModal = false" type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button>
              <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Save</button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Delete Modal -->
    <transition name="fade">
      <div v-if="showDeleteModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Confirm Deletion</h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Are you sure you want to delete this login?</p>
          <div class="flex justify-end mt-4 space-x-3">
            <button @click="showDeleteModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button>
            <button @click="deleteLogin" class="rounded-xl bg-rose-600 px-4 py-2 text-white hover:bg-rose-700">Delete</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Fetch Modal -->
    <transition name="fade">
      <div v-if="showFetchModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Fetch Royal Express Cities & States</h3>
          <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">This will use your Royal Express login credentials to fetch and sync cities & states into the system.</p>
          <div class="mb-4 h-32 overflow-y-auto rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-950">
            <div v-for="(msg, index) in fetchLogs" :key="index" class="mb-1">
              <span class="text-slate-700 dark:text-slate-300">{{ msg }}</span>
            </div>
          </div>
          <div class="flex justify-end space-x-3">
            <button @click="showFetchModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Close</button>
            <button @click="startFetching" :disabled="fetching" class="rounded-xl bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
              {{ fetching ? "Fetching..." : "Start Fetching" }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";

export default {
  name: "CurfoxLoginManager",
  data() {
    return {
      logins: [],
      form: { id: null, email: "", password: "" },
      isDrawerOpen: false,
      isEditing: false,
      showDeleteModal: false,
      deletingId: null,
      loading: false,
      successMessage: null,

      showLocationModal: false,
      locationForm: { id: null, city: "", state: "" },

      showFetchModal: false,
      fetchLogs: [],
      fetching: false,

      businesses: [],
      businessError: null,
      fetchingBusinesses: false,

      toast: useToast(),
    };
  },
  methods: {
    // === Fetch logins ===
    async fetchLogins() {
      try {
        const res = await axios.get("/api/royal-express/logins");
        this.logins = res.data;
      } catch {
        this.toast.error("Failed to load logins.");
      }
    },

    openDrawer() {
      this.resetForm();
      this.isDrawerOpen = true;
    },
    closeDrawer() {
      this.isDrawerOpen = false;
    },
    resetForm() {
      this.form = { id: null, email: "", password: "" };
      this.isEditing = false;
    },
    editLogin(login) {
      this.form = { id: login.id, email: login.email, password: "" };
      this.isEditing = true;
      this.isDrawerOpen = true;
    },

    // === Save login via Royal Express auth ===
    async saveLogin() {
      this.loading = true;
      try {
        const payload = {
          email: this.form.email,
          password: this.form.password,
        };

        const res = await axios.post("/api/royal-express/login", payload);
        this.toast.success(res.data.message || "Royal Express login saved successfully!");
        this.fetchLogins();
        this.closeDrawer();
      } catch (err) {
        this.toast.error(err.response?.data?.message || "Login failed.");
      } finally {
        this.loading = false;
      }
    },


    confirmDelete(id) {
      this.deletingId = id;
      this.showDeleteModal = true;
    },
    async deleteLogin() {
      try {
        await axios.delete(`/api/royal-express/logins/${this.deletingId}`);
        this.toast.success("Login deleted successfully!");
        this.fetchLogins();
      } catch {
        this.toast.error("Failed to delete login.");
      } finally {
        this.showDeleteModal = false;
      }
    },

    // === Fetch Cities & States ===
    openFetchModal() {
      this.fetchLogs = [];
      this.showFetchModal = true;
    },
    async startFetching() {
      this.fetching = true;
      this.fetchLogs = ["🔄 Starting fetch process..."];

      let page = 1;
      let hasNext = true;
      let type = "state";

      while (hasNext) {
        try {
          const res = await axios.post("/api/royal-express/fetch-cities-states", { type, page });

          if (res.data.logs) this.fetchLogs.push(...res.data.logs);

          if (res.data.count > 0) {
            this.fetchLogs.push(
              `✅ Synced ${res.data.count} ${res.data.type}s. Total in DB: ${res.data.total}`
            );
          }

          if (res.data.next) {
            page = res.data.next;
          } else {
            if (type === "state") {
              this.fetchLogs.push("🎉 Finished syncing states. Moving to cities...");
              type = "city";
              page = 1;
              hasNext = true;
            } else {
              hasNext = false;
            }
          }
        } catch (err) {
          this.fetchLogs.push("❌ Error syncing: " + (err.response?.data?.message || err.message));
          hasNext = false;
        }
      }

      this.fetchLogs.push("✅ Done syncing all states & cities.");
      this.fetching = false;
    },

    // === Location ===
    openLocationModal(login) {
      this.locationForm = {
        id: login.id,
        city: login.city || "",
        state: login.state || "",
      };
      this.showLocationModal = true;
    },
    async saveLocation() {
      try {
        const res = await axios.put(`/api/royal-express/logins/${this.locationForm.id}/location`, {
          city: this.locationForm.city,
          state: this.locationForm.state,
        });
        this.toast.success(res.data.message || "Location updated successfully!");
        this.fetchLogins();
        this.showLocationModal = false;
      } catch (err) {
        this.toast.error(err.response?.data?.message || "Failed to update location.");
      }
    },

    // === Business List ===
    async fetchBusinesses() {
      this.fetchingBusinesses = true;
      this.businessError = null;
      try {
        const res = await axios.get("/api/royal-express/businesses", { params: { noPagination: 1 } });
        this.businesses = res.data.businesses || [];
        this.toast.success(`Loaded ${this.businesses.length} businesses`);
      } catch (err) {
        const msg = err.response?.data?.message || err.message || "Failed to fetch businesses";
        this.businessError = msg;
        this.toast.error(msg);
      } finally {
        this.fetchingBusinesses = false;
      }
    },
  },
  mounted() {
    this.fetchLogins();
  },
};
</script>


<style scoped>
.floating-input {
  @apply block py-2.5 px-0 w-full text-sm bg-transparent border-0 border-b-2 border-gray-300 focus:outline-none focus:ring-0 focus:border-gray-600 dark:text-white dark:border-gray-600;
}
.floating-label {
  @apply absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-gray-600 peer-focus:dark:text-gray-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6;
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
