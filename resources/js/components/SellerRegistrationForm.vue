<template>
  <div class="seller-form-shell overflow-hidden rounded-[2rem] shadow-slate-900/10 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
    <div class="mx-auto w-full">
      <div class="grid gap-8 lg:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/80 p-6 shadow-xl shadow-slate-900/5 backdrop-blur">
          <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-600 dark:text-sky-300">Seller Registration</p>
          <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Become a nextepSellers Partner</h1>
          <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Complete the steps below to submit your seller onboarding request.</p>

          <div class="mt-7 space-y-3">
            <div
              v-for="item in steps"
              :key="item.id"
              class="rounded-2xl border p-3 transition"
              :class="step >= item.id ? 'border-sky-300 dark:border-sky-500/40 bg-sky-50 dark:bg-sky-500/10' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
            >
              <p class="text-xs font-semibold uppercase tracking-wide" :class="step >= item.id ? 'text-sky-700 dark:text-sky-300' : 'text-slate-500 dark:text-slate-400'">
                Step {{ item.id }}
              </p>
              <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ item.label }}</p>
            </div>
          </div>
        </aside>

        <div class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xl shadow-slate-900/5 sm:p-8">
          <p
            v-if="successMessage"
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
          >
            {{ successMessage }}
          </p>

          <p
            v-if="errors.verification"
            class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"
          >
            {{ errors.verification[0] }}
          </p>
          <p
            v-if="errors.general"
            class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"
          >
            {{ errors.general[0] }}
          </p>

          <div v-if="step === 1" class="space-y-5">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">General Information</h2>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">First Name</label>
                <input v-model="form.first_name" type="text" class="input" placeholder="Enter first name" autocomplete="given-name" />
                <p v-if="errors.first_name" class="error-text">{{ errors.first_name[0] }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Last Name</label>
                <input v-model="form.last_name" type="text" class="input" placeholder="Enter last name" autocomplete="family-name" />
                <p v-if="errors.last_name" class="error-text">{{ errors.last_name[0] }}</p>
              </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Email</label>
                <div class="flex gap-2">
                  <input v-model="form.email" type="email" class="input flex-1" placeholder="name@example.com" autocomplete="email" />
                  <button type="button" class="verify-btn" :class="form.email_verified ? 'verified' : ''" @click="verifyEmail">
                    {{ form.email_verified ? 'Verified' : 'Verify' }}
                  </button>
                </div>
                <p v-if="errors.email" class="error-text">{{ errors.email[0] }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Phone</label>
                <div class="flex gap-2">
                  <input v-model="form.phone" type="text" class="input flex-1" placeholder="+94 77 123 4567" autocomplete="tel" />
                  <button type="button" class="verify-btn" :class="form.phone_verified ? 'verified' : ''" @click="verifyPhone">
                    {{ form.phone_verified ? 'Verified' : 'Verify' }}
                  </button>
                </div>
                <p v-if="errors.phone" class="error-text">{{ errors.phone[0] }}</p>
              </div>
            </div>

            <p class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-medium text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200">
              To continue to the next step, verifying at least one contact method (email or mobile) is mandatory.
            </p>

            <div class="flex justify-end">
              <button type="button" class="next-btn" @click="goToStepTwo">Continue</button>
            </div>
          </div>

          <div v-if="step === 2" class="space-y-5">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Detailed Information</h2>

            <div>
              <p class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Registration Type</p>
              <div class="grid gap-3 sm:grid-cols-2">
                <button
                  type="button"
                  class="option-card"
                  :class="form.seller_type === 'individual' ? 'option-card-active' : ''"
                  @click="form.seller_type = 'individual'"
                >
                  <span class="text-base font-semibold text-slate-900 dark:text-white">Individual</span>
                  <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Register as a single user.</span>
                </button>

                <button
                  type="button"
                  class="option-card"
                  :class="form.seller_type === 'business' ? 'option-card-active' : ''"
                  @click="form.seller_type = 'business'"
                >
                  <span class="text-base font-semibold text-slate-900 dark:text-white">Business</span>
                  <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Register with company information.</span>
                </button>
              </div>
              <p v-if="errors.seller_type" class="error-text">{{ errors.seller_type[0] }}</p>
            </div>

            <div v-if="form.seller_type === 'business'" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 p-4">
              <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Business Information</p>
              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Business Name</label>
                  <input v-model="form.business_name" type="text" class="input" placeholder="Business legal name" />
                  <p v-if="errors.business_name" class="error-text">{{ errors.business_name[0] }}</p>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Registration Number</label>
                  <input v-model="form.business_registration_number" type="text" class="input" placeholder="Registration number" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Business Type</label>
                  <input v-model="form.business_type" type="text" class="input" placeholder="e.g. Sole Proprietorship" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Registered Date</label>
                  <input v-model="form.business_registered_date" type="date" class="input" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Address Line 1</label>
                  <input v-model="form.address_line_1" type="text" class="input" placeholder="Street and number" />
                  <p v-if="errors.address_line_1" class="error-text">{{ errors.address_line_1[0] }}</p>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Address Line 2</label>
                  <input v-model="form.address_line_2" type="text" class="input" placeholder="Apartment, suite, etc. (optional)" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">City</label>
                  <input v-model="form.city" type="text" class="input" placeholder="City" />
                  <p v-if="errors.city" class="error-text">{{ errors.city[0] }}</p>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">District</label>
                  <input v-model="form.district" type="text" class="input" placeholder="District" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Postal Code</label>
                  <input v-model="form.postal_code" type="text" class="input" placeholder="Postal code" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Country</label>
                  <input v-model="form.country" type="text" class="input" placeholder="Country" />
                </div>
              </div>
            </div>

            <div v-if="form.seller_type === 'individual'" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 p-4">
              <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Individual Information</p>
              <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                You are registering as an individual. Personal tax and NIC details below are required.
              </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Tax Number</label>
                <input v-model="form.tax_number" type="text" class="input" placeholder="Tax number" />
                <p v-if="errors.tax_number" class="error-text">{{ errors.tax_number[0] }}</p>
              </div>
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">NIC Number</label>
                <input v-model="form.nic_number" type="text" class="input" placeholder="NIC number" />
                <p v-if="errors.nic_number" class="error-text">{{ errors.nic_number[0] }}</p>
              </div>
            </div>

            <div class="flex justify-between gap-3">
              <button type="button" class="back-btn" @click="step = 1">Back</button>
              <button type="button" class="next-btn" @click="goToStepThree">Continue</button>
            </div>
          </div>

          <div v-if="step === 3" class="space-y-5">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Uploads & Agreement</h2>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">NIC Front</label>
                <input type="file" class="file-input" accept=".jpg,.jpeg,.png,.pdf" @change="onFileChange($event, 'nic_front')" />
                <p v-if="errors.nic_front" class="error-text">{{ errors.nic_front[0] }}</p>
              </div>
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">NIC Back</label>
                <input type="file" class="file-input" accept=".jpg,.jpeg,.png,.pdf" @change="onFileChange($event, 'nic_back')" />
                <p v-if="errors.nic_back" class="error-text">{{ errors.nic_back[0] }}</p>
              </div>
            </div>

            <div v-if="form.seller_type === 'business'">
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Business Registration Document</label>
              <input
                type="file"
                class="file-input"
                accept=".jpg,.jpeg,.png,.pdf"
                @change="onFileChange($event, 'business_registration_document')"
              />
              <p v-if="errors.business_registration_document" class="error-text">{{ errors.business_registration_document[0] }}</p>
            </div>

            <div class="rounded-2xl border border-sky-200 dark:border-slate-700 bg-sky-50 dark:bg-slate-900/60 p-4">
              <p class="text-sm font-semibold text-sky-900 dark:text-white">Seller Agreement</p>
              <p class="mt-2 text-sm leading-6 text-sky-800 dark:text-slate-300">
                By submitting this form, you confirm that all provided information is accurate, documents are valid, and
                nextepSellers may review and verify your submission for onboarding compliance.
              </p>

              <label class="mt-4 flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 p-3">
                <input v-model="form.agreement_accepted" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300" />
                <span class="text-sm text-slate-700 dark:text-slate-300">I agree to the Seller Agreement and consent to verification.</span>
              </label>
              <p v-if="errors.agreement_accepted" class="error-text">{{ errors.agreement_accepted[0] }}</p>
            </div>

            <div class="flex justify-between gap-3">
              <button type="button" class="back-btn" @click="step = 2">Back</button>
              <button type="button" class="next-btn" :disabled="submitting" @click="submitForm">
                {{ submitting ? 'Submitting...' : 'Submit Registration' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showEmailOtpModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/70 px-4">
      <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-2xl">
        <div class="mb-4 flex items-start justify-between">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Verify Email</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
              Enter the 6-digit code sent to <span class="font-semibold">{{ form.email }}</span>.
            </p>
          </div>
          <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="showEmailOtpModal = false">
            ✕
          </button>
        </div>

        <p v-if="emailOtpMessage" class="mb-3 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-700">
          {{ emailOtpMessage }}
        </p>

        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Verification Code</label>
        <input
          v-model="emailOtpCode"
          type="text"
          maxlength="6"
          class="input otp-input"
          placeholder="Enter 6-digit OTP"
        />
        <p v-if="emailOtpError" class="error-text">{{ emailOtpError }}</p>

        <div class="mt-4 flex justify-end gap-2">
          <button type="button" class="back-btn" :disabled="emailOtpSending" @click="verifyEmail">
            {{ emailOtpSending ? 'Sending...' : 'Resend Code' }}
          </button>
          <button type="button" class="next-btn" :disabled="emailOtpVerifying" @click="confirmEmailOtp">
            {{ emailOtpVerifying ? 'Verifying...' : 'Verify Email' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'

const props = defineProps({
  submitUrl: {
    type: String,
    required: true,
  },
  emailOtpSendUrl: {
    type: String,
    required: true,
  },
  emailOtpVerifyUrl: {
    type: String,
    required: true,
  },
  csrfToken: {
    type: String,
    required: true,
  },
})

const step = ref(1)
const submitting = ref(false)
const successMessage = ref('')
const errors = ref({})
const showEmailOtpModal = ref(false)
const emailOtpCode = ref('')
const emailOtpSending = ref(false)
const emailOtpVerifying = ref(false)
const emailOtpMessage = ref('')
const emailOtpError = ref('')

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  email_verified: false,
  phone_verified: false,
  seller_type: 'individual',
  tax_number: '',
  nic_number: '',

  business_name: '',
  business_registration_number: '',
  business_type: '',
  business_registered_date: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  district: '',
  postal_code: '',
  country: 'Sri Lanka',

  nic_front: null,
  nic_back: null,
  business_registration_document: null,
  agreement_accepted: false,
})

const steps = computed(() => [
  { id: 1, label: 'General Information' },
  { id: 2, label: 'Detailed Information' },
  { id: 3, label: 'Uploads & Agreement' },
])

watch(() => form.email, () => {
  form.email_verified = false
  emailOtpCode.value = ''
  emailOtpError.value = ''
  emailOtpMessage.value = ''
})

watch(() => form.phone, () => {
  form.phone_verified = false
})

function resetErrors() {
  errors.value = {}
}

async function verifyEmail() {
  resetErrors()
  emailOtpError.value = ''
  emailOtpMessage.value = ''

  if (!form.email || !/^\S+@\S+\.\S+$/.test(form.email)) {
    errors.value.email = ['Enter a valid email before verifying.']
    return
  }

  emailOtpSending.value = true

  try {
    const response = await window.axios.post(props.emailOtpSendUrl, {
      _token: props.csrfToken,
      email: form.email,
      first_name: form.first_name,
    })

    emailOtpMessage.value = response.data.message || 'Verification code sent to your email.'
    showEmailOtpModal.value = true
  } catch (error) {
    if (error.response?.status === 422) {
      const message = error.response?.data?.message || 'Unable to send OTP.'
      errors.value.email = [message]
      return
    }
    errors.value.email = ['Failed to send verification code. Please try again.']
  } finally {
    emailOtpSending.value = false
  }
}

function verifyPhone() {
  resetErrors()
  if (!form.phone || form.phone.length < 7) {
    errors.value.phone = ['Enter a valid phone before verifying.']
    return
  }
  form.phone_verified = true
}

function goToStepTwo() {
  resetErrors()

  if (!form.first_name) errors.value.first_name = ['First name is required.']
  if (!form.last_name) errors.value.last_name = ['Last name is required.']
  if (!form.email || !/^\S+@\S+\.\S+$/.test(form.email)) errors.value.email = ['A valid email is required.']

  if (!form.email_verified && !form.phone_verified) {
    errors.value.verification = ['Verify at least one of email or phone before continuing.']
  }

  if (Object.keys(errors.value).length) return
  step.value = 2
}

function goToStepThree() {
  resetErrors()

  if (!form.seller_type) errors.value.seller_type = ['Select registration type.']

  if (form.seller_type === 'business') {
    if (!form.business_name) errors.value.business_name = ['Business name is required.']
    if (!form.address_line_1) errors.value.address_line_1 = ['Address line 1 is required.']
    if (!form.city) errors.value.city = ['City is required.']
  }

  if (Object.keys(errors.value).length) return
  step.value = 3
}

function onFileChange(event, key) {
  const [file] = event.target.files || []
  form[key] = file || null
}

async function confirmEmailOtp() {
  emailOtpError.value = ''

  if (!/^\d{6}$/.test(emailOtpCode.value)) {
    emailOtpError.value = 'Enter a valid 6-digit code.'
    return
  }

  emailOtpVerifying.value = true

  try {
    const response = await window.axios.post(props.emailOtpVerifyUrl, {
      _token: props.csrfToken,
      email: form.email,
      otp: emailOtpCode.value,
    })

    form.email_verified = true
    showEmailOtpModal.value = false
    emailOtpCode.value = ''
    emailOtpMessage.value = response.data.message || 'Email verified successfully.'
  } catch (error) {
    if (error.response?.status === 422) {
      emailOtpError.value = error.response?.data?.message || 'Invalid verification code.'
      return
    }
    emailOtpError.value = 'Unable to verify code right now. Please try again.'
  } finally {
    emailOtpVerifying.value = false
  }
}

function buildPayload() {
  const payload = new FormData()
  payload.append('_token', props.csrfToken)

  Object.entries(form).forEach(([key, value]) => {
    if (value === null || value === undefined) return

    if (typeof value === 'boolean') {
      payload.append(key, value ? '1' : '0')
      return
    }

    payload.append(key, value)
  })

  return payload
}

function resetForm() {
  Object.assign(form, {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    email_verified: false,
    phone_verified: false,
    seller_type: 'individual',
    tax_number: '',
    nic_number: '',
    business_name: '',
    business_registration_number: '',
    business_type: '',
    business_registered_date: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    district: '',
    postal_code: '',
    country: 'Sri Lanka',
    nic_front: null,
    nic_back: null,
    business_registration_document: null,
    agreement_accepted: false,
  })
  showEmailOtpModal.value = false
  emailOtpCode.value = ''
  emailOtpMessage.value = ''
  emailOtpError.value = ''
}

async function submitForm() {
  resetErrors()

  if (!form.nic_front) errors.value.nic_front = ['NIC front is required.']
  if (!form.nic_back) errors.value.nic_back = ['NIC back is required.']
  if (form.seller_type === 'business' && !form.business_registration_document) {
    errors.value.business_registration_document = ['Business registration document is required for business registration.']
  }
  if (!form.agreement_accepted) errors.value.agreement_accepted = ['You must accept the seller agreement.']

  if (Object.keys(errors.value).length) return

  submitting.value = true

  try {
    const response = await window.axios.post(props.submitUrl, buildPayload(), {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    successMessage.value = response.data.message || 'Seller registration submitted successfully.'
    resetForm()
    step.value = 1
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {
        general: ['Validation failed. Please review your form.'],
      }
      return
    }

    errors.value = {
      general: ['Something went wrong while submitting your registration. Please try again.'],
    }
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.seller-form-shell {
  --input-bg: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  --input-border: #cbd5e1;
  --input-border-hover: #94a3b8;
  --input-text: #0f172a;
  --input-placeholder: #94a3b8;
  --focus-ring: rgba(14, 165, 233, 0.18);
  --focus-border: #0ea5e9;
  --input-shadow: 0 1px 0 rgba(15, 23, 42, 0.02), inset 0 1px 0 rgba(255, 255, 255, 0.4);
  --file-bg: #f8fafc;
  --file-border: #94a3b8;
  --file-text: #334155;
  --file-button-bg: #ffffff;
  --file-button-border: #cbd5e1;
  --file-button-text: #0f172a;
}

.input {
  width: 100%;
  border-radius: 0.9rem;
  border: 1px solid var(--input-border);
  background: var(--input-bg);
  padding: 0.72rem 0.86rem;
  font-size: 0.92rem;
  color: var(--input-text);
  box-shadow: var(--input-shadow);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, transform 0.16s ease;
}

.input::placeholder {
  color: var(--input-placeholder);
}

.input:hover {
  border-color: var(--input-border-hover);
}

.input:focus {
  outline: none;
  border-color: var(--focus-border);
  box-shadow: 0 0 0 3px var(--focus-ring), var(--input-shadow);
  transform: translateY(-1px);
}

.input:disabled {
  cursor: not-allowed;
  opacity: 0.65;
  filter: grayscale(0.1);
}

.otp-input {
  text-align: center;
  letter-spacing: 0.32em;
  font-weight: 700;
  font-size: 1.04rem;
}

.file-input {
  width: 100%;
  border-radius: 0.9rem;
  border: 1px dashed var(--file-border);
  background: var(--file-bg);
  padding: 0.6rem 0.7rem;
  font-size: 0.88rem;
  color: var(--file-text);
  transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
}

.file-input:hover {
  border-color: var(--focus-border);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--focus-ring) 70%, transparent);
}

.file-input::file-selector-button {
  border: 1px solid var(--file-button-border);
  background: var(--file-button-bg);
  color: var(--file-button-text);
  border-radius: 0.7rem;
  padding: 0.45rem 0.7rem;
  margin-right: 0.65rem;
  font-weight: 700;
  cursor: pointer;
  transition: border-color 0.2s ease, background-color 0.2s ease;
}

.verify-btn,
.next-btn,
.back-btn {
  border-radius: 0.9rem;
  padding: 0.55rem 0.95rem;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  transition: transform 0.16s ease, box-shadow 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
}

.verify-btn:hover,
.next-btn:hover,
.back-btn:hover {
  transform: translateY(-1px);
}

.verify-btn:focus-visible,
.next-btn:focus-visible,
.back-btn:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px var(--focus-ring);
}

.verify-btn {
  border: 1px solid #0ea5e9;
  color: #0369a1;
  background: #f0f9ff;
}

.verify-btn.verified {
  border-color: #059669;
  background: #ecfdf5;
  color: #047857;
}

.next-btn {
  border: 1px solid #0ea5e9;
  background: #0ea5e9;
  color: #ffffff;
  box-shadow: 0 8px 18px rgba(14, 165, 233, 0.28);
}

.next-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.back-btn {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #334155;
}

.option-card {
  border-radius: 0.9rem;
  border: 1px solid #d6dbe3;
  background: #fff;
  text-align: left;
  padding: 0.95rem;
  transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.16s ease;
}

.option-card:hover {
  transform: translateY(-1px);
}

.option-card-active {
  border-color: #0ea5e9;
  background: #f0f9ff;
}

.error-text {
  margin-top: 0.3rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: #dc2626;
}

:global(.dark) .input {
  border-color: #334155;
  background: #0f172a;
  color: #e2e8f0;
}

:global(.dark) .file-input {
  border-color: #334155;
  background: #0b1220;
  color: #cbd5e1;
}

:global(.dark) .input::placeholder {
  color: #64748b;
}

:global(.dark) .input:hover {
  border-color: #475569;
}

:global(.dark) .input:focus {
  border-color: #38bdf8;
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2), var(--input-shadow);
  background: #0b1220;
}

:global(.dark) .file-input:hover {
  border-color: #38bdf8;
  background: #0f172a;
}

:global(.dark) .file-input::file-selector-button {
  border-color: #334155;
  background: #0f172a;
  color: #e2e8f0;
}

:global(.dark) .verify-btn {
  border-color: #334155;
  background: #0f172a;
  color: #cbd5e1;
}

:global(.dark) .verify-btn.verified {
  border-color: #10b981;
  background: rgba(16, 185, 129, 0.12);
  color: #6ee7b7;
}

:global(.dark) .next-btn {
  border-color: #e2e8f0;
  background: #e2e8f0;
  color: #0f172a;
}

:global(.dark) .back-btn {
  border-color: #334155;
  background: #0f172a;
  color: #cbd5e1;
}

:global(.dark) .option-card {
  border-color: #334155;
  background: #0f172a;
}

:global(.dark) .option-card-active {
  border-color: rgba(14, 165, 233, 0.5);
  background: rgba(14, 165, 233, 0.12);
}

:global(body.theme-comfort) .seller-form-shell {
  --input-bg: linear-gradient(180deg, #f9f2e1 0%, #f3ebd9 100%);
  --input-border: #d2c5a7;
  --input-border-hover: #bfae86;
  --input-text: #586e75;
  --input-placeholder: #8a816e;
  --focus-ring: rgba(174, 147, 101, 0.2);
  --focus-border: #ae9365;
  --input-shadow: 0 1px 0 rgba(124, 111, 100, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.38);
  --file-bg: #f5eedc;
  --file-border: #ccbda0;
  --file-text: #586e75;
  --file-button-bg: #fdf7ea;
  --file-button-border: #ccbda0;
  --file-button-text: #5f5345;
}

:global(body.theme-comfort) .option-card,
:global(body.theme-comfort) .back-btn {
  background: #f7f0dd;
  border-color: #d6cdb6;
  color: #586e75;
}

:global(body.theme-comfort) .option-card-active {
  border-color: #ae9365;
  background: #efe5cf;
}

:global(body.theme-comfort) .next-btn {
  background: #c7a777;
  border-color: #c7a777;
  color: #23303a;
  box-shadow: 0 8px 18px rgba(143, 122, 88, 0.28);
}

:global(body.theme-comfort) .verify-btn {
  background: #ede1c8;
  border-color: #c7a777;
  color: #6f5e4b;
}

:global(body.theme-comfort) .file-input::file-selector-button {
  background: #fdf7ea;
  border-color: #ccbda0;
  color: #5f5345;
}
</style>
