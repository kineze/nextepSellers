<template>
  <section class="space-y-4 rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
    <div>
      <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Profile</p>
      <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">Profile Manager</h2>
    </div>

    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-800">
      <button
        type="button"
        class="rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wide transition"
        :class="activeTab === 'profile' ? 'bg-blue-700 text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800'"
        @click="activeTab = 'profile'"
      >
        Profile Details
      </button>
      <button
        type="button"
        class="rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wide transition"
        :class="activeTab === 'bank' ? 'bg-blue-700 text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800'"
        @click="activeTab = 'bank'"
      >
        Bank Details
      </button>
    </div>

    <form v-if="activeTab === 'profile'" class="space-y-4" @submit.prevent="saveProfile">
      <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">Profile Image</p>
        <div class="mt-3 flex flex-wrap items-center gap-4">
          <div class="h-20 w-20 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <img v-if="previewImage" :src="previewImage" alt="Seller profile" class="h-full w-full object-cover" />
            <div v-else class="flex h-full w-full items-center justify-center text-slate-400">
              <i class="fas fa-user"></i>
            </div>
          </div>

          <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            <i class="fas fa-upload"></i>
            Change Photo
            <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp" @change="onImageSelected" :disabled="loading || savingProfile" />
          </label>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2">
        <label class="space-y-1">
          <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">First Name</span>
          <input v-model.trim="form.first_name" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Last Name</span>
          <input v-model.trim="form.last_name" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
        </label>
      </div>

      <div class="grid gap-3 sm:grid-cols-2">
        <label class="space-y-1">
          <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Email</span>
          <div class="space-y-2">
            <input v-model.trim="form.email" type="email" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
            <div class="flex items-center gap-2">
              <button
                v-if="isEmailChanged && !emailVerifiedForCurrent"
                type="button"
                class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-amber-800 hover:bg-amber-100"
                :disabled="emailOtpSending || loading || savingProfile"
                @click="sendEmailOtp"
              >
                {{ emailOtpSending ? 'Sending...' : 'Verify New Email' }}
              </button>
              <span v-if="isEmailChanged && emailVerifiedForCurrent" class="text-xs font-semibold text-emerald-600">New email verified</span>
            </div>
          </div>
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Phone</span>
          <input v-model.trim="form.phone" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
        </label>
      </div>

      <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">Business Details</p>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Business details are managed by admin and cannot be changed here.</p>
      </div>

      <div class="flex items-center justify-end gap-2">
        <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="loadProfile" :disabled="loading || savingProfile">Reset</button>
        <button type="submit" class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60" :disabled="loading || savingProfile">{{ savingProfile ? 'Saving...' : 'Save Profile' }}</button>
      </div>
    </form>

    <form v-else class="space-y-4" @submit.prevent="saveBankDetails">
      <div class="grid gap-3 sm:grid-cols-2">
        <label class="space-y-1">
          <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Bank</span>
          <select v-model="bankForm.bank_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option :value="null">Select a bank</option>
            <option v-for="bank in bankOptions" :key="bank.id" :value="bank.id">{{ bank.name }}</option>
          </select>
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Account Number</span>
          <input v-model.trim="bankForm.account_no" type="text" placeholder="Example: 1234567890" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Account Name</span>
          <input v-model.trim="bankForm.name" type="text" placeholder="Example: John Doe" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
        </label>
        <label class="space-y-1">
          <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Branch</span>
          <input v-model.trim="bankForm.branch" type="text" placeholder="Example: Colombo Main Branch" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
        </label>
        <label class="space-y-1 sm:col-span-2">
          <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">SWIFT Code</span>
          <input v-model.trim="bankForm.swift_code" type="text" placeholder="Example: CCEYLKLX" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
        </label>
      </div>

      <div class="flex items-center justify-end gap-2">
        <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="resetBankForm" :disabled="loading || savingBank">Reset</button>
        <button type="submit" class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60" :disabled="loading || savingBank">{{ savingBank ? 'Saving...' : 'Save Bank Details' }}</button>
      </div>
    </form>
  </section>

  <div v-if="showEmailOtpModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/70 px-4">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Verify New Email</h3>
        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="showEmailOtpModal = false">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <p class="mb-3 text-sm text-slate-600 dark:text-slate-300">Enter the 6-digit code sent to {{ form.email }}.</p>
      <input
        v-model.trim="emailOtpCode"
        type="text"
        maxlength="6"
        inputmode="numeric"
        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm tracking-[0.2em] text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
        placeholder="123456"
      />

      <div class="mt-4 flex items-center justify-end gap-2">
        <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" :disabled="emailOtpSending" @click="sendEmailOtp">
          {{ emailOtpSending ? 'Sending...' : 'Resend' }}
        </button>
        <button type="button" class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60" :disabled="emailOtpVerifying" @click="verifyEmailOtp">
          {{ emailOtpVerifying ? 'Verifying...' : 'Verify Email' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const activeTab = ref('profile')
const loading = ref(false)
const savingProfile = ref(false)
const savingBank = ref(false)
const bankOptions = ref([])

const showEmailOtpModal = ref(false)
const emailOtpCode = ref('')
const emailOtpSending = ref(false)
const emailOtpVerifying = ref(false)
const originalEmail = ref('')
const emailVerifiedForCurrent = ref(true)
const selectedImageFile = ref(null)
const previewImage = ref('')
const originalBank = ref({
  bank_id: null,
  account_no: '',
  swift_code: '',
  name: '',
  branch: '',
})
let objectUrl = null

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  seller_image: '',
})

