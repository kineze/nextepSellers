<template>
  <div class="p-6 w-full space-y-6">
    <!-- Header -->
    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-4 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
          Curfox City ↔ System City Matcher
        </h2>
      </div>
      <div class="flex flex-wrap gap-2">
        <button @click="fetchStats"
                class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700">
          <i class="fas fa-sync mr-2"></i> Refresh Stats
        </button>
        <button @click="startSync" :disabled="loading || stats?.matched_count === 0"
                class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-700 disabled:opacity-50">
          <i class="fas fa-link mr-2"></i>
          {{ loading ? "Syncing..." : "Sync Matched" }}
        </button>
        <button @click="startAutoCreate" :disabled="loading || stats?.unmatched_count === 0"
                class="rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-violet-700 disabled:opacity-50">
          <i class="fas fa-plus mr-2"></i>
          {{ loading ? "Creating..." : "Auto Create Unmatched" }}
        </button>
        <button @click="checkOrphans"
                class="rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-rose-700">
          <i class="fas fa-trash mr-2"></i> Clean Orphans
        </button>
      </div>
      </div>
    </div>

    <!-- Stats as Cards -->
    <div v-if="stats" class="grid grid-cols-2 gap-4 md:grid-cols-5">
      <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 text-center shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <p class="text-sm text-slate-500 dark:text-slate-400">Total Cities</p>
        <p class="text-xl font-bold">{{ stats.total_cities }}</p>
      </div>
      <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-center shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
        <p class="text-sm text-blue-600 dark:text-blue-300">Synced</p>
        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ stats.synced_count }}</p>
      </div>
      <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <p class="text-sm text-green-600 dark:text-green-300">Matched</p>
        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ stats.matched_count }}</p>
      </div>
      <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-center shadow-sm dark:border-rose-500/30 dark:bg-rose-500/10">
        <p class="text-sm text-red-600 dark:text-red-300">Unmatched</p>
        <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ stats.unmatched_count }}</p>
      </div>
      <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-center shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
        <p class="text-sm text-yellow-600 dark:text-yellow-300">Match Score</p>
        <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.score }}%</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="inline-flex rounded-xl border border-slate-300 bg-white p-1 dark:border-slate-700 dark:bg-slate-950">
      <button v-for="t in tabs" :key="t" @click="changeTab(t)"
              class="rounded-lg px-4 py-2 text-sm font-semibold transition"
              :class="activeTab === t ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-300'">
        {{ t.charAt(0).toUpperCase() + t.slice(1) }}
      </button>
    </div>

    <!-- Search bar -->
    <div v-if="activeTab" class="mt-4 mb-2">
      <input type="text" v-model="search" @input="fetchList(1)"
             :placeholder="`Search ${activeTab} cities...`"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white md:w-1/3" />
    </div>

    <!-- Table -->
    <div v-if="list.data.length" class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
        <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <tr>
            <th class="px-3 py-2">Curfox City</th>
            <th v-if="activeTab !== 'unmatched'" class="px-3 py-2">System City</th>
            <th v-else class="px-3 py-2">Select Match</th>
            <th v-if="activeTab === 'unmatched'" class="px-3 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
            <tr v-for="c in list.data" :key="c.curfox_city_id"
              class="border-b border-slate-200/70 dark:border-slate-800/70">
            <td class="px-3 py-2">{{ c.curfox_city_name }}</td>

            <!-- Synced / Matched -->
            <td v-if="activeTab !== 'unmatched'" class="px-3 py-2">{{ c.system_city_name }}</td>

            <!-- Unmatched -->
            <template v-else>
              <td class="px-3 py-2 relative">
                <div class="relative">
                  <input type="text"
                         v-model="searchQueries[c.curfox_city_id]"
                         @focus="openDropdown(c.curfox_city_id)"
                         @input="searchSystemCities(c.curfox_city_id)"
                         placeholder="Search system city..."
                         class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                  <!-- Dropdown -->
                  <div v-if="openDropdownId === c.curfox_city_id"
                       class="absolute z-20 mt-1 w-full rounded-xl border border-slate-200 bg-white shadow dark:border-slate-700 dark:bg-slate-800">
                    <ul class="max-h-48 overflow-y-auto text-sm">
                      <li v-for="s in suggestions[c.curfox_city_id] || []" :key="s.id"
                          @click="selectSystemCity(c.curfox_city_id, s)"
                          class="flex cursor-pointer items-center gap-2 px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <input type="radio" class="w-4 h-4" :value="s.id"
                               v-model="selected[c.curfox_city_id]" @click.stop />
                        <span class="dark:text-gray-100">{{ s.name_en }}</span>
                      </li>
                      <li v-if="!suggestions[c.curfox_city_id] || suggestions[c.curfox_city_id].length === 0"
                          class="px-3 py-2 text-gray-500 dark:text-gray-300">
                        No system cities found
                      </li>
                    </ul>
                  </div>
                </div>
              </td>
              <td class="px-3 py-2 text-right space-x-2">
                <button @click="manualMatch(c)" :disabled="!selected[c.curfox_city_id]"
                        class="rounded-lg bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-700">Save</button>
                <button @click="createAndMatch(c)"
                        class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700">Create New</button>
              </td>
            </template>
          </tr>
        </tbody>
      </table>
      </div>

      <!-- Pagination -->
      <div class="flex justify-between items-center mt-4 text-sm">
        <span>Page {{ list.current_page }} of {{ list.last_page }}</span>
        <div class="space-x-2">
          <button @click="fetchList(list.current_page - 1)" :disabled="list.current_page === 1"
                  class="rounded-lg border border-slate-200 px-3 py-1 text-slate-700 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200">Prev</button>
          <button @click="fetchList(list.current_page + 1)" :disabled="list.current_page === list.last_page"
                  class="rounded-lg border border-slate-200 px-3 py-1 text-slate-700 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200">Next</button>
        </div>
      </div>
    </div>

    <div v-else-if="activeTab" class="mt-4 text-gray-500 text-center">
      No {{ activeTab }} cities found.
    </div>

    <!-- Sync Progress Modal -->
    <transition name="fade">
      <div v-if="showSyncModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Syncing Matched Cities</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Synced: {{ synced }} / {{ totalToSync }}</p>
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 mb-4">
            <div class="bg-green-600 h-4 rounded-full" :style="{ width: ((synced / totalToSync) * 100) + '%' }"></div>
          </div>
          <div class="flex justify-end">
            <button v-if="synced >= totalToSync" @click="closeModal"
                    class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Close</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Auto Create Progress Modal -->
    <transition name="fade">
      <div v-if="showAutoModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Auto Creating Unmatched Cities</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Created: {{ created }} / {{ totalToCreate }}</p>
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 mb-4">
            <div class="bg-purple-600 h-4 rounded-full" :style="{ width: ((created / totalToCreate) * 100) + '%' }"></div>
          </div>
          <div class="flex justify-end">
            <button v-if="created >= totalToCreate" @click="closeAutoModal"
                    class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Close</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Orphan Cleanup Modal -->
    <transition name="fade">
      <div v-if="showOrphanModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Cleanup Orphan Cities</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
            Found <b>{{ orphanCount }}</b> orphan cities in System DB that are not linked to any Curfox City.
          </p>
          <div class="flex justify-end space-x-2">
            <button @click="closeOrphanModal" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button>
            <button @click="deleteOrphans" class="rounded-xl bg-rose-600 px-4 py-2 text-white hover:bg-rose-700">Delete All</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "CitySync",
  data() {
    return {
      stats: null,
      list: { data: [] },
      activeTab: "",
      tabs: ["synced", "matched", "unmatched"],
      loading: false,
      search: "",
      searchQueries: {},
      suggestions: {},
      selected: {},
      openDropdownId: null,
      // sync modal
      showSyncModal: false,
      totalToSync: 0,
      synced: 0,
      // auto create modal
      showAutoModal: false,
      totalToCreate: 0,
      created: 0,
      // orphan modal
      showOrphanModal: false,
      orphanCount: 0,
    };
  },
  methods: {
    async fetchStats() {
      const res = await axios.get("/api/curfox-cities/stats");
      this.stats = res.data;
    },
    async fetchList(page = 1) {
      if (!this.activeTab) return;
      const params = { type: this.activeTab, page, per_page: 10 };
      if (this.search) params.search = this.search;
      const res = await axios.get("/api/curfox-cities/preview", { params });
      this.list = res.data;
    },
    changeTab(tab) {
      this.activeTab = tab;
      this.search = "";
      this.fetchList(1);
    },
    openDropdown(cityId) {
      this.openDropdownId = cityId;
      this.suggestions[cityId] = [];
      this.searchSystemCities(cityId);
    },
    async searchSystemCities(cityId) {
      const q = this.searchQueries[cityId] || "";
      const res = await axios.get("/api/system-cities", { params: { search: q } });
      this.suggestions[cityId] = res.data;
    },
    selectSystemCity(cityId, systemCity) {
      this.selected[cityId] = systemCity.id;
      this.searchQueries[cityId] = systemCity.name_en;
      this.openDropdownId = null;
    },
    async startSync() {
      this.loading = true;
      this.showSyncModal = true;
      this.synced = 0;
      this.totalToSync = this.stats.matched_count;
      while (this.synced < this.totalToSync) {
        const res = await axios.post("/api/curfox-cities/sync-matched", { batch: 50 });
        this.synced += res.data.synced;
        if (res.data.remaining <= 0) break;
      }
      await this.fetchStats();
      this.loading = false;
    },
    async startAutoCreate() {
      this.loading = true;
      this.showAutoModal = true;
      this.created = 0;
      this.totalToCreate = this.stats.unmatched_count;
      while (this.created < this.totalToCreate) {
        const res = await axios.post("/api/curfox-cities/auto-create-sync", { batch: 50 });
        this.created += res.data.created;
        if (res.data.remaining <= 0) break;
      }
      await this.fetchStats();
      this.loading = false;
    },
    async checkOrphans() {
      const res = await axios.get("/api/curfox-cities/orphans");
      this.orphanCount = res.data.orphan_count;
      this.showOrphanModal = true;
    },
    async deleteOrphans() {
      await axios.delete("/api/curfox-cities/orphans");
      this.showOrphanModal = false;
      await this.fetchStats();
    },
    closeModal() {
      this.showSyncModal = false;
    },
    closeAutoModal() {
      this.showAutoModal = false;
    },
    closeOrphanModal() {
      this.showOrphanModal = false;
    },
    async manualMatch(c) {
      await axios.post("/api/curfox-cities/manual-match", {
        curfox_city_id: c.curfox_city_id,
        system_city_id: this.selected[c.curfox_city_id],
      });
      await this.fetchStats();
      if (this.activeTab) await this.fetchList(this.list.current_page || 1);
    },
    async createAndMatch(c) {
      await axios.post("/api/curfox-cities/create-and-match", {
        curfox_city_id: c.curfox_city_id,
      });
      await this.fetchStats();
      if (this.activeTab) await this.fetchList(this.list.current_page || 1);
    },
  },
  mounted() {
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
