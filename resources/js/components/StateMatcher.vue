<template>
  <div class="p-6 w-full space-y-6">
    <!-- Header -->
    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-4 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Curfox State ↔ District Matcher</h2>
      <div class="flex flex-wrap gap-2">
        <!-- Refresh Stats -->
        <button
          @click="fetchStats"
          class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700"
        >
          <i class="fas fa-sync mr-2"></i> Refresh Stats
        </button>
        <!-- Sync Matched -->
        <button
          @click="syncMatched"
          :disabled="loading"
          class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-700 disabled:opacity-50"
        >
          <i class="fas fa-link mr-2"></i>
          {{ loading ? "Syncing..." : "Sync Matched" }}
        </button>
      </div>
      </div>
    </div>

    <!-- Stats Table -->
    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
        <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <tr>
            <th class="px-3 py-3">Total States</th>
            <th class="px-3 py-3">Synced</th>
            <th class="px-3 py-3">Matched (unsynced)</th>
            <th class="px-3 py-3">Unmatched</th>
            <th class="px-3 py-3">Match Score</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!stats">
            <td colspan="5" class="px-3 py-4 text-center text-slate-500 dark:text-slate-400">
              Loading stats...
            </td>
          </tr>
          <tr v-else class="border-b border-slate-200/70 dark:border-slate-800/70">
            <td class="px-3 py-4">{{ stats.total_states }}</td>
            <td class="px-3 py-4 text-blue-600 font-semibold">{{ stats.synced_count }}</td>
            <td class="px-3 py-4 text-green-600 font-semibold">{{ stats.matched_count }}</td>
            <td class="px-3 py-4 text-red-600 font-semibold">{{ stats.unmatched_count }}</td>
            <td class="px-3 py-4 font-bold">{{ stats.score }}%</td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- Synced States Table -->
    <div v-if="stats?.synced_states?.length" class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <h3 class="mb-2 text-md font-bold text-blue-700 dark:text-blue-300">Synced States</h3>
      <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
        <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <tr>
            <th class="px-3 py-3">Curfox State</th>
            <th class="px-3 py-3">Linked District</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in stats.synced_states" :key="s.curfox_state_id" class="border-b border-slate-200/70 dark:border-slate-800/70">
            <td class="px-3 py-4">{{ s.curfox_state_name }}</td>
            <td class="px-3 py-4">{{ s.district_name }}</td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- Matched States (Unsynced) Table -->
    <div v-if="stats?.matched_states?.length" class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <h3 class="mb-2 text-md font-bold text-emerald-700 dark:text-emerald-300">Matched States (Need Sync)</h3>
      <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
        <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <tr>
            <th class="px-3 py-3">Curfox State</th>
            <th class="px-3 py-3">Suggested District</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in stats.matched_states" :key="s.curfox_state_id" class="border-b border-slate-200/70 dark:border-slate-800/70">
            <td class="px-3 py-4">{{ s.curfox_state_name }}</td>
            <td class="px-3 py-4">{{ s.district_name }}</td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- Unmatched States Table -->
    <div v-if="stats?.unmatched_states?.length" class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <h3 class="mb-2 text-md font-bold text-rose-700 dark:text-rose-300">Unmatched States</h3>
      <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
        <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <tr>
            <th class="px-3 py-3">Curfox State</th>
            <th class="px-3 py-3">Match with District</th>
            <th class="px-3 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in stats.unmatched_states" :key="s.curfox_state_id" class="border-b border-slate-200/70 dark:border-slate-800/70">
            <td class="px-3 py-4">{{ s.curfox_state_name }}</td>
            <td class="px-3 py-4">
              <select
                v-model="selectedDistricts[s.curfox_state_id]"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option disabled value="">-- Select District --</option>
                <option v-for="d in districts" :key="d.id" :value="d.id">
                  {{ d.name_en }}
                </option>
              </select>
            </td>
            <td class="px-3 py-4 text-right space-x-2">
              <button
                @click="manualMatch(s)"
                :disabled="!selectedDistricts[s.curfox_state_id]"
                class="rounded-lg bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-700"
              >
                <i class="fas fa-save mr-1"></i> Save Match
              </button>
              <button
                @click="createAndMatch(s)"
                class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700"
              >
                <i class="fas fa-plus mr-1"></i> Create New
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- Sync Result Modal -->
    <transition name="fade">
      <div
        v-if="showResultModal"
        class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
      >
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
            Sync Results
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
            ✅ Synced {{ result?.synced }} states successfully.
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="showResultModal = false"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
            >
              Close
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
  name: "CityMatcher",
  data() {
    return {
      stats: null,
      result: null,
      loading: false,
      showResultModal: false,
      districts: [],
      selectedDistricts: {},
      toast: useToast(),
    };
  },
  methods: {
    async fetchStats() {
      try {
        const res = await axios.get("/api/curfox-states/preview");
        this.stats = res.data;

        const dRes = await axios.get("/api/districts");
        this.districts = dRes.data;
      } catch {
        this.toast.error("❌ Failed to fetch stats");
      }
    },
    async syncMatched() {
      this.loading = true;
      try {
        const res = await axios.post("/api/curfox-states/sync-matched");
        this.result = res.data;
        this.showResultModal = true;
        await this.fetchStats();
      } catch {
        this.toast.error("❌ Error syncing matched states");
      } finally {
        this.loading = false;
      }
    },
    async manualMatch(state) {
      try {
        const districtId = this.selectedDistricts[state.curfox_state_id];
        await axios.post("/api/curfox-states/manual-match", {
          state_id: state.curfox_state_id,
          district_id: districtId,
        });
        this.toast.success(`✅ State "${state.curfox_state_name}" matched successfully`);
        await this.fetchStats();
      } catch {
        this.toast.error("❌ Failed to match state");
      }
    },
    async createAndMatch(state) {
      try {
        await axios.post("/api/curfox-states/create-and-match", {
          state_id: state.curfox_state_id,
        });
        this.toast.success(`✅ New district created for "${state.curfox_state_name}"`);
        await this.fetchStats();
      } catch {
        this.toast.error(`❌ Failed to create district`);
      }
    },
  },
  mounted() {
    // Auto load stats when page loads
    this.fetchStats();
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
