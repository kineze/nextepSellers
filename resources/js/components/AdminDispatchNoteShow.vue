<template>
  <section class="mx-3 mt-3 mb-8 space-y-4">
    <div class="rounded-3xl border border-slate-200/80 bg-white/95 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/90">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-300">Dispatch</p>
          <h1 class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ note?.ref_no || `DN #${noteId}` }}</h1>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            {{ formatDate(note?.dispatch_date) }} {{ shortTime(note?.dispatch_time) }}
            <span v-if="note?.creator?.name">• {{ note.creator.name }}</span>
          </p>
          <p v-if="note?.remarks" class="mt-2 text-sm text-slate-600 dark:text-slate-300"><strong>Remarks:</strong> {{ note.remarks }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide" :class="statusClass(note?.status)">
            {{ note?.status || '-' }}
          </span>
          <button type="button" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="goBack">
            Back
          </button>
          <button type="button" class="rounded-xl border border-blue-300 bg-blue-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-blue-700 hover:bg-blue-100 dark:border-blue-700/50 dark:bg-blue-900/20 dark:text-blue-200 dark:hover:bg-blue-900/30" @click="copyWaybills">
            Copy Waybills
          </button>
          <button type="button" class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900" @click="bulkPrintLabels(false)">
            {{ alreadyPrinted ? 'Print Again' : 'Bulk Print Labels' }}
          </button>
          <button type="button" class="rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-indigo-700 disabled:opacity-50" :disabled="!selectedPrintableIds.length" @click="bulkPrintLabels(true)">
            Print Selected ({{ selectedPrintableIds.length }})
          </button>
          <button type="button" class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-700 disabled:opacity-50" :disabled="shipping || !shippableRows.length" @click="openShipSidebar">
            Start Shipping
          </button>
        </div>
      </div>

      <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-xs text-slate-500 dark:text-slate-400">Orders</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ note?.orders_count || 0 }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-xs text-slate-500 dark:text-slate-400">Shipped</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ note?.shipped_count || 0 }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-xs text-slate-500 dark:text-slate-400">Collectable</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">LKR {{ toMoney(note?.total_collectable || 0) }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-xs text-slate-500 dark:text-slate-400">Ref</p>
          <p class="mt-1 text-base font-bold text-slate-900 dark:text-white">{{ note?.ref_no || '-' }}</p>
        </article>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="mb-3 flex items-center justify-between gap-2">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Dispatch Orders</h2>
        <div class="flex items-center gap-2 text-xs">
          <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="toggleAllPrintable">
            {{ allPrintableChecked ? 'Unselect Print' : 'Select Print All' }}
          </button>
          <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="toggleAllShippable">
            {{ allShippableChecked ? 'Unselect Ship' : 'Select Ship All' }}
          </button>
        </div>
      </div>

      <div v-if="loading" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">Loading dispatch note...</div>
      <div v-else-if="!orders.length" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">No orders found in this dispatch note.</div>

      <div v-else class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
            <tr>
              <th class="px-3 py-2 text-left">Print</th>
              <th class="px-3 py-2 text-left">Ship</th>
              <th class="px-3 py-2 text-left">Order</th>
              <th class="px-3 py-2 text-left">Customer</th>
              <th class="px-3 py-2 text-left">Waybill</th>
              <th class="px-3 py-2 text-left">Products</th>
              <th class="px-3 py-2 text-right">Collectable</th>
              <th class="px-3 py-2 text-left">Linking</th>
              <th class="px-3 py-2 text-left">Status</th>
              <th class="px-3 py-2 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <tr v-for="row in orders" :key="row.order_id" class="bg-white dark:bg-slate-900/40">
              <td class="px-3 py-2">
                <input type="checkbox" :checked="selectedPrintableIds.includes(row.order_id)" @change="togglePrintable(row.order_id)" />
              </td>
              <td class="px-3 py-2">
                <input type="checkbox" :checked="selectedShippableIds.includes(row.order_id)" :disabled="!row.can_ship || row.is_shipped" @change="toggleShippable(row.order_id)" />
              </td>
              <td class="px-3 py-2">
                <a :href="`/admin/orders/${row.order_id}`" class="font-semibold text-blue-600 hover:underline dark:text-blue-300">#{{ row.order_id }}</a>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ formatDate(row.order_datetime) }}</p>
              </td>
              <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                <p>{{ row.customer_name || '-' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ row.phone || '-' }}<span v-if="row.additional_phone"> / {{ row.additional_phone }}</span></p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ row.city?.name_en || '-' }}</p>
              </td>
              <td class="px-3 py-2">
                <span class="font-mono text-xs text-slate-700 dark:text-slate-200">{{ waybillValue(row) }}</span>
              </td>
              <td class="px-3 py-2">
                <div class="space-y-1">
                  <p v-for="item in row.items || []" :key="item.id" class="text-xs text-slate-600 dark:text-slate-300">
                    {{ item.product?.title || 'Product' }} × {{ item.quantity }}
                  </p>
                </div>
              </td>
              <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(row.collectable_amount) }}</td>
              <td class="px-3 py-2">
                <div class="inline-flex w-full items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 dark:border-slate-700 dark:bg-slate-800/60">
                  <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="linkingClass(row.linking_status)">
                    {{ linkingLabel(row.linking_status) }}
                  </span>
                  <span v-if="row.required_lines?.length" class="text-[11px] font-medium text-slate-600 dark:text-slate-300">
                    {{ linkedSummary(row) }}
                  </span>
                  <button
                    v-if="row.required_lines?.length && !row.is_shipped"
                    type="button"
                    class="ml-auto rounded-md border border-indigo-300 bg-indigo-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-indigo-700 hover:bg-indigo-100 dark:border-indigo-700/50 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/30"
                    @click="openLotScanModal(row)"
                  >
                    Scan Lots
                  </button>
                </div>
              </td>
              <td class="px-3 py-2">
                <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="row.is_shipped ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : row.can_ship ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'">
                  {{ row.is_shipped ? 'Shipped' : (row.can_ship ? 'Ready to Ship' : row.status) }}
                </span>
              </td>
              <td class="px-3 py-2 text-right">
                <a
                  :href="`/admin/orders/${row.order_id}`"
                  class="rounded-lg border border-slate-300 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                  View Order
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div id="bulk-labels" class="hidden">
      <div v-for="row in printRows" :key="`label-${row.order_id}`" class="lable-outer-box bg-white text-black rounded shadow">
        <div class="label-box">
          <div class="label-header">
            <div>
              <strong>Name:</strong> {{ row.seller?.name || 'Nextep' }}<br>
              <strong>Date:</strong> {{ formatDate(note?.dispatch_date) }}
            </div>
            <div class="text-right text-xs">
              <p><strong>Ref:</strong> {{ note?.ref_no || '-' }}</p>
              <p><strong>Waybill:</strong> {{ waybillValue(row) }}</p>
            </div>
          </div>

          <div class="barcode-block">
            <div class="barcode-left">
              <svg :id="`barcode-${row.order_id}`" :data-waybill="waybillValue(row)"></svg>
              <div class="text-xs mt-1">{{ waybillValue(row) }}</div>
            </div>
            <div class="barcode-right">
              <div class="text-xs">Qty</div>
              <div class="text-lg barcode-number font-bold">{{ totalQty(row) }}</div>
            </div>
          </div>

          <div class="first-line">
            <div v-for="item in row.items || []" :key="`f-${row.order_id}-${item.id}`">
              {{ item.product?.title || 'Product' }} × {{ item.quantity }}
            </div>
          </div>

          <table class="table-top">
            <tr>
              <th>Customer Details</th>
              <th>Order Total</th>
              <th class="text-right">{{ toMoney(row.net_total) }}</th>
            </tr>
          </table>

          <table class="table-bottom">
            <tr>
              <td rowspan="4" class="customer">
                <strong>Name:</strong> {{ row.customer_name || '-' }}<br>
                <strong>Address:</strong> {{ row.address || '-' }}<br>
                <strong>Contacts:</strong> {{ row.phone || '-' }} <span v-if="row.additional_phone">{{ row.additional_phone }}</span>
              </td>
              <td class="label">Delivery Cost</td>
              <td class="value text-right">{{ toMoney(row.delivery_charge) }}</td>
            </tr>
            <tr>
              <td class="label">Discount (-)</td>
              <td class="value text-right">{{ toMoney(row.total_discount) }}</td>
            </tr>
            <tr>
              <td class="label">Paid (-)</td>
              <td class="value text-right">0.00</td>
            </tr>
            <tr class="total-row">
              <td class="label font-bold">Total Due</td>
              <td class="value text-right font-bold">{{ toMoney(row.collectable_amount) }}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div v-if="shipSidebarOpen" class="fixed inset-0 z-[1400] flex justify-end bg-black/55">
      <div class="flex h-full w-full max-w-2xl flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">
          <div class="flex items-center justify-between gap-2">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-300">Shipping</p>
              <h3 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ note?.ref_no || '-' }}</h3>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Scan waybill barcode or type order id and press Enter.</p>
            </div>
            <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="closeShipSidebar">
              Close
            </button>
          </div>

          <div class="mt-3 space-y-2">
            <input
              ref="scanInputRef"
              v-model.trim="scanInput"
              type="text"
              placeholder="Scan barcode / order id and press Enter"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @keyup.enter="handleScanSubmit"
            />
            <p v-if="scanMessage" class="text-xs font-medium text-slate-600 dark:text-slate-300">{{ scanMessage }}</p>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="!shippableRows.length" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">
            No shippable orders available.
          </div>

          <div v-else class="space-y-2">
            <article
              v-for="row in shippableRows"
              :key="`ship-${row.order_id}`"
              class="rounded-xl border p-3 transition"
              :class="selectedShippableIds.includes(row.order_id)
                ? 'border-emerald-300 bg-emerald-50/70 dark:border-emerald-700/60 dark:bg-emerald-900/20'
                : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900/40'"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white">Order #{{ row.order_id }}</p>
                  <p class="mt-1 font-mono text-xs text-slate-600 dark:text-slate-300">{{ waybillValue(row) }}</p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.customer_name || '-' }} • {{ row.phone || '-' }}</p>
                  <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="linkingClass(row.linking_status)">
                      {{ linkingLabel(row.linking_status) }}
                    </span>
                    <button
                      v-if="row.required_lines?.length"
                      type="button"
                      class="rounded-md border border-indigo-300 bg-indigo-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-indigo-700 hover:bg-indigo-100 dark:border-indigo-700/50 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/30"
                      @click="openLotScanModal(row)"
                    >
                      Scan Lots
                    </button>
                  </div>
                </div>
                <input
                  type="checkbox"
                  :checked="selectedShippableIds.includes(row.order_id)"
                  :disabled="!row.can_ship"
                  @change="toggleShippable(row.order_id)"
                />
              </div>
            </article>
          </div>
        </div>

        <div class="border-t border-slate-200 p-4 dark:border-slate-700">
          <div class="flex items-center justify-between gap-2">
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Selected {{ selectedShippableIds.length }} / {{ shippableRows.length }}
            </p>
            <button
              type="button"
              class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-700 disabled:opacity-50"
              :disabled="shipping || !selectedShippableIds.length"
              @click="shipSelected"
            >
              {{ shipping ? 'Submitting...' : `Start Shipping (${selectedShippableIds.length})` }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showLotScanModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-black/60 p-4">
      <div class="w-full max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Scan Lot Items</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Order #{{ lotScanOrder?.order_id }} • {{ waybillValue(lotScanOrder) }}</p>
          </div>
          <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="closeLotScanModal">
            Close
          </button>
        </div>

        <div class="p-5">
          <input
            ref="lotScanInputRef"
            v-model.trim="lotScanInput"
            type="text"
            placeholder="Scan lot barcode and press Enter"
            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            @keyup.enter="handleLotBarcodeScan"
          />
          <p v-if="lotScanMessage" class="mt-2 text-xs font-medium text-slate-600 dark:text-slate-300">{{ lotScanMessage }}</p>

          <div class="mt-4 max-h-[50vh] space-y-2 overflow-y-auto">
            <article v-for="line in lotScanLines" :key="line.variant_id" class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ line.product_title || 'Variant' }}</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">SKU: {{ line.sku || '-' }}</p>
                </div>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                  {{ line.scanned_barcodes.length }} / {{ line.required_qty }}
                </p>
              </div>

              <div class="mt-2 grid gap-1 md:grid-cols-2">
                <div v-for="(barcode, index) in line.scanned_barcodes" :key="`${line.variant_id}-${index}`" class="flex items-center justify-between rounded-md bg-slate-100 px-2 py-1 text-[11px] font-mono dark:bg-slate-800">
                  <span>{{ barcode }}</span>
                  <button type="button" class="text-rose-600" @click="removeScannedBarcode(line.variant_id, index)">✕</button>
                </div>
              </div>
            </article>
          </div>
        </div>

        <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4 dark:border-slate-700">
          <p class="text-xs text-slate-500 dark:text-slate-400">
            {{ isLotScanComplete ? 'Ready to submit.' : 'Scan all required barcodes to continue.' }}
          </p>
          <button
            type="button"
            class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-indigo-700 disabled:opacity-50"
            :disabled="lotScanSubmitting || !isLotScanComplete"
            @click="submitLotScan"
          >
            {{ lotScanSubmitting ? 'Submitting...' : 'Scan & Submit' }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps({
  noteId: { type: Number, required: true },
})

const toast = useToast()

const loading = ref(false)
const shipping = ref(false)
const note = ref(null)
const orders = ref([])
const printRows = ref([])
const alreadyPrinted = ref(false)
const shipSidebarOpen = ref(false)
const scanInputRef = ref(null)
const scanInput = ref('')
const scanMessage = ref('')
const showLotScanModal = ref(false)
const lotScanOrder = ref(null)
const lotScanLines = ref([])
const lotScanInputRef = ref(null)
const lotScanInput = ref('')
const lotScanMessage = ref('')
const lotScanSubmitting = ref(false)
const selectedPrintableIds = ref([])
const selectedShippableIds = ref([])

const allPrintableChecked = computed(() => {
  if (!orders.value.length) return false
  return orders.value.every((row) => selectedPrintableIds.value.includes(row.order_id))
})

const allShippableChecked = computed(() => {
  const ids = orders.value.filter((row) => row.can_ship && !row.is_shipped).map((row) => row.order_id)
  if (!ids.length) return false
  return ids.every((id) => selectedShippableIds.value.includes(id))
})

const shippableRows = computed(() => {
  return orders.value.filter((row) => !row.is_shipped && ['packed', 'approved', 'confirmed'].includes(String(row.status || '').toLowerCase()))
})

const isLotScanComplete = computed(() => {
  return lotScanLines.value.length > 0 && lotScanLines.value.every((line) => line.scanned_barcodes.length === Number(line.required_qty || 0))
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return date.toLocaleDateString()
}

const shortTime = (value) => {
  if (!value) return ''
  const raw = String(value)
  return raw.length >= 5 ? raw.slice(0, 5) : raw
}

const statusClass = (status) => {
  const s = String(status || '').toLowerCase()
  if (s === 'shipped') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
  if (s === 'partial_shipped') return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'
}

const linkingLabel = (status) => {
  if (status === 'linked') return 'Linked'
  if (status === 'partial') return 'Partial'
  if (status === 'not_required') return 'No Variant Scan'
  return 'Pending'
}

const linkingClass = (status) => {
  if (status === 'linked') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
  if (status === 'partial') return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
  if (status === 'not_required') return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
  return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'
}

const linkedSummary = (row) => {
  const lines = Array.isArray(row?.required_lines) ? row.required_lines : []
  if (!lines.length) return 'No variant-linked items'
  const required = lines.reduce((sum, line) => sum + Number(line?.required_qty || 0), 0)
  const linked = lines.reduce((sum, line) => sum + Number(line?.linked_qty || 0), 0)
  return `${linked} / ${required} linked`
}

const waybillValue = (row) => {
  const fromApi = String(row?.waybill_no || '').trim()
  if (fromApi) return fromApi
  return `PKG${String(row?.order_id || '').padStart(6, '0')}`
}

const totalQty = (row) => {
  return (row?.items || []).reduce((sum, item) => sum + Number(item?.quantity || 0), 0)
}

const fetchNote = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/admin/dispatch-notes/${props.noteId}`)
    note.value = data?.dispatch_note || null
    orders.value = Array.isArray(data?.orders) ? data.orders : []
    selectedPrintableIds.value = orders.value.map((row) => row.order_id)
    selectedShippableIds.value = orders.value.filter((row) => row.can_ship && !row.is_shipped).map((row) => row.order_id)

    if (note.value?.id) {
      alreadyPrinted.value = window.localStorage.getItem(`bulkPrinted_${note.value.id}`) === 'true'
    }
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load dispatch note.')
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  window.location.href = '/admin/orders/dispatch-notes'
}

const togglePrintable = (orderId) => {
  if (selectedPrintableIds.value.includes(orderId)) {
    selectedPrintableIds.value = selectedPrintableIds.value.filter((id) => id !== orderId)
    return
  }
  selectedPrintableIds.value.push(orderId)
}

const toggleShippable = (orderId) => {
  const row = shippableRows.value.find((item) => item.order_id === orderId)
  if (row && !row.can_ship) return

  if (selectedShippableIds.value.includes(orderId)) {
    selectedShippableIds.value = selectedShippableIds.value.filter((id) => id !== orderId)
    return
  }
  selectedShippableIds.value.push(orderId)
}

const openShipSidebar = async () => {
  shipSidebarOpen.value = true
  selectedShippableIds.value = shippableRows.value.filter((row) => row.can_ship).map((row) => row.order_id)
  scanInput.value = ''
  scanMessage.value = ''
  await nextTick()
  scanInputRef.value?.focus()
}

const closeShipSidebar = () => {
  shipSidebarOpen.value = false
  scanInput.value = ''
  scanMessage.value = ''
}

const handleScanSubmit = () => {
  const code = String(scanInput.value || '').trim()
  if (!code) return

  const normalized = code.toLowerCase()
  const matched = shippableRows.value.find((row) => {
    const wb = waybillValue(row).toLowerCase()
    const orderId = String(row.order_id)
    return wb === normalized || orderId === code
  })

  if (!matched) {
    scanMessage.value = `No match found for "${code}".`
    scanInput.value = ''
    return
  }

  if (!matched.can_ship) {
    scanMessage.value = `Order #${matched.order_id} is not linked. Scan lots first.`
    scanInput.value = ''
    return
  }

  if (!selectedShippableIds.value.includes(matched.order_id)) {
    selectedShippableIds.value.push(matched.order_id)
  }
  scanMessage.value = `Scanned Order #${matched.order_id}`
  scanInput.value = ''
}

