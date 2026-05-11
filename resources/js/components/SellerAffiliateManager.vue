<template>
  <div class="space-y-6">
    <div
      v-if="error"
      class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"
    >
      {{ error }}
    </div>

    <section class="overflow-hidden rounded-3xl border border-emerald-200/70 bg-gradient-to-br from-emerald-100 via-white to-cyan-100 p-7 shadow-sm dark:border-emerald-500/30 dark:from-emerald-600/20 dark:via-slate-900 dark:to-cyan-600/20">
      <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-emerald-700 dark:text-emerald-300">Seller Affiliate</p>
      <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">Grow by Referring Other Sellers</h1>
      <p class="mt-3 max-w-3xl text-sm text-slate-700 dark:text-slate-200">
        Suggest this platform to other sellers and earn commissions whenever their orders are placed. Share your referral code, track onboarding progress, and monitor expected payouts.
      </p>

      <div class="mt-5 grid gap-3 lg:grid-cols-[1fr_auto]">
        <div class="rounded-2xl border border-emerald-200/70 bg-white/90 p-4 dark:border-emerald-500/30 dark:bg-slate-900/70">
          <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Your Referral Code</p>
          <p class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ seller.referral_code || 'Not generated yet' }}</p>

          <div v-if="referralLink" class="mt-2 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-800/80">
            <p class="min-w-0 flex-1 truncate text-xs text-slate-700 dark:text-slate-200" :title="referralLink">
              {{ referralLink }}
            </p>
            <button
              type="button"
              class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-700"
              title="Copy referral link"
              @click="copyReferralLink"
            >
              <i class="fas fa-copy text-xs" aria-hidden="true"></i>
              <span class="sr-only">Copy referral link</span>
            </button>
          </div>

          <p v-if="copyMessage" class="mt-1 text-xs font-semibold" :class="copyOk ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'">
            {{ copyMessage }}
          </p>

          <p v-if="!referralLink" class="mt-1 break-all text-xs text-slate-600 dark:text-slate-300">
            Generate referral credentials to create your seller invite link.
          </p>
        </div>

        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-400 bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-60 dark:border-emerald-400/60 dark:bg-emerald-500 dark:hover:bg-emerald-400"
          :disabled="generating"
          @click="generateReferral"
        >
          <i class="fas" :class="generating ? 'fa-spinner fa-spin' : seller.referral_code ? 'fa-arrows-rotate' : 'fa-link'" aria-hidden="true"></i>
          {{ generating ? 'Generating...' : seller.referral_code ? 'Regenerate Referral Link & Code' : 'Generate Referral Link & Code' }}
        </button>
      </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatsCard label="Total Referrals" :value="formatNumber(stats.total_referrals)" />
      <StatsCard label="Active Referrals" :value="formatNumber(stats.active_referrals)" tone="blue" />
      <StatsCard label="Available Affiliate Commission" :value="`LKR ${toMoney(stats.available_commission_lkr)}`" tone="violet" />
      <StatsCard
        label="Paid Affiliate Commission"
        :value="`LKR ${toMoney(stats.paid_commission_lkr)}`"
        :subtext="`Total: LKR ${toMoney(stats.total_commission_lkr)}`"
        tone="amber"
      />
    </div>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Referred Sellers</h2>
        <span class="rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:text-slate-300">
          {{ formatNumber(referredSellers.length) }} Linked
        </span>
      </div>

      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead>
            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
              <th class="px-3 py-2">Seller</th>
              <th class="px-3 py-2">Email</th>
              <th class="px-3 py-2">Phone</th>
              <th class="px-3 py-2">Joined</th>
              <th class="px-3 py-2">Status</th>
              <th class="px-3 py-2">Orders</th>
              <th class="px-3 py-2 text-right">Available</th>
              <th class="px-3 py-2 text-right">Paid</th>
              <th class="px-3 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            <tr v-if="loading">
              <td colspan="9" class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Loading affiliate data...</td>
            </tr>
            <tr v-else-if="!referredSellers.length">
              <td colspan="9" class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No sellers are linked to your affiliate account yet.</td>
            </tr>
            <tr v-for="item in referredSellers" :key="item.id" class="text-slate-700 dark:text-slate-200">
              <td class="px-3 py-2.5 font-semibold">{{ item.name || `Seller #${item.id}` }}</td>
              <td class="px-3 py-2.5">{{ item.email || '-' }}</td>
              <td class="px-3 py-2.5">{{ item.phone || '-' }}</td>
              <td class="px-3 py-2.5">{{ formatDate(item.created_at) }}</td>
              <td class="px-3 py-2.5">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="statusClass(item.status)">
                  {{ capitalize(item.status) }}
                </span>
              </td>
              <td class="px-3 py-2.5">{{ formatNumber(item.orders_count) }}</td>
              <td class="px-3 py-2.5 text-right font-semibold text-violet-700 dark:text-violet-300">LKR {{ toMoney(item.affiliate_available_amount) }}</td>
              <td class="px-3 py-2.5 text-right font-semibold text-blue-700 dark:text-blue-300">LKR {{ toMoney(item.affiliate_paid_amount) }}</td>
              <td class="px-3 py-2.5 text-right font-semibold">LKR {{ toMoney(item.affiliate_total_amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const generating = ref(false)
const error = ref('')
const copyMessage = ref('')
const copyOk = ref(true)
const referredSellers = ref([])
const seller = reactive({
  id: null,
  referral_code: '',
  referral_path: '',
})
const stats = reactive({
  total_referrals: 0,
  active_referrals: 0,
  orders_from_referrals: 0,
  available_commission_lkr: 0,
  paid_commission_lkr: 0,
  total_commission_lkr: 0,
})

const StatsCard = defineComponent({
  props: {
    label: { type: String, required: true },
    value: { type: String, required: true },
    subtext: { type: String, default: '' },
    tone: { type: String, default: 'slate' },
  },
  setup(props) {
    const tones = {
      slate: 'border-slate-200/80 bg-white text-slate-900 dark:border-slate-700 dark:bg-slate-900/70 dark:text-white',
      blue: 'border-blue-200/80 bg-blue-50/80 text-blue-900 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-100',
      violet: 'border-violet-200/80 bg-violet-50/80 text-violet-900 dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-100',
      amber: 'border-amber-200/80 bg-amber-50/80 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100',
    }
    const labelTones = {
      slate: 'text-slate-500 dark:text-slate-400',
      blue: 'text-blue-700 dark:text-blue-300',
      violet: 'text-violet-700 dark:text-violet-300',
      amber: 'text-amber-700 dark:text-amber-300',
    }

    return () => h('article', { class: `rounded-2xl border p-4 shadow-sm ${tones[props.tone] || tones.slate}` }, [
      h('p', { class: `text-[11px] font-semibold uppercase tracking-wider ${labelTones[props.tone] || labelTones.slate}` }, props.label),
      h('p', { class: 'mt-2 text-3xl font-extrabold' }, props.value),
      props.subtext ? h('p', { class: 'mt-1 text-[11px] opacity-80' }, props.subtext) : null,
    ])
  },
})

const referralLink = computed(() => {
  if (!seller.referral_path) return ''
  return new URL(seller.referral_path, window.location.origin).href
})

const applyPayload = (data) => {
  seller.id = data?.seller?.id || null
  seller.referral_code = data?.seller?.referral_code || ''
  seller.referral_path = data?.seller?.referral_path || ''
  referredSellers.value = Array.isArray(data?.referred_sellers) ? data.referred_sellers : []

  const incomingStats = data?.stats || {}
  Object.keys(stats).forEach((key) => {
    stats[key] = Number(incomingStats[key] || 0)
  })
}

const fetchAffiliate = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await axios.get('/api/seller/affiliate')
    applyPayload(data)
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to load affiliate data.'
    toast.error(error.value)
  } finally {
    loading.value = false
  }
}

const generateReferral = async () => {
  generating.value = true
  error.value = ''
  try {
    const { data } = await axios.post('/api/seller/affiliate/generate')
    seller.id = data?.seller?.id || seller.id
    seller.referral_code = data?.seller?.referral_code || ''
    seller.referral_path = data?.seller?.referral_path || ''
    toast.success(data?.message || 'Referral link generated.')
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to generate referral link.'
    toast.error(error.value)
  } finally {
    generating.value = false
  }
}

const copyReferralLink = async () => {
  const link = referralLink.value
  if (!link) return
  copyMessage.value = ''
  copyOk.value = true

  const fallbackCopy = () => {
    const temp = document.createElement('textarea')
    temp.value = link
    temp.setAttribute('readonly', '')
    temp.style.position = 'fixed'
    temp.style.left = '-9999px'
    temp.style.top = '0'
    document.body.appendChild(temp)
    temp.focus()
    temp.select()
    temp.setSelectionRange(0, temp.value.length)
    const copied = document.execCommand('copy')
    document.body.removeChild(temp)
    return copied
  }

  try {
    const copied = fallbackCopy()
    if (!copied && navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(link)
    }
    copyMessage.value = 'Copied full link'
    toast.success('Referral link copied.')
  } catch (err) {
    copyOk.value = false
    copyMessage.value = 'Copy failed'
    toast.error('Copy failed. Please select and copy the link manually.')
  } finally {
    window.setTimeout(() => {
      copyMessage.value = ''
    }, 1800)
  }
}

const toMoney = (value) => Number(value || 0).toFixed(2)
const formatNumber = (value) => Number(value || 0).toLocaleString()
const capitalize = (value) => {
  const text = String(value || '-')
  return text.charAt(0).toUpperCase() + text.slice(1)
}

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toISOString().slice(0, 10)
}

const statusClass = (status) => {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
  if (status === 'pending') return 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200'
  if (status === 'blocked') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
}

onMounted(fetchAffiliate)
</script>
