<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-cyan-600 dark:text-cyan-300">Finance</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Affiliate Payments</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-finance-affiliate-payments"
        @filters-changed="onGlobalFiltersChanged"
      />
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <article class="rounded-xl border border-cyan-200 bg-cyan-50/70 p-3 dark:border-cyan-500/30 dark:bg-cyan-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Sellers</p>
        <p class="mt-1 text-lg font-bold text-cyan-800 dark:text-cyan-200">{{ summary.sellers_count }}</p>
      </article>
      <article class="rounded-xl border border-violet-200 bg-violet-50/70 p-3 dark:border-violet-500/30 dark:bg-violet-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-300">Available Affiliate</p>
        <p class="mt-1 text-lg font-bold text-violet-800 dark:text-violet-200">LKR {{ toMoney(summary.available_value) }}</p>
      </article>
      <article class="rounded-xl border border-blue-200 bg-blue-50/70 p-3 dark:border-blue-500/30 dark:bg-blue-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">Paid Affiliate</p>
        <p class="mt-1 text-lg font-bold text-blue-800 dark:text-blue-200">LKR {{ toMoney(summary.paid_value) }}</p>
      </article>
      <article class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Total Affiliate</p>
        <p class="mt-1 text-lg font-bold text-emerald-800 dark:text-emerald-200">LKR {{ toMoney(summary.total_value) }}</p>
      </article>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} seller records</p>
      <div class="flex flex-wrap items-end gap-3">
        <label class="space-y-1">
          <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-600 mr-3 dark:text-slate-300">Status</span>
          <select
            v-model="filters.status"
            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            @change="onFilterChanged"
          >
            <option value="all">All</option>
            <option value="available">Available</option>
            <option value="paid">Paid</option>
          </select>
        </label>

        <label class="space-y-1">
          <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-600 mr-3 dark:text-slate-300">Date Type</span>
          <select
            v-model="filters.date_type"
            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            @change="onFilterChanged"
          >
            <option value="available_at">Available Date</option>
            <option value="paid_at">Paid Date</option>
          </select>
        </label>

        <button
          type="button"
          class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
          @click="fetchRows"
        >
          Refresh
        </button>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">Seller</th>
            <th class="px-3 py-3 text-left">Contact</th>
            <th class="px-3 py-3 text-right">Records</th>
            <th class="px-3 py-3 text-right">Available</th>
            <th class="px-3 py-3 text-right">Paid</th>
            <th class="px-3 py-3 text-right">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="6" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading affiliate payments...</td>
          </tr>
          <tr v-else-if="!rows.length">
            <td colspan="6" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No affiliate payments found.</td>
          </tr>

          <tr v-for="seller in rows" :key="seller.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3">
              <p class="font-semibold text-slate-900 dark:text-white">{{ sellerName(seller) }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">Seller #{{ seller.id }}</p>
            </td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">
              <p>{{ seller.email || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ seller.phone || '-' }}</p>
            </td>
            <td class="px-3 py-3 text-right font-semibold text-slate-700 dark:text-slate-200">{{ Number(seller.commissions_count || 0) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-violet-700 dark:text-violet-300">LKR {{ toMoney(seller.available_affiliate_value) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-blue-700 dark:text-blue-300">LKR {{ toMoney(seller.paid_affiliate_value) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(seller.total_affiliate_value) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="meta.current_page <= 1 || loading"
        @click="changePage(meta.current_page - 1)"
      >
        Previous
      </button>
      <p class="text-xs text-slate-500 dark:text-slate-400">Page {{ meta.current_page }} of {{ meta.last_page }}</p>
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        :disabled="meta.current_page >= meta.last_page || loading"
        @click="changePage(meta.current_page + 1)"
      >
        Next
      </button>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const rows = ref([])

const filters = reactive({
  search: '',
  seller_id: null,
  date_from: '',
  date_to: '',
  status: 'all',
  date_type: 'available_at',
  page: 1,
  per_page: 20,
})

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const summary = reactive({
  sellers_count: 0,
  records_count: 0,
  available_value: 0,
  paid_value: 0,
  total_value: 0,
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const sellerName = (seller) => {
  if (!seller) return '-'
  const name = `${seller.first_name || ''} ${seller.last_name || ''}`.trim()
  return name || seller.email || `Seller #${seller.id}`
}

const fetchRows = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/finance/affiliate-payments', {
      params: {
        search: filters.search || undefined,
        seller_id: filters.seller_id || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        status: filters.status || 'all',
        date_type: filters.date_type || 'available_at',
        page: filters.page,
        per_page: filters.per_page,
      },
    })

    rows.value = Array.isArray(data?.sellers) ? data.sellers : []
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
    meta.total = Number(data?.meta?.total || 0)

    summary.sellers_count = Number(data?.summary?.sellers_count || 0)
    summary.records_count = Number(data?.summary?.records_count || 0)
    summary.available_value = Number(data?.summary?.available_value || 0)
    summary.paid_value = Number(data?.summary?.paid_value || 0)
    summary.total_value = Number(data?.summary?.total_value || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load affiliate payments.')
  } finally {
    loading.value = false
  }
}

const onFilterChanged = () => {
  filters.page = 1
  fetchRows()
}

const onGlobalFiltersChanged = (payload) => {
  filters.search = payload?.search || ''
  filters.date_from = payload?.date_from || ''
  filters.date_to = payload?.date_to || ''
  filters.seller_id = payload?.seller_id || null
  onFilterChanged()
}

const changePage = (page) => {
  filters.page = page
  fetchRows()
}

onMounted(() => {
  fetchRows()
})
</script>
