<template>
  <div class="p-6 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Sellers</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Active Sellers</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Search by seller name or phone and manage active accounts.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-4 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
            <i class="fas fa-user-check"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Active Seller Directory</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">View details and block sellers</p>
          </div>
        </div>

        <div class="relative w-full sm:w-80">
          <input
            v-model="search"
            @input="debounceSearch"
            type="search"
            placeholder="Search by name or phone..."
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          />
          <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
            <i class="fas fa-search"></i>
          </div>
        </div>
      </div>

      <div class="p-4 overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">Seller</th>
              <th class="px-3 py-3">Phone</th>
              <th class="px-3 py-3">Email</th>
              <th class="px-3 py-3">Type</th>
              <th class="px-3 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
            <tr v-if="sellers.length === 0">
              <td colspan="5" class="py-6 text-center text-slate-500 dark:text-slate-400">No active sellers found</td>
            </tr>

            <tr v-for="seller in sellers" :key="seller.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4 font-medium text-slate-900 dark:text-white">{{ seller.first_name }} {{ seller.last_name }}</td>
              <td class="px-3 py-4">{{ seller.phone || '-' }}</td>
              <td class="px-3 py-4">{{ seller.email }}</td>
              <td class="px-3 py-4 capitalize">{{ seller.seller_type }}</td>
              <td class="px-3 py-4 text-right space-x-2">
                <button @click="openDetail(seller.id)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700" title="View details">
                  <i class="fas fa-eye"></i>
                </button>
                <a :href="`/active-sellers/${seller.id}/profile`" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-sky-500 transition hover:bg-sky-100 hover:text-sky-700 dark:hover:bg-sky-900/30" title="View seller profile">
                  <i class="fas fa-user"></i>
                </a>
                <button @click="openBlockModal(seller.id)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-100 hover:text-rose-700 dark:hover:bg-rose-900/30" title="Block seller">
                  <i class="fas fa-user-slash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200/70 px-4 py-4 text-sm text-slate-600 dark:border-slate-800/70 dark:text-slate-300 sm:flex-row sm:items-center sm:justify-between">
        <div>
          Showing <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.from || 0 }}</span>
          to <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.to || 0 }}</span>
          of <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.total || 0 }}</span>
        </div>

        <div class="flex items-center gap-2">
          <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">Prev</button>
          <button class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">Next</button>
        </div>
      </div>
    </div>

    <transition name="fade">
      <div v-if="showDetails" class="fixed inset-0 z-[1400] bg-slate-900/60 backdrop-blur-sm flex justify-end">
        <div class="h-full w-full max-w-xl overflow-y-auto border-l border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Seller Details</h3>
            <button @click="showDetails = false" class="h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300">✖</button>
          </div>

          <div v-if="selectedSeller" class="space-y-2 text-sm">
            <p><span class="text-slate-500">Name:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.first_name }} {{ selectedSeller.last_name }}</span></p>
            <p><span class="text-slate-500">Email:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.email }}</span></p>
            <p><span class="text-slate-500">Phone:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.phone || '-' }}</span></p>
            <p><span class="text-slate-500">Seller Type:</span> <span class="text-slate-900 dark:text-white capitalize">{{ selectedSeller.seller_type }}</span></p>
            <p><span class="text-slate-500">Tax Number:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.tax_number || '-' }}</span></p>
            <p><span class="text-slate-500">NIC Number:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.nic_number || '-' }}</span></p>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div v-if="showBlockModal" class="fixed inset-0 z-[1500] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Block Seller</h3>
          <p class="mb-3 text-sm text-slate-600 dark:text-slate-300">Enter a reason for blocking this seller.</p>
          <textarea v-model="blockReason" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white" placeholder="Enter reason..."></textarea>

          <div class="mt-4 flex justify-end gap-3">
            <button @click="showBlockModal = false" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Cancel</button>
            <button @click="confirmBlock" class="px-4 py-2 rounded-xl bg-rose-600 text-white hover:bg-rose-700">Block</button>
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
const sellers = ref([])
const search = ref('')
const selectedSeller = ref(null)
const showDetails = ref(false)
const showBlockModal = ref(false)
const blockSellerId = ref(null)
const blockReason = ref('')
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 })

let searchTimeout = null

const fetchSellers = async (page = 1) => {
  try {
    const res = await axios.get('/api/active-sellers', { params: { search: search.value, page, per_page: pagination.value.per_page } })
    sellers.value = res.data.sellers || []
    pagination.value = res.data.pagination || pagination.value
  } catch {
    toast.error('Failed to load active sellers')
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchSellers(1), 350)
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchSellers(page)
}

const openDetail = async (id) => {
  try {
    const res = await axios.get(`/api/sellers/${id}`)
    selectedSeller.value = res.data
    showDetails.value = true
  } catch {
    toast.error('Failed to load seller details')
  }
}

const openBlockModal = (sellerId) => {
  blockSellerId.value = sellerId
  blockReason.value = ''
  showBlockModal.value = true
}

const confirmBlock = async () => {
  if (!blockReason.value.trim()) {
    toast.error('Block reason is required')
    return
  }

  try {
    const res = await axios.post(`/api/active-sellers/${blockSellerId.value}/block`, { reason: blockReason.value })
    toast.success(res.data.message || 'Seller blocked successfully')
    showBlockModal.value = false
    fetchSellers(pagination.value.current_page)
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to block seller')
  }
}

onMounted(() => {
  fetchSellers(1)
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
