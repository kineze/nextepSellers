<template>
  <section class="mx-3 mt-3 mb-8 rounded-3xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/85">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-cyan-600 dark:text-cyan-300">Orders</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Dispatch Notes</h1>
      </div>
      <admin-global-filter-bar
        context-key="admin-dispatch-notes"
        @filters-changed="onGlobalFiltersChanged"
      />
    </div>

    <div class="mt-4 flex items-center justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} dispatch notes</p>
      <div class="flex items-center gap-2">
        <select v-model="filters.status" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200" @change="applyStatusFilter">
          <option value="all">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="partial_shipped">Partial Shipped</option>
          <option value="shipped">Shipped</option>
        </select>
        <button type="button" class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-700" @click="openShippingSidebar">
          Start Shipping
        </button>
        <button type="button" class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900" @click="fetchNotes">Refresh</button>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200/80 bg-white/80 dark:border-slate-700 dark:bg-slate-900/70">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50/90 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/90 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">Ref</th>
            <th class="px-3 py-3 text-left">Date</th>
            <th class="px-3 py-3 text-left">Orders</th>
            <th class="px-3 py-3 text-right">Collectable</th>
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-left">Created By</th>
            <th class="px-3 py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="7" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading dispatch notes...</td>
          </tr>
          <tr v-else-if="!notes.length">
            <td colspan="7" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No dispatch notes found.</td>
          </tr>
          <tr v-for="note in notes" :key="note.id" class="bg-white/70 transition hover:bg-slate-50 dark:bg-slate-900/40 dark:hover:bg-slate-800/70">
            <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">{{ note.ref_no || `#${note.id}` }}</td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
              {{ formatDate(note.dispatch_date) }}
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ note.dispatch_time || '-' }}</p>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ note.shipped_count }}/{{ note.orders_count }}</td>
            <td class="px-3 py-3 text-right font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(note.total_collectable) }}</td>
            <td class="px-3 py-3">
              <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="statusClass(note.status)">{{ note.status || '-' }}</span>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ note.creator?.name || '-' }}</td>
            <td class="px-3 py-3 text-right">
              <a :href="`/admin/orders/dispatch-notes/${note.id}`" class="rounded-lg border border-blue-300 bg-blue-50 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-blue-700 hover:bg-blue-100 dark:border-blue-700/40 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/30">Open Page</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
      <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" :disabled="meta.current_page <= 1 || loading" @click="changePage(meta.current_page - 1)">Previous</button>

      <div class="flex flex-wrap items-center justify-center gap-1.5">
        <button
          v-for="entry in pageEntries"
          :key="`p-${entry.key}`"
          type="button"
          class="min-w-9 rounded-lg border px-2 py-1.5 text-xs font-semibold transition"
          :class="entry.type === 'ellipsis'
            ? 'cursor-default border-transparent text-slate-400'
            : Number(entry.value) === Number(meta.current_page)
              ? 'border-blue-600 bg-blue-600 text-white'
              : 'border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800'"
          :disabled="loading || entry.type === 'ellipsis'"
          @click="entry.type === 'page' ? changePage(Number(entry.value)) : null"
        >
          {{ entry.label }}
        </button>
      </div>

      <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" :disabled="meta.current_page >= meta.last_page || loading" @click="changePage(meta.current_page + 1)">Next</button>
    </div>

    <div v-if="shippingSidebarOpen" class="fixed inset-0 z-[1400] flex justify-end bg-black/55">
      <div class="flex h-full w-full max-w-xl flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">
          <div class="flex items-center justify-between gap-2">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">Shipping</p>
              <h3 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">Waybill Scan Module</h3>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Scan any waybill/order id. It will resolve dispatch note automatically.</p>
            </div>
            <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="closeShippingSidebar">
              Close
            </button>
          </div>

          <div class="mt-3 space-y-2">
            <input
              ref="shippingScanInputRef"
              v-model.trim="shippingScanInput"
              type="text"
              placeholder="Scan waybill / order id and press Enter"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @keyup.enter="scanAndShip"
            />
            <p v-if="shippingScanMessage" class="text-xs font-medium text-slate-600 dark:text-slate-300">{{ shippingScanMessage }}</p>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="!shippingLogs.length" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">
            No scans yet.
          </div>

          <div v-else class="space-y-2">
            <article
              v-for="log in shippingLogs"
              :key="log.id"
              class="rounded-xl border p-3"
              :class="log.type === 'success'
                ? 'border-emerald-300 bg-emerald-50/70 dark:border-emerald-700/60 dark:bg-emerald-900/20'
                : 'border-rose-300 bg-rose-50/70 dark:border-rose-700/60 dark:bg-rose-900/20'"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ log.title }}</p>
                  <p class="text-xs text-slate-600 dark:text-slate-300">{{ log.message }}</p>
                </div>
                <span class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ log.at }}</span>
              </div>
            </article>
          </div>
        </div>

        <div class="border-t border-slate-200 px-5 py-3 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-xs text-slate-500 dark:text-slate-400">Scanned {{ shippingLogs.length }} result(s)</p>
            <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="clearShippingLogs">
              Clear Logs
            </button>
          </div>
        </div>
      </div>
    </div>

  </section>
