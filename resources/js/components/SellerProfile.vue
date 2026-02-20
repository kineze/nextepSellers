<template>
  <div class="p-6 w-full">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Sellers</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Seller Profile</h2>
      </div>
      <a href="/active-sellers" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Back to Active Sellers</a>
    </div>

    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70" v-if="seller">
      <div class="grid gap-4 sm:grid-cols-2">
        <p><span class="text-slate-500">Name:</span> <span class="text-slate-900 dark:text-white">{{ seller.first_name }} {{ seller.last_name }}</span></p>
        <p><span class="text-slate-500">Email:</span> <span class="text-slate-900 dark:text-white">{{ seller.email }}</span></p>
        <p><span class="text-slate-500">Phone:</span> <span class="text-slate-900 dark:text-white">{{ seller.phone || '-' }}</span></p>
        <p><span class="text-slate-500">Status:</span> <span class="text-slate-900 dark:text-white capitalize">{{ seller.status }}</span></p>
        <p><span class="text-slate-500">Seller Type:</span> <span class="text-slate-900 dark:text-white capitalize">{{ seller.seller_type }}</span></p>
        <p><span class="text-slate-500">Tax Number:</span> <span class="text-slate-900 dark:text-white">{{ seller.tax_number || '-' }}</span></p>
      </div>

      <div class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-700">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Business Information</h3>
        <div v-if="seller.business_information" class="mt-3 grid gap-4 sm:grid-cols-2">
          <p><span class="text-slate-500">Business Name:</span> <span class="text-slate-900 dark:text-white">{{ seller.business_information.business_name || '-' }}</span></p>
          <p><span class="text-slate-500">Business Reg No:</span> <span class="text-slate-900 dark:text-white">{{ seller.business_information.business_registration_number || '-' }}</span></p>
          <p><span class="text-slate-500">City:</span> <span class="text-slate-900 dark:text-white">{{ seller.business_information.city || '-' }}</span></p>
          <p><span class="text-slate-500">Country:</span> <span class="text-slate-900 dark:text-white">{{ seller.business_information.country || '-' }}</span></p>
        </div>
        <p v-else class="mt-3 text-sm text-slate-500 dark:text-slate-300">No business information available.</p>
      </div>
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

const fetchSeller = async () => {
  try {
    const res = await axios.get(`/api/sellers/${props.sellerId}`)
    seller.value = res.data
  } catch {
    toast.error('Failed to load seller profile')
  }
}

onMounted(() => {
  fetchSeller()
})
</script>