const bankForm = reactive({
  bank_id: null,
  account_no: '',
  swift_code: '',
  name: '',
  branch: '',
})

const isEmailChanged = computed(() => normalizeEmail(form.email) !== normalizeEmail(originalEmail.value))

watch(
  () => form.email,
  () => {
    emailVerifiedForCurrent.value = !isEmailChanged.value
  }
)

const normalizeEmail = (value) => String(value || '').trim().toLowerCase()
const sellerImageUrl = (path) => (path ? `/storage/${path}` : '')

const clearObjectUrl = () => {
  if (objectUrl) {
    URL.revokeObjectURL(objectUrl)
    objectUrl = null
  }
}

const syncPreviewImage = () => {
  clearObjectUrl()
  if (selectedImageFile.value) {
    objectUrl = URL.createObjectURL(selectedImageFile.value)
    previewImage.value = objectUrl
    return
  }
  previewImage.value = sellerImageUrl(form.seller_image)
}

const applyBankDetails = (bankDetail) => {
  bankForm.bank_id = bankDetail?.bank_id || bankDetail?.bank?.id || null
  bankForm.account_no = bankDetail?.account_no || ''
  bankForm.swift_code = bankDetail?.swift_code || ''
  bankForm.name = bankDetail?.name || ''
  bankForm.branch = bankDetail?.branch || ''

  originalBank.value = {
    bank_id: bankForm.bank_id,
    account_no: bankForm.account_no,
    swift_code: bankForm.swift_code,
    name: bankForm.name,
    branch: bankForm.branch,
  }
}

const loadBanks = async () => {
  try {
    const { data } = await axios.get('/api/seller/profile/banks')
    bankOptions.value = Array.isArray(data?.banks) ? data.banks : []
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load banks.')
  }
}

const applySeller = (seller) => {
  form.first_name = seller?.first_name || ''
  form.last_name = seller?.last_name || ''
  form.email = seller?.email || ''
  form.phone = seller?.phone || ''
  form.seller_image = seller?.seller_image || ''

  applyBankDetails(seller?.bank_detail || null)

  originalEmail.value = form.email
  emailVerifiedForCurrent.value = true
  emailOtpCode.value = ''
  selectedImageFile.value = null
  syncPreviewImage()
}

const resetBankForm = () => {
  applyBankDetails(originalBank.value)
}

const loadProfile = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/seller/profile')
    applySeller(data?.seller || null)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load profile.')
  } finally {
    loading.value = false
  }
}

const onImageSelected = (event) => {
  const file = event.target.files?.[0]
  selectedImageFile.value = file || null
  syncPreviewImage()
  event.target.value = ''
}

const sendEmailOtp = async () => {
  if (!isEmailChanged.value) {
    toast.error('Change the email first.')
    return
  }

  emailOtpSending.value = true
  try {
    const { data } = await axios.post('/api/seller/profile/email-otp/send', { email: form.email })
    showEmailOtpModal.value = true
    toast.success(data?.message || 'Verification code sent.')
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Unable to send verification code.')
  } finally {
    emailOtpSending.value = false
  }
}

const verifyEmailOtp = async () => {
  if (!/^\d{6}$/.test(emailOtpCode.value)) {
    toast.error('Enter a valid 6-digit OTP.')
    return
  }

  emailOtpVerifying.value = true
  try {
    const { data } = await axios.post('/api/seller/profile/email-otp/verify', {
      email: form.email,
      otp: emailOtpCode.value,
    })
    emailVerifiedForCurrent.value = true
    showEmailOtpModal.value = false
    toast.success(data?.message || 'Email verified successfully.')
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Unable to verify code.')
  } finally {
    emailOtpVerifying.value = false
  }
}

const saveProfile = async () => {
  if (isEmailChanged.value && !emailVerifiedForCurrent.value) {
    toast.error('Verify the new email before saving profile changes.')
    return
  }

  savingProfile.value = true
  try {
    const payload = new FormData()
    payload.append('_method', 'PUT')
    payload.append('first_name', form.first_name)
    payload.append('last_name', form.last_name)
    payload.append('email', form.email)
    payload.append('phone', form.phone || '')

    if (selectedImageFile.value) {
      payload.append('seller_image', selectedImageFile.value)
    }

    const { data } = await axios.post('/api/seller/profile', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    applySeller(data?.seller || null)
    toast.success(data?.message || 'Profile updated successfully.')
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to update profile.')
  } finally {
    savingProfile.value = false
  }
}

const saveBankDetails = async () => {
  savingBank.value = true
  try {
    const { data } = await axios.put('/api/seller/profile/bank-details', {
      bank_id: bankForm.bank_id,
      account_no: bankForm.account_no,
      swift_code: bankForm.swift_code || null,
      name: bankForm.name,
      branch: bankForm.branch || null,
    })

    applyBankDetails(data?.bank_detail || null)
    toast.success(data?.message || 'Bank details updated successfully.')
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to update bank details.')
  } finally {
    savingBank.value = false
  }
}

onMounted(() => {
  loadBanks()
  loadProfile()
})

onBeforeUnmount(() => {
  clearObjectUrl()
})
</script>
