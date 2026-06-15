<template>
  <div class="w-full p-3">
    <div class="mb-6">
      <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">System Configuration</p>
      <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">System Data</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Maintain the single company profile used across system documents and settings.</p>
    </div>

    <form
      @submit.prevent="save"
      class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70"
    >
      <div class="flex flex-col gap-3 border-b border-slate-200/70 p-4 dark:border-slate-800/70 md:flex-row md:items-center md:justify-between">
        <div>
          <h3 class="text-base font-semibold text-slate-900 dark:text-white">Company Details</h3>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
            {{ systemDataId ? 'Editing the active system record.' : 'Create the active system record.' }}
          </p>
        </div>
        <button
          type="submit"
          :disabled="loading || saving"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-900"
        >
          <i class="fas fa-save"></i>
          {{ saving ? 'Saving...' : 'Save System Data' }}
        </button>
      </div>

      <div v-if="loading" class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
        Loading system data...
      </div>

      <div v-else class="grid gap-4 p-4 lg:grid-cols-2">
        <section class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
          <div class="mb-4">
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Company General Details</h4>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Company, address, and contact details used across documents.</p>
          </div>

          <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Company Name</label>
              <input
                v-model="form.company_name"
                type="text"
                required
                placeholder="Company name"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
              />
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Address</label>
              <textarea
                v-model="form.address"
                rows="4"
                placeholder="Company address"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
              ></textarea>
            </div>

            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Country</label>
              <input
                v-model="form.country"
                type="text"
                required
                placeholder="Country"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
              />
            </div>

            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Phone Number</label>
              <input
                v-model="form.phone_number"
                type="text"
                placeholder="Phone number"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
              />
            </div>

            <div>
              <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Fax</label>
              <input
                v-model="form.fax"
                type="text"
                placeholder="Fax number"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
              />
            </div>
          </div>
        </section>

        <div class="grid gap-4">
          <section class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <div class="mb-4">
              <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Date Configuration</h4>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Fiscal year and quarter boundaries for reports.</p>
            </div>

            <div class="grid gap-5">
              <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Year Start Month</label>
                <select
                  v-model.number="form.year_start_month"
                  class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                >
                  <option v-for="month in months" :key="month.value" :value="month.value">
                    {{ month.label }}
                  </option>
                </select>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  Year end month is automatically set to {{ yearEndMonthLabel }}.
                </p>
              </div>

              <div class="overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                <table class="w-full text-left text-sm">
                  <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/70 dark:text-slate-400">
                    <tr>
                      <th class="px-3 py-2">Quarter</th>
                      <th class="px-3 py-2">Start Date</th>
                      <th class="px-3 py-2">End Date</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-200 text-slate-700 dark:divide-slate-700 dark:text-slate-200">
                    <tr v-for="quarter in quarterDates" :key="quarter.label">
                      <td class="px-3 py-2 font-semibold text-slate-900 dark:text-white">{{ quarter.label }}</td>
                      <td class="px-3 py-2">{{ quarter.startDate }}</td>
                      <td class="px-3 py-2">{{ quarter.endDate }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <div class="mb-4">
              <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Media</h4>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Brand mark for invoices and exports.</p>
            </div>

            <div>
              <div class="flex aspect-[3/2] items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/70">
                <img v-if="logoPreview" :src="logoPreview" alt="Logo preview" class="max-h-full max-w-full object-contain" />
                <span v-else class="text-sm text-slate-400">No logo selected</span>
              </div>
              <input
                type="file"
                accept="image/jpeg,image/png,image/webp"
                @change="onLogoChange"
                class="mt-4 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-wide file:text-white hover:file:bg-black dark:text-slate-300 dark:file:bg-white dark:file:text-slate-900"
              />
              <p class="mt-2 text-xs text-slate-400">JPG, PNG, or WEBP up to 2MB.</p>
            </div>
          </section>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const saving = ref(false)
const systemDataId = ref(null)
const months = [
  { value: 1, label: 'January' },
  { value: 2, label: 'February' },
  { value: 3, label: 'March' },
  { value: 4, label: 'April' },
  { value: 5, label: 'May' },
  { value: 6, label: 'June' },
  { value: 7, label: 'July' },
  { value: 8, label: 'August' },
  { value: 9, label: 'September' },
  { value: 10, label: 'October' },
  { value: 11, label: 'November' },
  { value: 12, label: 'December' },
]
const monthEndDays = {
  1: '31',
  2: '28/29',
  3: '31',
  4: '30',
  5: '31',
  6: '30',
  7: '31',
  8: '31',
  9: '30',
  10: '31',
  11: '30',
  12: '31',
}
const blankForm = () => ({ company_name: '', address: '', country: 'Sri Lanka', phone_number: '', fax: '', year_start_month: 1, logo: null })
const form = ref(blankForm())
const logoPreview = ref('')
const currentLogoUrl = ref('')
let logoObjectUrl = ''

const yearEndMonth = () => {
  const startMonth = Number(form.value.year_start_month) || 1
  return startMonth === 1 ? 12 : startMonth - 1
}

const monthLabel = (value) => months.find((month) => month.value === value)?.label || ''
const yearEndMonthLabel = computed(() => monthLabel(yearEndMonth()))
const addMonths = (month, amount) => ((month + amount - 1) % 12) + 1
const monthStartDate = (month) => `${monthLabel(month)} 1`
const monthEndDate = (month) => `${monthLabel(month)} ${monthEndDays[month]}`
const quarterDates = computed(() => {
  const startMonth = Number(form.value.year_start_month) || 1

  return [0, 1, 2, 3].map((quarterIndex) => {
    const quarterStartMonth = addMonths(startMonth, quarterIndex * 3)
    const quarterEndMonth = addMonths(quarterStartMonth, 2)

    return {
      label: `Q${quarterIndex + 1}`,
      startDate: monthStartDate(quarterStartMonth),
      endDate: monthEndDate(quarterEndMonth),
    }
  })
})

const fillForm = (item = null) => {
  systemDataId.value = item?.id || null
  form.value = {
    company_name: item?.company_name || '',
    address: item?.address || '',
    country: item?.country || 'Sri Lanka',
    phone_number: item?.phone_number || '',
    fax: item?.fax || '',
    year_start_month: item?.year_start_month || 1,
    logo: null,
  }
  currentLogoUrl.value = item?.logo_url || ''
  logoPreview.value = currentLogoUrl.value
}

const fetchSystemData = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/system-data')
    fillForm(data.data || null)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load system data.')
  } finally {
    loading.value = false
  }
}