const toggleAllPrintable = () => {
  const ids = orders.value.map((row) => row.order_id)
  if (allPrintableChecked.value) {
    selectedPrintableIds.value = []
    return
  }
  selectedPrintableIds.value = ids
}

const toggleAllShippable = () => {
  const ids = shippableRows.value.filter((row) => row.can_ship).map((row) => row.order_id)
  if (allShippableChecked.value) {
    selectedShippableIds.value = []
    return
  }
  selectedShippableIds.value = ids
}

const shipSelected = async () => {
  if (!note.value || !selectedShippableIds.value.length) {
    toast.error('Select orders to ship.')
    return
  }

  shipping.value = true
  try {
    const { data } = await axios.post(`/api/admin/dispatch-notes/${note.value.id}/ship`, {
      order_ids: selectedShippableIds.value,
    })
    toast.success(data?.message || 'Orders shipped successfully.')
    await fetchNote()
    closeShipSidebar()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to ship selected orders.')
  } finally {
    shipping.value = false
  }
}

const openLotScanModal = async (row) => {
  lotScanOrder.value = row
  lotScanInput.value = ''
  lotScanMessage.value = ''

  const linkedByVariant = row.linked_by_variant || {}
  lotScanLines.value = (row.required_lines || []).map((line) => {
    const existing = Array.isArray(linkedByVariant[String(line.variant_id)]) ? linkedByVariant[String(line.variant_id)] : []
    return {
      variant_id: Number(line.variant_id),
      product_title: line.product_title || null,
      sku: line.sku || null,
      required_qty: Number(line.required_qty || 0),
      scanned_barcodes: existing.slice(0, Number(line.required_qty || 0)),
    }
  })

  showLotScanModal.value = true
  await nextTick()
  lotScanInputRef.value?.focus()
}

