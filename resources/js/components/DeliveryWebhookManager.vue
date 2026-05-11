<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">Webhooks and API</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Delivery Webhook</h1>
        <p class="mt-2 max-w-3xl text-sm text-slate-600 dark:text-slate-300">
          Configure the delivery partner webhook and review incoming waybill status payloads.
        </p>
      </div>

      <button
        type="button"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-900"
        :disabled="loading"
        @click="loadAll"
      >
        <i class="fas fa-rotate" :class="{ 'fa-spin': loading }" aria-hidden="true"></i>
        Refresh
      </button>
    </div>

    <div class="mt-5 grid gap-4 lg:grid-cols-3">
      <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700 lg:col-span-2">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Webhook Configuration</h2>
          <span
            class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide"
            :class="configuration.key_configured ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'"
          >
            {{ configuration.key_configured ? 'Key Configured' : 'Key Missing' }}
          </span>
        </div>

        <dl class="mt-4 grid gap-3 text-sm">
          <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Webhook URL</dt>
            <dd class="mt-1 flex flex-col gap-2 rounded-lg bg-slate-50 p-3 font-mono text-xs text-slate-800 dark:bg-slate-950 dark:text-slate-200 sm:flex-row sm:items-center sm:justify-between">
              <span class="break-all">{{ configuration.webhook_url || '-' }}</span>
              <button type="button" class="shrink-0 text-indigo-600 hover:text-indigo-800 dark:text-indigo-300" @click="copyText(configuration.webhook_url)">
                <i class="fas fa-copy" aria-hidden="true"></i>
                <span class="sr-only">Copy webhook URL</span>
              </button>
            </dd>
          </div>
          <div class="grid gap-3 md:grid-cols-2">
            <div>
              <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Method</dt>
              <dd class="mt-1 rounded-lg bg-slate-50 p-3 font-mono text-xs text-slate-800 dark:bg-slate-950 dark:text-slate-200">{{ configuration.method || 'POST' }}</dd>
            </div>
            <div>
              <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Header</dt>
              <dd class="mt-1 flex items-center justify-between gap-2 rounded-lg bg-slate-50 p-3 font-mono text-xs text-slate-800 dark:bg-slate-950 dark:text-slate-200">
                <span>{{ configuration.header_name || 'X-Webhook-Key' }}</span>
                <button type="button" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-300" @click="copyText(configuration.header_name)">
                  <i class="fas fa-copy" aria-hidden="true"></i>
                  <span class="sr-only">Copy header name</span>
                </button>
              </dd>
            </div>
          </div>
        </dl>
      </div>

      <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Required Payload</h2>
        <div class="mt-3 rounded-lg bg-slate-950 p-3 text-xs text-slate-100">
          <pre class="whitespace-pre-wrap">{{ samplePayload }}</pre>
        </div>
        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Set the secret value in the server environment as DELIVERY_WEBHOOK_KEY.</p>
      </div>
    </div>

    <div class="mt-5 rounded-xl border border-slate-200 dark:border-slate-700">
      <div class="flex flex-col gap-3 border-b border-slate-200 p-4 dark:border-slate-700 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Webhook Logs</h2>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Latest received delivery webhook data.</p>
        </div>
        <div class="relative w-full lg:w-80">
          <input
            v-model="search"
            type="search"
            placeholder="Search waybill or status..."
            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 pr-9 text-sm text-slate-900 outline-none focus:border-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            @input="debouncedFetch"
          />
          <i class="fas fa-search pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
            <tr>
              <th class="px-3 py-3 text-left">Time</th>
              <th class="px-3 py-3 text-left">Waybill</th>
              <th class="px-3 py-3 text-left">Status Key</th>
              <th class="px-3 py-3 text-left">Status</th>
              <th class="px-3 py-3 text-left">Raw Data</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <tr v-if="loading">
              <td colspan="5" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading webhook logs...</td>
            </tr>
            <tr v-else-if="!logs.length">
              <td colspan="5" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No webhook logs found.</td>
            </tr>
            <tr v-for="log in logs" :key="log.id" class="bg-white dark:bg-slate-900/40">
              <td class="px-3 py-3 align-top text-xs text-slate-500 dark:text-slate-400">{{ formatDate(log.created_at) }}</td>
              <td class="px-3 py-3 align-top font-mono text-xs font-semibold text-slate-900 dark:text-white">{{ log.waybill_no || '-' }}</td>
              <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">{{ log.status_key || '-' }}</td>
              <td class="px-3 py-3 align-top text-slate-700 dark:text-slate-200">{{ log.status || '-' }}</td>
              <td class="px-3 py-3 align-top">
                <details class="max-w-xl">
                  <summary class="cursor-pointer text-xs font-semibold text-indigo-600 dark:text-indigo-300">View payload</summary>
                  <pre class="mt-2 max-h-56 overflow-auto rounded-lg bg-slate-950 p-3 text-xs text-slate-100">{{ formatJson(log.raw_data) }}</pre>
                </details>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t border-slate-200 px-4 py-4 text-sm text-slate-600 dark:border-slate-700 dark:text-slate-300">
        <div>
          Showing <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.from || 0 }}</span>
          to <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.to || 0 }}</span>
          of <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.total || 0 }}</span>
        </div>
        <div class="flex items-center gap-2">
          <button class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:hover:bg-slate-800" :disabled="pagination.current_page <= 1" @click="fetchLogs(pagination.current_page - 1)">Prev</button>
          <button class="rounded-lg border border-slate-200 px-3 py-1.5 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:hover:bg-slate-800" :disabled="pagination.current_page >= pagination.last_page" @click="fetchLogs(pagination.current_page + 1)">Next</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const logs = ref([])
const search = ref('')
const pagination = reactive({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const configuration = reactive({
  webhook_url: '',
  method: 'POST',
  header_name: 'X-Webhook-Key',
  key_configured: false,
  sample_payload: {},
})
let searchTimeout = null

const samplePayload = computed(() => JSON.stringify(configuration.sample_payload || {}, null, 2))

const fetchConfiguration = async () => {
  const { data } = await axios.get('/api/webhooks/delivery/configuration')
  configuration.webhook_url = data?.webhook_url || ''
  configuration.method = data?.method || 'POST'
  configuration.header_name = data?.header_name || 'X-Webhook-Key'
  configuration.key_configured = Boolean(data?.key_configured)
  configuration.sample_payload = data?.sample_payload || {}
}

const fetchLogs = async (page = 1) => {
  const { data } = await axios.get('/api/webhooks/delivery/logs', {
    params: {
      page,
      search: search.value || undefined,
    },
  })
  logs.value = data?.data || []
  pagination.current_page = data?.current_page || 1
  pagination.last_page = data?.last_page || 1
  pagination.total = data?.total || 0
  pagination.from = data?.from || 0
  pagination.to = data?.to || 0
}

const loadAll = async () => {
  loading.value = true
  try {
    await Promise.all([fetchConfiguration(), fetchLogs(pagination.current_page || 1)])
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load delivery webhook data.')
  } finally {
    loading.value = false
  }
}

const debouncedFetch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loading.value = true
    fetchLogs(1)
      .catch((error) => toast.error(error?.response?.data?.message || 'Failed to load webhook logs.'))
      .finally(() => {
        loading.value = false
      })
  }, 300)
}

const copyText = async (text) => {
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
    toast.success('Copied.')
  } catch (error) {
    toast.error('Clipboard permission denied.')
  }
}

const formatJson = (value) => JSON.stringify(value || {}, null, 2)

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

onMounted(loadAll)
</script>