</template>

<script setup>
import { computed, nextTick, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const notes = ref([])
const shippingSidebarOpen = ref(false)
const shippingScanInputRef = ref(null)
const shippingScanInput = ref('')
const shippingScanMessage = ref('')
const shippingInProgress = ref(false)
const shippingLogs = ref([])

const filters = reactive({
  search: '',
  status: 'all',
  date_from: '',
  date_to: '',
  page: 1,
  per_page: 20,
})

const meta = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 20,
})

const pageEntries = computed(() => {
  const current = Number(meta.current_page || 1)
  const last = Number(meta.last_page || 1)
  if (last <= 1) return [{ key: '1', type: 'page', value: 1, label: '1' }]

  const windowStart = Math.max(1, current - 2)
  const windowEnd = Math.min(last, current + 2)
  const entries = []

  const pushPage = (value) => {
    entries.push({ key: `page-${value}`, type: 'page', value, label: String(value) })
  }
  const pushEllipsis = (key) => {
    entries.push({ key: `ellipsis-${key}`, type: 'ellipsis', value: null, label: '...' })
  }

  pushPage(1)
  if (windowStart > 2) pushEllipsis('left')
  for (let page = windowStart; page <= windowEnd; page++) {
    if (page !== 1 && page !== last) pushPage(page)
  }
  if (windowEnd < last - 1) pushEllipsis('right')
  if (last !== 1) pushPage(last)

  return entries
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return date.toLocaleDateString()
}

const statusClass = (status) => {
  const s = String(status || '').toLowerCase()
  if (s === 'shipped') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
  if (s === 'partial_shipped') return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'
}

const fetchNotes = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/dispatch-notes', {
      params: {
        search: filters.search || undefined,
        status: filters.status || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        page: filters.page,
        per_page: filters.per_page,
      },
    })

    notes.value = Array.isArray(data?.notes) ? data.notes : []
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.total = Number(data?.meta?.total || 0)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load dispatch notes.')
  } finally {
    loading.value = false
  }
}

const onGlobalFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.page = 1
  fetchNotes()
}

const applyStatusFilter = () => {
  filters.page = 1
  fetchNotes()
}

const changePage = (page) => {
  filters.page = page
  fetchNotes()
}

const addShippingLog = (type, title, message) => {
  shippingLogs.value.unshift({
    id: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
    type,
    title,
    message,
    at: new Date().toLocaleTimeString(),
  })
}

const openShippingSidebar = async () => {
  shippingSidebarOpen.value = true
  shippingScanInput.value = ''
  shippingScanMessage.value = ''
  await nextTick()
  shippingScanInputRef.value?.focus()
}

const closeShippingSidebar = () => {
  shippingSidebarOpen.value = false
}

const clearShippingLogs = () => {
  shippingLogs.value = []
}

const scanAndShip = async () => {
  const code = String(shippingScanInput.value || '').trim()
  if (!code || shippingInProgress.value) return

  shippingInProgress.value = true
  try {
    const { data } = await axios.post('/api/admin/shipping/scan-waybill', { code })
    const order = data?.order
    const dispatch = data?.dispatch_note

    if (!order || !dispatch) {
      throw new Error('Invalid shipping lookup response.')
    }

    if (!order.can_ship) {
      const reason = order.reason || 'Order cannot be shipped.'
      shippingScanMessage.value = `Skipped #${order.id}: ${reason}`
      addShippingLog('error', `Order #${order.id}`, `${reason} (${dispatch.ref_no})`)
      return
    }

    await axios.post(`/api/admin/dispatch-notes/${dispatch.id}/ship`, {
      order_ids: [order.id],
    })

    shippingScanMessage.value = `Shipped Order #${order.id} via ${dispatch.ref_no}`
    addShippingLog('success', `Order #${order.id}`, `Shipped successfully (${dispatch.ref_no})`)
    await fetchNotes()
  } catch (error) {
    const message = error?.response?.data?.message || error?.message || 'Scan shipping failed.'
    shippingScanMessage.value = message
    addShippingLog('error', `Scan ${code}`, message)
  } finally {
    shippingScanInput.value = ''
    shippingInProgress.value = false
    await nextTick()
    shippingScanInputRef.value?.focus()
  }
}

</script>