const closeLotScanModal = () => {
  showLotScanModal.value = false
  lotScanOrder.value = null
  lotScanLines.value = []
  lotScanInput.value = ''
  lotScanMessage.value = ''
}

const removeScannedBarcode = (variantId, index) => {
  const line = lotScanLines.value.find((item) => item.variant_id === Number(variantId))
  if (!line) return
  line.scanned_barcodes.splice(index, 1)
}

const handleLotBarcodeScan = async () => {
  const barcode = String(lotScanInput.value || '').trim()
  if (!barcode || !lotScanOrder.value) return

  const alreadyScanned = lotScanLines.value.some((entry) => entry.scanned_barcodes.includes(barcode))
  if (alreadyScanned) {
    lotScanMessage.value = 'Barcode already scanned in this order.'
    lotScanInput.value = ''
    return
  }

  try {
    const { data } = await axios.post(`/api/admin/dispatch-notes/${props.noteId}/validate-lot-barcode`, {
      order_id: lotScanOrder.value.order_id,
      barcode,
    })

    const resolvedVariantId = Number(data?.variant_id || 0)
    const line = lotScanLines.value.find((entry) => entry.variant_id === resolvedVariantId)
    if (!line) {
      lotScanMessage.value = 'Scanned barcode variant is not required for this order.'
      lotScanInput.value = ''
      return
    }

    if (line.scanned_barcodes.length >= line.required_qty) {
      lotScanMessage.value = `Required quantity already completed for SKU ${line.sku || line.variant_id}.`
      lotScanInput.value = ''
      return
    }

    line.scanned_barcodes.push(barcode)
    lotScanMessage.value = `Scanned ${barcode} (${line.sku || `Variant ${line.variant_id}`})`
    lotScanInput.value = ''
  } catch (error) {
    lotScanMessage.value = error?.response?.data?.message || 'Invalid barcode.'
    lotScanInput.value = ''
  }
}