const onLogoChange = (event) => {
  const file = event.target.files?.[0] || null
  form.value.logo = file

  if (logoObjectUrl) {
    URL.revokeObjectURL(logoObjectUrl)
    logoObjectUrl = ''
  }

  if (file) {
    logoObjectUrl = URL.createObjectURL(file)
    logoPreview.value = logoObjectUrl
  } else {
    logoPreview.value = currentLogoUrl.value
  }
}

const save = async () => {
  saving.value = true
  try {
    const payload = new FormData()
    payload.append('company_name', form.value.company_name || '')
    payload.append('address', form.value.address || '')
    payload.append('country', form.value.country || '')
    payload.append('phone_number', form.value.phone_number || '')
    payload.append('fax', form.value.fax || '')
    payload.append('year_start_month', form.value.year_start_month || 1)
    payload.append('year_end_month', yearEndMonth())
    if (form.value.logo) {
      payload.append('logo', form.value.logo)
    }

    const { data } = await axios.post('/api/system-data', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    fillForm(data.data || null)
    toast.success('System data saved.')
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to save system data.')
  } finally {
    saving.value = false
  }
}

onMounted(fetchSystemData)
onBeforeUnmount(() => {
  if (logoObjectUrl) {
    URL.revokeObjectURL(logoObjectUrl)
  }
})
</script>
