<template>
  <div class="p-6 w-full">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Settings</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Seller Registrations</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
        Review seller signups and filter by status.
      </p>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <div class="flex flex-col gap-4 border-b border-slate-200/70 p-4 dark:border-slate-800/70 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">
            <i class="fas fa-store"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Seller Manager</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">Search and filter registrations</p>
          </div>
        </div>

        <form class="w-full lg:w-auto" @submit.prevent>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative">
              <button
                id="status-dropdown-button"
                type="button"
                @click="showStatusDropdown = !showStatusDropdown"
                class="inline-flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
              >
                {{ selectedStatusLabel }}
                <svg class="h-2.5 w-2.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                </svg>
              </button>

              <div
                v-if="showStatusDropdown"
                class="absolute z-20 mt-2 w-48 rounded-xl border border-slate-200 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-900"
              >
                <ul class="text-sm text-slate-700 dark:text-slate-200">
                  <li>
                    <button @click="selectStatus('')" type="button" class="inline-flex w-full rounded-lg px-3 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-800">
                      All Statuses
                    </button>
                  </li>
                  <li v-for="status in statuses" :key="status">
                    <button @click="selectStatus(status)" type="button" class="inline-flex w-full rounded-lg px-3 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-800">
                      {{ formatStatus(status) }}
                    </button>
                  </li>
                </ul>
              </div>
            </div>

            <div class="relative w-full sm:w-80">
              <input
                v-model="search"
                @input="debounceSearch"
                type="search"
                placeholder="Search name, email, or phone..."
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-slate-400"
              />
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                <i class="fas fa-search"></i>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="p-4 overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-700 dark:text-slate-200">
          <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="px-3 py-3">Seller</th>
              <th class="px-3 py-3">Email</th>
              <th class="px-3 py-3">Phone</th>
              <th class="px-3 py-3">Type</th>
              <th class="px-3 py-3">Status</th>
              <th class="px-3 py-3">Submitted</th>
              <th class="px-3 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
            <tr v-if="sellers.length === 0">
              <td colspan="7" class="text-center py-6 text-slate-500 dark:text-slate-400">No seller registrations found</td>
            </tr>

            <tr v-for="seller in sellers" :key="seller.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
              <td class="px-3 py-4 font-medium text-slate-900 dark:text-white">
                {{ seller.first_name }} {{ seller.last_name }}
              </td>
              <td class="px-3 py-4">{{ seller.email }}</td>
              <td class="px-3 py-4">{{ seller.phone || '-' }}</td>
              <td class="px-3 py-4 capitalize">{{ seller.seller_type }}</td>
              <td class="px-3 py-4">
                <span :class="statusClass(seller.status)" class="text-[0.65rem] font-semibold px-2.5 py-1 rounded-full uppercase tracking-wide">
                  {{ seller.status }}
                </span>
              </td>
              <td class="px-3 py-4">{{ formatDate(seller.created_at) }}</td>
              <td class="px-3 py-4 text-right">
                <button
                  @click="openSellerDrawer(seller.id)"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                  title="View"
                >
                  <i class="fas fa-eye"></i>
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

    <transition name="fade">
      <div v-if="showSellerDrawer" class="fixed inset-0 z-[1300] flex justify-end bg-slate-900/60 backdrop-blur-sm">
        <div class="h-full w-full max-w-xl overflow-y-auto border-l border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Seller Details</h3>
            <button @click="closeSellerDrawer" class="h-9 w-9 rounded-full border border-slate-200 text-slate-500 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white">
              ✖
            </button>
          </div>

          <div v-if="loadingSellerDetail" class="py-10 text-center text-slate-500 dark:text-slate-300">
            Loading seller details...
          </div>

          <div v-else-if="selectedSeller" class="space-y-5 text-sm">
            <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
              <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Basic Details</h4>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div><span class="text-slate-500">First Name:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.first_name }}</span></div>
                <div><span class="text-slate-500">Last Name:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.last_name }}</span></div>
                <div><span class="text-slate-500">Email:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.email }}</span></div>
                <div><span class="text-slate-500">Phone:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.phone || '-' }}</span></div>
                <div><span class="text-slate-500">Seller Type:</span> <span class="text-slate-900 dark:text-white capitalize">{{ selectedSeller.seller_type }}</span></div>
                <div>
                  <span class="text-slate-500">Status:</span>
                  <span :class="statusClass(selectedSeller.status)" class="ml-2 rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide">
                    {{ selectedSeller.status }}
                  </span>
                </div>
                <div><span class="text-slate-500">Tax Number:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.tax_number || '-' }}</span></div>
                <div><span class="text-slate-500">NIC Number:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.nic_number || '-' }}</span></div>
                <div><span class="text-slate-500">Email Verified:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.email_verified ? 'Yes' : 'No' }}</span></div>
                <div><span class="text-slate-500">Phone Verified:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.phone_verified ? 'Yes' : 'No' }}</span></div>
                <div><span class="text-slate-500">Agreement Accepted:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.agreement_accepted ? 'Yes' : 'No' }}</span></div>
                <div><span class="text-slate-500">Linked User ID:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.user_id || '-' }}</span></div>
              </div>
            </div>

            <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
              <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Documents</h4>
              <div class="space-y-2">
                <div>
                  <span class="text-slate-500">NIC Front:</span>
                  <a v-if="selectedSeller.nic_front" :href="storageUrl(selectedSeller.nic_front)" target="_blank" class="ml-2 text-sky-600 hover:underline dark:text-sky-300">View document</a>
                  <span v-else class="ml-2 text-slate-900 dark:text-white">-</span>
                </div>
                <div>
                  <span class="text-slate-500">NIC Back:</span>
                  <a v-if="selectedSeller.nic_back" :href="storageUrl(selectedSeller.nic_back)" target="_blank" class="ml-2 text-sky-600 hover:underline dark:text-sky-300">View document</a>
                  <span v-else class="ml-2 text-slate-900 dark:text-white">-</span>
                </div>
              </div>
            </div>

            <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
              <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Business Information</h4>
              <div v-if="selectedSeller.business_information" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div><span class="text-slate-500">Business Name:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.business_name || '-' }}</span></div>
                <div><span class="text-slate-500">Registration No:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.business_registration_number || '-' }}</span></div>
                <div><span class="text-slate-500">Business Type:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.business_type || '-' }}</span></div>
                <div><span class="text-slate-500">Registered Date:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.business_registered_date || '-' }}</span></div>
                <div><span class="text-slate-500">Address 1:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.address_line_1 || '-' }}</span></div>
                <div><span class="text-slate-500">Address 2:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.address_line_2 || '-' }}</span></div>
                <div><span class="text-slate-500">City:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.city || '-' }}</span></div>
                <div><span class="text-slate-500">District:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.district || '-' }}</span></div>
                <div><span class="text-slate-500">Postal Code:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.postal_code || '-' }}</span></div>
                <div><span class="text-slate-500">Country:</span> <span class="text-slate-900 dark:text-white">{{ selectedSeller.business_information.country || '-' }}</span></div>
                <div class="sm:col-span-2">
                  <span class="text-slate-500">Business Document:</span>
                  <a
                    v-if="selectedSeller.business_information.business_registration_document"
                    :href="storageUrl(selectedSeller.business_information.business_registration_document)"
                    target="_blank"
                    class="ml-2 text-sky-600 hover:underline dark:text-sky-300"
                  >
                    View document
                  </a>
                  <span v-else class="ml-2 text-slate-900 dark:text-white">-</span>
                </div>
              </div>
              <p v-else class="text-slate-500 dark:text-slate-300">No business information available.</p>
            </div>

            <div v-if="selectedSeller.status === 'rejected'" class="rounded-xl border border-rose-200 bg-rose-50/70 p-4 text-rose-700 dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-200">
              <h4 class="mb-1 text-xs font-semibold uppercase tracking-wider">Rejection Reason</h4>
              <p>{{ selectedSeller.rejection_reason || '-' }}</p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
              <button
                v-if="selectedSeller.status === 'pending'"
                @click="openApproveConfirm"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
              >
                Approve
              </button>

              <button
                v-if="selectedSeller.status === 'pending'"
                @click="openRejectReasonModal"
                class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700"
              >
                Reject
              </button>

              <button
                v-if="selectedSeller.status === 'approved'"
                @click="openBlockReasonModal"
                class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700"
              >
                Block
              </button>

              <button
                v-if="selectedSeller.status === 'blocked'"
                @click="openUnblockConfirm"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
              >
                Unblock
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div
        v-if="showRejectModal"
        class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
      >
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Reject Seller</h3>
          <p class="mb-3 text-sm text-slate-600 dark:text-slate-300">Please provide a rejection reason.</p>
          <textarea
            v-model="rejectReason"
            rows="4"
            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            placeholder="Enter rejection reason..."
          ></textarea>

          <div class="mt-5 flex justify-end gap-3">
            <button
              @click="showRejectModal = false"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
            >
              Cancel
            </button>
            <button
              @click="confirmRejectReason"
              class="rounded-xl bg-rose-600 px-4 py-2 text-white hover:bg-rose-700"
            >
              Continue
            </button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div
        v-if="showBlockModal"
        class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
      >
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Block Seller</h3>
          <p class="mb-3 text-sm text-slate-600 dark:text-slate-300">Please provide a block reason.</p>
          <textarea
            v-model="blockReason"
            rows="4"
            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            placeholder="Enter block reason..."
          ></textarea>

          <div class="mt-5 flex justify-end gap-3">
            <button
              @click="showBlockModal = false"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
            >
              Cancel
            </button>
            <button
              @click="confirmBlockReason"
              class="rounded-xl bg-rose-600 px-4 py-2 text-white hover:bg-rose-700"
            >
              Continue
            </button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div
        v-if="showConfirmModal"
        class="fixed inset-0 z-[1600] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
      >
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Confirm Action</h3>
          <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">{{ confirmMessage }}</p>

          <div class="flex justify-end gap-3">
            <button
              @click="showConfirmModal = false"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
              :disabled="actionLoading"
            >
              Cancel
            </button>
            <button
              @click="executeConfirmedAction"
              class="rounded-xl px-4 py-2 text-white"
              :class="confirmAction === 'approve' || confirmAction === 'unblock'
                ? 'bg-emerald-600 hover:bg-emerald-700'
                : 'bg-rose-600 hover:bg-rose-700'"
              :disabled="actionLoading"
            >
              {{ actionLoading ? 'Please wait...' : 'Confirm' }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const sellers = ref([])
const statuses = ref([])
const search = ref('')
const selectedStatus = ref('')
const showStatusDropdown = ref(false)

const showSellerDrawer = ref(false)
const selectedSeller = ref(null)
const loadingSellerDetail = ref(false)

const showRejectModal = ref(false)
const rejectReason = ref('')
const showBlockModal = ref(false)
const blockReason = ref('')

const showConfirmModal = ref(false)
const confirmAction = ref('')
const confirmMessage = ref('')
const actionLoading = ref(false)

const selectedStatusLabel = computed(() => {
  return selectedStatus.value ? formatStatus(selectedStatus.value) : 'All Statuses'
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0,
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

let searchTimeout = null

const fetchSellers = async (page = 1) => {
  try {
    const res = await axios.get('/api/sellers', {
      params: {
        search: search.value,
        status: selectedStatus.value,
        page,
        per_page: pagination.value.per_page,
      },
    })

    sellers.value = res.data.sellers || []
    statuses.value = res.data.statuses || []
    pagination.value = res.data.pagination || pagination.value
  } catch {
    toast.error('Failed to load seller registrations')
  }
}

const fetchSellerDetail = async (sellerId) => {
  try {
    loadingSellerDetail.value = true
    const res = await axios.get(`/api/sellers/${sellerId}`)
    selectedSeller.value = res.data
  } catch {
    toast.error('Failed to load seller details')
    showSellerDrawer.value = false
  } finally {
    loadingSellerDetail.value = false
  }
}

const openSellerDrawer = async (sellerId) => {
  showSellerDrawer.value = true
  await fetchSellerDetail(sellerId)
}

const closeSellerDrawer = () => {
  showSellerDrawer.value = false
  selectedSeller.value = null
  rejectReason.value = ''
  blockReason.value = ''
  showBlockModal.value = false
}

const openApproveConfirm = () => {
  confirmAction.value = 'approve'
  confirmMessage.value = 'Are you sure you want to approve this seller? A seller user account will be created and login details will be emailed.'
  showConfirmModal.value = true
}

const openRejectReasonModal = () => {
  rejectReason.value = ''
  showRejectModal.value = true
}

const openBlockReasonModal = () => {
  blockReason.value = ''
  showBlockModal.value = true
}

const openUnblockConfirm = () => {
  confirmAction.value = 'unblock'
  confirmMessage.value = 'Are you sure you want to unblock this seller?'
  showConfirmModal.value = true
}

const confirmRejectReason = () => {
  if (!rejectReason.value.trim()) {
    toast.error('Rejection reason is required')
    return
  }

  showRejectModal.value = false
  confirmAction.value = 'reject'
  confirmMessage.value = 'Are you sure you want to reject this seller registration?'
  showConfirmModal.value = true
}

const confirmBlockReason = () => {
  if (!blockReason.value.trim()) {
    toast.error('Block reason is required')
    return
  }

  showBlockModal.value = false
  confirmAction.value = 'block'
  confirmMessage.value = 'Are you sure you want to block this seller registration?'
  showConfirmModal.value = true
}

const executeConfirmedAction = async () => {
  if (!selectedSeller.value?.id || !confirmAction.value) return

  try {
    actionLoading.value = true

    if (confirmAction.value === 'approve') {
      const res = await axios.post(`/api/sellers/${selectedSeller.value.id}/approve`)
      toast.success(res.data.message || 'Seller approved successfully')
    }

    if (confirmAction.value === 'reject') {
      const res = await axios.post(`/api/sellers/${selectedSeller.value.id}/reject`, {
        reason: rejectReason.value,
      })
      toast.success(res.data.message || 'Seller rejected successfully')
    }

    if (confirmAction.value === 'unblock') {
      const res = await axios.post(`/api/sellers/${selectedSeller.value.id}/unblock`)
      toast.success(res.data.message || 'Seller unblocked successfully')
    }

    if (confirmAction.value === 'block') {
      const res = await axios.post(`/api/sellers/${selectedSeller.value.id}/block`, {
        reason: blockReason.value,
      })
      toast.success(res.data.message || 'Seller blocked successfully')
    }

    showConfirmModal.value = false
    await fetchSellers(pagination.value.current_page)
    await fetchSellerDetail(selectedSeller.value.id)
  } catch (err) {
    const message = err.response?.data?.message || 'Unable to process seller action'
    toast.error(message)
  } finally {
    actionLoading.value = false
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchSellers(1), 400)
}

const selectStatus = (status) => {
  selectedStatus.value = status
  showStatusDropdown.value = false
  fetchSellers(1)
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchSellers(page)
}

const formatStatus = (value) => {
  if (!value) return ''
  return value.charAt(0).toUpperCase() + value.slice(1)
}

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleDateString()
}

const storageUrl = (path) => {
  return `/storage/${path}`
}

const statusClass = (status) => {
  if (status === 'approved') {
    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
  }

  if (status === 'rejected') {
    return 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'
  }

  if (status === 'blocked') {
    return 'bg-slate-200 text-slate-700 dark:bg-slate-500/20 dark:text-slate-200'
  }

  return 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200'
}

const handleClickOutside = (e) => {
  const dropdown = document.getElementById('status-dropdown-button')
  if (dropdown && !dropdown.contains(e.target)) {
    showStatusDropdown.value = false
  }
}

onMounted(() => {
  fetchSellers(1)
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
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