const submitLotScan = async () => {
  if (!lotScanOrder.value || !isLotScanComplete.value) return

  lotScanSubmitting.value = true
  try {
    await axios.post(`/api/admin/dispatch-notes/${props.noteId}/link-lot-items`, {
      order_id: lotScanOrder.value.order_id,
      variants: lotScanLines.value.map((line) => ({
        variant_id: line.variant_id,
        barcodes: line.scanned_barcodes,
      })),
    })

    toast.success('Lot items linked successfully.')
    closeLotScanModal()
    await fetchNote()
    shipSidebarOpen.value = true
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to link lot items.')
  } finally {
    lotScanSubmitting.value = false
  }
}

const copyWaybills = async () => {
  const list = orders.value
    .map((row) => waybillValue(row))
    .filter(Boolean)

  if (!list.length) {
    toast.info('No waybills found.')
    return
  }

  try {
    await navigator.clipboard.writeText(list.join('\n'))
    toast.success(`${list.length} waybill(s) copied.`)
  } catch {
    toast.error('Clipboard permission denied.')
  }
}

const bulkPrintLabels = async (selectedOnly) => {
  const source = selectedOnly
    ? orders.value.filter((row) => selectedPrintableIds.value.includes(row.order_id))
    : orders.value

  if (!source.length) {
    toast.error('No items to print')
    return
  }

  printRows.value = source
  await nextTick()

  const printWindow = window.open('', '', 'width=900,height=1200')
  if (!printWindow) {
    toast.error('Popup blocked. Please allow popups for printing.')
    return
  }

  let styles = ''
  document.querySelectorAll('link[rel="stylesheet"], style').forEach((node) => {
    styles += node.outerHTML
  })

  const bulkLabels = document.getElementById('bulk-labels')
  if (!bulkLabels) {
    toast.error('No labels DOM found')
    return
  }

  const htmlContent = `
    <html>
    <head>
      <title>Bulk Labels</title>
      ${styles}
      <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
      <style>
        html, body { width: 10cm; height: 10cm; margin: 0; padding: 0; background: #fff; }
        @page { size: 10cm 10cm; margin: 0; bleed: 0; }
        .lable-outer-box { padding: 20px; width: 10cm; height: 10cm; }
        .label-box {
          height: 100%; box-sizing: border-box; border: 1px solid #000; margin: 0;
          font-size: 9px; line-height: 1.2; display: flex; flex-direction: column;
          justify-content: space-between; overflow: hidden; position: relative; page-break-after: always;
        }
        .label-header { display: flex; padding: 6px; height: 15%; justify-content: space-between; font-size: 14px; font-weight: 600; }
        .barcode-block { display: flex; align-items: stretch; justify-content: space-between; flex: 1; height: 25% !important; }
        .barcode-left {
          flex: 4; border: 1px solid #000; padding: 0.1cm; text-align: center; overflow: hidden;
          display: flex; font-size: 8px !important; flex-direction: column; justify-content: center;
        }
        .barcode-left svg { width: 100% !important; height: 60px !important; }
        .barcode-right {
          flex: 1; border: 1px solid #000; text-align: center; font-size: 11px; font-weight: bold;
          display: flex; flex-direction: column; justify-content: center; align-self: stretch; background: #f9f9f9;
        }
        .barcode-number { font-size: 24px !important; }
        .first-line {
          border: 1px solid #000; padding: 0.1cm; font-size: 14px; font-weight: 800; height: 20%;
          display: flex; flex-direction: column; align-items: center; justify-content: center;
          text-align: center; word-break: break-word;
        }
        .table-top { width: 100%; border-collapse: collapse; font-size: 12px; border: 1px solid #000; }
        .table-top th { border-right: 1px solid #000; padding: 3px 5px; text-align: left; font-weight: 600; }
        .table-top th:last-child { border-right: none; text-align: right; }
        .table-bottom { width: 100%; height: 100%; border-collapse: collapse; font-size: 10px; border: 1px solid #000; }
        .table-bottom td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
        .table-bottom .customer {
          width: 60%; font-size: 12px; line-height: 1.4; word-break: break-word; white-space: pre-wrap;
          text-align: left; vertical-align: middle; padding: 6px 5px;
        }
        .table-bottom .label { width: 25%; text-align: right; font-weight: 500; }
        .table-bottom .value { width: 15%; text-align: right; font-weight: 500; }
        .table-bottom .total-row td { font-weight: 700; background: #f7f7f7; }
        .text-right { text-align: right; }
        @media print {
          body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
      </style>
    </head>
    <body>${bulkLabels.innerHTML}</body>
    </html>
  `

  printWindow.document.open()
  printWindow.document.write(htmlContent)
  printWindow.document.close()

  const renderBarcodesAndPrint = (attempt = 0) => {
    if (!printWindow || printWindow.closed) return
    if (!printWindow.JsBarcode) {
      if (attempt < 20) {
        setTimeout(() => renderBarcodesAndPrint(attempt + 1), 120)
      }
      return
    }

    const barcodes = printWindow.document.querySelectorAll('svg[data-waybill]')
    barcodes.forEach((svg) => {
      const wb = svg.getAttribute('data-waybill') || ''
      if (!wb) return
      try {
        printWindow.JsBarcode(svg, wb, {
          format: 'CODE128',
          width: 2,
          height: 60,
          displayValue: false,
          margin: 0,
        })
      } catch (error) {
        console.error('Barcode render failed:', error)
      }
    })

    printWindow.focus()
    printWindow.print()

    if (note.value?.id) {
      window.localStorage.setItem(`bulkPrinted_${note.value.id}`, 'true')
      alreadyPrinted.value = true
    }
  }

  printWindow.onload = () => {
    renderBarcodesAndPrint()
  }
}

fetchNote()
</script>
