<template>
  <div class="p-6 w-full">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Sellers</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Seller Profile</h2>
      </div>
      <a href="/active-sellers" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Back to Active Sellers</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]" v-if="seller">
      <aside class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/80">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Profile Image</p>
        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
          <img
            v-if="seller.seller_image"
            :src="storageUrl(seller.seller_image)"
            alt="Seller image"
            class="h-64 w-full object-cover"
          />
          <div v-else class="flex h-64 w-full flex-col items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-500 dark:from-slate-800 dark:to-slate-900 dark:text-slate-300">
            <i class="fas fa-image text-3xl"></i>
            <p class="mt-3 text-sm font-semibold">No image uploaded</p>
          </div>
        </div>

        <div class="mt-4">
          <label class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-black dark:bg-white dark:text-slate-900">
            <i class="fas fa-upload"></i>
            {{ imageUploading ? 'Uploading...' : (seller.seller_image ? 'Update Image' : 'Upload Image') }}
            <input
              type="file"
              class="hidden"
              accept=".jpg,.jpeg,.png,.webp"
              :disabled="imageUploading"
              @change="uploadSellerImage"
            />
          </label>
          <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">JPG, PNG or WEBP up to 5MB.</p>
        </div>
      </aside>

      <section class="space-y-6">
        <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-6 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/80">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Seller Overview</p>
              <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ seller.first_name }} {{ seller.last_name }}</h3>
            </div>
            <span
              class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide"
              :class="statusClass(seller.status)"
            >
              {{ seller.status }}
            </span>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div class="detail-tile">
              <p class="detail-label">Email</p>
              <p class="detail-value">{{ seller.email }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Phone</p>
              <p class="detail-value">{{ seller.phone || '-' }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Seller Type</p>
              <p class="detail-value capitalize">{{ seller.seller_type }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Tax Number</p>
              <p class="detail-value">{{ seller.tax_number || '-' }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">NIC Number</p>
              <p class="detail-value">{{ seller.nic_number || '-' }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Starting Level</p>
              <p class="detail-value">{{ seller.level?.level_name || '-' }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Points</p>
              <p class="detail-value">{{ seller.points ?? 0 }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Linked User ID</p>
              <p class="detail-value">{{ seller.user_id || '-' }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-6 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/80">
          <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Business Information</h3>
          <div v-if="seller.business_information" class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="detail-tile">
              <p class="detail-label">Business Name</p>
              <p class="detail-value">{{ seller.business_information.business_name || '-' }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Business Reg No</p>
              <p class="detail-value">{{ seller.business_information.business_registration_number || '-' }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Business Type</p>
              <p class="detail-value">{{ seller.business_information.business_type || '-' }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">City</p>
              <p class="detail-value">{{ seller.business_information.city || '-' }}</p>
            </div>
            <div class="detail-tile sm:col-span-2">
              <p class="detail-label">Address</p>
              <p class="detail-value">
                {{ seller.business_information.address_line_1 || '-' }}
                <span v-if="seller.business_information.address_line_2">, {{ seller.business_information.address_line_2 }}</span>
              </p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Country</p>
              <p class="detail-value">{{ seller.business_information.country || '-' }}</p>
            </div>
            <div class="detail-tile">
              <p class="detail-label">Postal Code</p>
              <p class="detail-value">{{ seller.business_information.postal_code || '-' }}</p>
            </div>
          </div>
          <p v-else class="mt-3 text-sm text-slate-500 dark:text-slate-300">No business information available.</p>
        </div>
      </section>
    </div>

    <div v-else class="rounded-2xl border border-slate-200/70 bg-white/80 p-6 text-slate-500 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-300">
      Loading seller profile...
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
  sellerId: {
    type: Number,
    required: true,
  },
})

const toast = useToast()
const seller = ref(null)
const imageUploading = ref(false)

const fetchSeller = async () => {
  try {
    const res = await axios.get(`/api/sellers/${props.sellerId}`)
    seller.value = res.data
  } catch {
    toast.error('Failed to load seller profile')
  }
}

const uploadSellerImage = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  const formData = new FormData()
  formData.append('seller_image', file)

  try {
    imageUploading.value = true
    const res = await axios.post(`/api/sellers/${props.sellerId}/image`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    seller.value = res.data.seller
    toast.success(res.data.message || 'Seller image uploaded successfully')
  } catch (err) {
    if (err.response?.status === 422) {
      Object.values(err.response.data.errors || {}).flat().forEach((msg) => toast.error(msg))
    } else {
      toast.error(err.response?.data?.message || 'Failed to upload seller image')
    }
  } finally {
    imageUploading.value = false
    event.target.value = ''
  }
}

const storageUrl = (path) => `/storage/${path}`

const statusClass = (status) => {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
  if (status === 'rejected') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'
  if (status === 'blocked') return 'bg-slate-200 text-slate-700 dark:bg-slate-500/20 dark:text-slate-200'
  return 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200'
}

onMounted(() => {
  fetchSeller()
})
</script>

<style scoped>
.detail-tile {
  @apply rounded-xl border border-slate-200 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/60;
}

.detail-label {
  @apply text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400;
}

.detail-value {
  @apply mt-1 text-sm font-semibold text-slate-900 dark:text-white;
}
</style>
