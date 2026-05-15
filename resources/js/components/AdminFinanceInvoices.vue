<template>
  <section class="mx-3 mt-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="space-y-3">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-cyan-600 dark:text-cyan-300">Finance</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Invoices</h1>
      </div>

      <admin-global-filter-bar
        context-key="admin-finance-invoices"
        @filters-changed="onGlobalFiltersChanged"
      />

      <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
        <div class="flex flex-wrap items-end gap-3">
          <label class="space-y-1">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Status</span>
            <select
              v-model="filters.status"
              class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @change="onFilterChanged"
            >
              <option value="">All</option>
              <option value="draft">Draft</option>
              <option value="paid">Paid</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </label>
        </div>
      </div>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
      <article class="rounded-xl border border-cyan-200 bg-cyan-50/70 p-3 dark:border-cyan-500/30 dark:bg-cyan-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-cyan-700 dark:text-cyan-300">Invoice Count</p>
        <p class="mt-1 text-lg font-bold text-cyan-800 dark:text-cyan-200">{{ summary.invoice_count }}</p>
      </article>
      <article class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Total Commission Value</p>
        <p class="mt-1 text-lg font-bold text-emerald-800 dark:text-emerald-200">LKR {{ toMoney(summary.total_commission_value) }}</p>
      </article>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <p class="text-xs text-slate-500 dark:text-slate-400">Total {{ meta.total }} invoice records</p>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="rounded-xl border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-700 hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-300"
          :disabled="exporting || loading || selectedInvoiceIds.length === 0"
          @click="exportBankDocument"
        >
          {{ exporting ? 'Exporting...' : `Export Selected (${selectedInvoiceIds.length})` }}
        </button>
        <button
          type="button"
          class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900"
          @click="fetchInvoices"
        >
          Refresh
        </button>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3 text-left">
              <input
                type="checkbox"
                class="h-4 w-4 rounded border border-slate-300"
                :checked="allVisibleSelected"
                :indeterminate.prop="someVisibleSelected"
                @change="toggleSelectAllVisible($event.target.checked)"
              />
            </th>
            <th class="px-3 py-3 text-left">Invoice</th>
            <th class="px-3 py-3 text-left">Seller</th>
            <th class="px-3 py-3 text-left">Date</th>
            <th class="px-3 py-3 text-left">Time</th>
            <th class="px-3 py-3 text-right">Orders</th>
            <th class="px-3 py-3 text-right">Commission</th>
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading invoices...</td>
          </tr>
          <tr v-else-if="!invoices.length">
            <td colspan="9" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No invoices found.</td>
          </tr>

          <tr v-for="invoice in invoices" :key="invoice.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3">
              <input
                type="checkbox"
                class="h-4 w-4 rounded border border-slate-300"
                :checked="isSelected(invoice.id)"
                @change="toggleInvoiceSelection(invoice.id, $event.target.checked)"
              />
            </td>
            <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">#{{ invoice.id }}</td>
            <td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ sellerName(invoice.seller) }}</td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(invoice.invoice_date) }}</td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatPdfTime(invoice.invoice_time) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-cyan-700 dark:text-cyan-300">{{ Number(invoice.orders_count || 0) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(invoice.total_commission_value) }}</td>
            <td class="px-3 py-3">
              <span :class="statusClass(invoice.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">
                {{ invoice.status }}
              </span>
            </td>
            <td class="px-3 py-3 text-right">
              <div class="flex flex-wrap justify-end gap-2">
                <button
                  type="button"
                  class="rounded-lg border border-sky-300 bg-sky-50 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wide text-sky-700 hover:bg-sky-100 disabled:opacity-50 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-300"
                  :disabled="pdfGeneratingId === invoice.id"
                  @click="openInvoicePdf(invoice)"
                >
                  {{ pdfGeneratingId === invoice.id ? 'Opening...' : 'PDF' }}
                </button>
              <button
                v-if="String(invoice.status).toLowerCase() === 'draft'"
                type="button"
                class="rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 hover:bg-emerald-100 disabled:opacity-50"
                :disabled="markingPaidId === invoice.id"
                @click="openMarkPaidConfirm(invoice)"
              >
                {{ markingPaidId === invoice.id ? 'Updating...' : 'Mark as Paid' }}
              </button>
              </div>
            </td>
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

    <transition name="fade">
      <div v-if="showMarkPaidModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200/70 bg-white/95 p-6 shadow-2xl dark:border-slate-800/70 dark:bg-slate-900/95">
          <h3 class="mb-2 text-lg font-semibold text-slate-900 dark:text-white">Mark Invoice as Paid</h3>
          <p class="mb-2 text-sm text-slate-600 dark:text-slate-300">
            Invoice <strong>#{{ selectedInvoiceForMarkPaid?.id }}</strong> will be marked as paid.
          </p>
          <p class="mb-2 text-sm text-slate-600 dark:text-slate-300">
            Seller: <strong>{{ sellerName(selectedInvoiceForMarkPaid?.seller) }}</strong>
          </p>
          <p class="mb-5 text-sm text-slate-600 dark:text-slate-300">
            Commission: <strong>LKR {{ toMoney(selectedInvoiceForMarkPaid?.total_commission_value) }}</strong>
          </p>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
              :disabled="markingPaidId !== null"
              @click="closeMarkPaidConfirm"
            >
              Cancel
            </button>
            <button
              type="button"
              class="rounded-xl bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700 disabled:opacity-60"
              :disabled="markingPaidId !== null"
              @click="confirmMarkPaid"
            >
              Confirm Mark as Paid
            </button>
          </div>
        </div>
      </div>
    </transition>

  </section>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import axios from 'axios'
import { jsPDF } from 'jspdf'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const exporting = ref(false)
const invoices = ref([])
const selectedInvoiceIds = ref([])
const showMarkPaidModal = ref(false)
const selectedInvoiceForMarkPaid = ref(null)
const markingPaidId = ref(null)
const pdfGeneratingId = ref(null)

const filters = reactive({
  search: '',
  date_from: '',
  date_to: '',
  seller_id: null,
  status: '',
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
  invoice_count: 0,
  total_commission_value: 0,
})

const allVisibleSelected = computed(() => {
  if (!invoices.value.length) return false
  return invoices.value.every((invoice) => selectedInvoiceIds.value.includes(invoice.id))
})

const someVisibleSelected = computed(() => {
  if (!invoices.value.length) return false
  const selectedCount = invoices.value.filter((invoice) => selectedInvoiceIds.value.includes(invoice.id)).length
  return selectedCount > 0 && selectedCount < invoices.value.length
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString()
}

const sellerName = (seller) => {
  if (!seller) return '-'
  const name = `${seller.first_name || ''} ${seller.last_name || ''}`.trim()
  return name || seller.email || `Seller #${seller.id}`
}

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'paid') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
  if (value === 'cancelled') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
  return 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
}

const pdfPlain = (value, fallback = '-') => {
  const text = String(value ?? '').trim()
  return text || fallback
}

const formatPdfDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return date.toLocaleDateString()
}

const formatPdfDateTime = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return date.toLocaleString()
}

const formatPdfTime = (value) => {
  if (!value) return '-'

  const raw = String(value)
  const isoDate = new Date(raw)
  if (!Number.isNaN(isoDate.getTime())) {
    return isoDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
  }

  const timeMatch = raw.match(/\b(\d{2}:\d{2}(?::\d{2})?)\b/)
  return timeMatch ? timeMatch[1] : raw
}

const statusLabel = (status) => String(status || 'draft').toUpperCase()

const fetchInvoices = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/admin/finance/invoices', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        status: filters.status || undefined,
        page: filters.page,
        per_page: filters.per_page,
      },
    })

    invoices.value = Array.isArray(data?.invoices) ? data.invoices : []
    selectedInvoiceIds.value = []
    meta.current_page = Number(data?.meta?.current_page || 1)
    meta.last_page = Number(data?.meta?.last_page || 1)
    meta.per_page = Number(data?.meta?.per_page || filters.per_page)
    meta.total = Number(data?.meta?.total || 0)

    summary.invoice_count = Number(data?.summary?.invoice_count || 0)
    summary.total_commission_value = Number(data?.summary?.total_commission_value || 0)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load invoices.')
  } finally {
    loading.value = false
  }
}

const onFilterChanged = () => {
  filters.page = 1
  fetchInvoices()
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
  fetchInvoices()
}

const isSelected = (invoiceId) => selectedInvoiceIds.value.includes(invoiceId)

const toggleInvoiceSelection = (invoiceId, checked) => {
  const next = new Set(selectedInvoiceIds.value)
  if (checked) {
    next.add(invoiceId)
  } else {
    next.delete(invoiceId)
  }
  selectedInvoiceIds.value = Array.from(next)
}

const toggleSelectAllVisible = (checked) => {
  if (!checked) {
    selectedInvoiceIds.value = []
    return
  }
  selectedInvoiceIds.value = invoices.value.map((invoice) => invoice.id)
}

const exportBankDocument = async () => {
  if (!selectedInvoiceIds.value.length) {
    toast.error('Select at least one invoice to export.')
    return
  }

  exporting.value = true
  try {
    const response = await axios.get('/api/admin/finance/invoices/export-bank-document', {
      params: {
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        seller_id: filters.seller_id || undefined,
        status: filters.status || undefined,
        invoice_ids: selectedInvoiceIds.value,
      },
      responseType: 'blob',
    })

    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    const fileName =
      response.headers?.['content-disposition']
        ?.split('filename=')[1]
        ?.replace(/\"/g, '') || `invoice-bank-document-${new Date().toISOString().slice(0, 10)}.csv`

    link.href = url
    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    toast.success('Bank document exported successfully.')
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to export bank document.')
  } finally {
    exporting.value = false
  }
}

const openInvoicePdf = async (invoice) => {
  if (!invoice?.id) return

  const targetWindow = window.open('', '_blank')
  if (!targetWindow) {
    toast.error('Popup blocked. Please allow popups to open invoice PDFs.')
    return
  }

  targetWindow.document.write('<p style="font-family: Arial, sans-serif; padding: 24px;">Generating invoice PDF...</p>')
  pdfGeneratingId.value = invoice.id

  try {
    const { data } = await axios.get(`/api/admin/finance/invoices/${invoice.id}/pdf-data`)
    const blob = await generateInvoicePdfBlob(data)

    const url = window.URL.createObjectURL(blob)
    targetWindow.location.href = url
    setTimeout(() => window.URL.revokeObjectURL(url), 60000)
  } catch (error) {
    targetWindow.close()
    toast.error(error?.response?.data?.message || 'Failed to generate invoice PDF.')
  } finally {
    pdfGeneratingId.value = null
  }
}

const loadImageAsDataUrl = async (url) => {
  if (!url) return null

  try {
    const response = await fetch(url)
    if (!response.ok) return null
    const blob = await response.blob()

    return await new Promise((resolve) => {
      const reader = new FileReader()
      reader.onload = () => resolve(reader.result)
      reader.onerror = () => resolve(null)
      reader.readAsDataURL(blob)
    })
  } catch {
    return null
  }
}

const loadLogoForPdf = async (url) => {
  const dataUrl = await loadImageAsDataUrl(url)
  if (!dataUrl) return null

  return await new Promise((resolve) => {
    const image = new Image()
    image.onload = () => {
      const canvas = document.createElement('canvas')
      canvas.width = image.naturalWidth || image.width
      canvas.height = image.naturalHeight || image.height
      const context = canvas.getContext('2d')

      if (!context || !canvas.width || !canvas.height) {
        resolve(null)
        return
      }

      context.clearRect(0, 0, canvas.width, canvas.height)
      context.drawImage(image, 0, 0)

      resolve({
        dataUrl: canvas.toDataURL('image/png'),
        width: canvas.width,
        height: canvas.height,
      })
    }
    image.onerror = () => resolve(null)
    image.src = dataUrl
  })
}

const generateInvoicePdfBlob = async (data) => {
  const system = data?.system_data || {}
  const invoice = data?.invoice || {}
  const seller = data?.seller || {}
  const payment = data?.payment_information || {}
  const summaryData = data?.summary || {}
  const orders = Array.isArray(data?.orders) ? data.orders : []
  const affiliateCommissions = Array.isArray(data?.affiliate_commissions) ? data.affiliate_commissions : []
  const sellerDisplayName = sellerName(seller)
  const generatedAt = new Date().toLocaleString()
  const logoImage = await loadLogoForPdf(system.logo_url)

  const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' })
  const pageWidth = doc.internal.pageSize.getWidth()
  const pageHeight = doc.internal.pageSize.getHeight()
  const margin = 36
  const contentWidth = pageWidth - (margin * 2)
  let y = margin

  const addPageIfNeeded = (height = 24) => {
    if (y + height <= pageHeight - margin) return
    doc.addPage('a4', 'portrait')
    y = margin
  }

  const setText = (size = 9, style = 'normal', color = [17, 24, 39]) => {
    doc.setFont('helvetica', style)
    doc.setFontSize(size)
    doc.setTextColor(...color)
  }

  const drawWrappedText = (text, x, top, maxWidth, lineHeight = 11, options = {}) => {
    const lines = doc.splitTextToSize(pdfPlain(text, ''), maxWidth)
    doc.text(lines, x, top, options)
    return lines.length * lineHeight
  }

  const drawBox = (x, top, width, height, fill = [255, 255, 255], stroke = [203, 213, 225]) => {
    doc.setFillColor(...fill)
    doc.setDrawColor(...stroke)
    doc.roundedRect(x, top, width, height, 5, 5, 'FD')
  }

  const drawKeyValue = (label, value, x, top, maxWidth) => {
    setText(8, 'bold', [51, 65, 85])
    doc.text(`${label}:`, x, top)
    setText(8, 'normal', [71, 85, 105])
    return drawWrappedText(value, x + 72, top, maxWidth - 72, 10)
  }

  const drawSectionTitle = (title) => {
    addPageIfNeeded(30)
    y += 8
    setText(12, 'bold', [15, 23, 42])
    doc.text(title, margin, y)
    y += 10
  }

  const drawSummaryCard = (label, value, x, top, width) => {
    drawBox(x, top, width, 48, [255, 255, 255], [226, 232, 240])
    setText(7, 'bold', [100, 116, 139])
    doc.text(label.toUpperCase(), x + 8, top + 15)
    setText(10, 'bold', [15, 23, 42])
    const valueLines = doc.splitTextToSize(pdfPlain(value), width - 16)
    doc.text(valueLines.slice(0, 2), x + 8, top + 31)
  }

  const drawTable = (headers, rows, widths) => {
    const rowHeight = 26
    const headerHeight = 22

    addPageIfNeeded(headerHeight + rowHeight)
    doc.setFillColor(15, 23, 42)
    doc.rect(margin, y, contentWidth, headerHeight, 'F')
    setText(7, 'bold', [255, 255, 255])

    let x = margin
    headers.forEach((header, index) => {
      doc.text(header, x + 4, y + 14)
      x += widths[index]
    })
    y += headerHeight

    if (!rows.length) {
      addPageIfNeeded(rowHeight)
      doc.setDrawColor(203, 213, 225)
      doc.rect(margin, y, contentWidth, rowHeight)
      setText(8, 'normal', [100, 116, 139])
      doc.text('No records found.', margin + 8, y + 16)
      y += rowHeight
      return
    }

    rows.forEach((row, rowIndex) => {
      addPageIfNeeded(rowHeight)
      doc.setFillColor(...(rowIndex % 2 ? [248, 250, 252] : [255, 255, 255]))
      doc.setDrawColor(203, 213, 225)
      doc.rect(margin, y, contentWidth, rowHeight, 'FD')
      x = margin
      setText(7, 'normal', [31, 41, 55])

      row.forEach((cell, index) => {
        const text = doc.splitTextToSize(pdfPlain(cell), widths[index] - 8).slice(0, 2)
        doc.text(text, x + 4, y + 11)
        x += widths[index]
      })

      y += rowHeight
    })
  }

  let companyTextX = margin
  let companyTextWidth = 300
  if (logoImage) {
    try {
      const maxLogoHeight = 64
      const maxLogoWidth = 112
      const ratio = logoImage.width / logoImage.height
      let logoHeight = maxLogoHeight
      let logoWidth = logoHeight * ratio

      if (logoWidth > maxLogoWidth) {
        logoWidth = maxLogoWidth
        logoHeight = logoWidth / ratio
      }

      doc.addImage(logoImage.dataUrl, 'PNG', margin, y - 8, logoWidth, logoHeight, undefined, 'FAST')
      companyTextX = margin + logoWidth + 14
      companyTextWidth = Math.max(190, pageWidth - companyTextX - margin - 210)
    } catch {
      companyTextX = margin
      companyTextWidth = 300
    }
  }

  setText(18, 'bold', [15, 23, 42])
  drawWrappedText(system.company_name || 'Company Name', companyTextX, y, companyTextWidth, 20)
  y += 24
  setText(8, 'normal', [71, 85, 105])
  y += drawWrappedText(system.address || 'Address not configured', companyTextX, y, 310, 10)
  doc.text(pdfPlain(system.country, ''), companyTextX, y)
  y += 12
  doc.text(`Phone: ${pdfPlain(system.phone_number)}    Fax: ${pdfPlain(system.fax)}`, companyTextX, y)

  setText(22, 'bold', [15, 23, 42])
  doc.text('Payment Invoice', pageWidth - margin, margin + 4, { align: 'right' })
  setText(9, 'normal', [71, 85, 105])
  doc.text(`Invoice: #${pdfPlain(invoice.id)}`, pageWidth - margin, margin + 24, { align: 'right' })
  doc.text(`Date: ${formatPdfDate(invoice.invoice_date)}`, pageWidth - margin, margin + 38, { align: 'right' })
  doc.text(`Time: ${formatPdfTime(invoice.invoice_time)}`, pageWidth - margin, margin + 52, { align: 'right' })

  y = 132
  doc.setDrawColor(15, 23, 42)
  doc.setLineWidth(1.5)
  doc.line(margin, y - 12, pageWidth - margin, y - 12)

  const boxGap = 12
  const boxWidth = (contentWidth - boxGap) / 2
  drawBox(margin, y, boxWidth, 94)
  drawBox(margin + boxWidth + boxGap, y, boxWidth, 94)
  setText(9, 'bold', [51, 65, 85])
  doc.text('SELLER', margin + 10, y + 17)
  doc.text('PAYMENT INFORMATION', margin + boxWidth + boxGap + 10, y + 17)
  setText(9, 'bold', [15, 23, 42])
  doc.text(pdfPlain(sellerDisplayName), margin + 10, y + 36)
  drawKeyValue('Email', seller.email, margin + 10, y + 52, boxWidth - 20)
  drawKeyValue('Phone', seller.phone, margin + 10, y + 66, boxWidth - 20)
  drawKeyValue('Seller ID', `#${pdfPlain(seller.id)}`, margin + 10, y + 80, boxWidth - 20)
  drawKeyValue('Bank', payment.bank_name, margin + boxWidth + boxGap + 10, y + 36, boxWidth - 20)
  drawKeyValue('Account', payment.account_name, margin + boxWidth + boxGap + 10, y + 50, boxWidth - 20)
  drawKeyValue('Account No', payment.account_number, margin + boxWidth + boxGap + 10, y + 64, boxWidth - 20)
  drawKeyValue('Branch', `${pdfPlain(payment.branch)} / SWIFT ${pdfPlain(payment.swift_code)}`, margin + boxWidth + boxGap + 10, y + 78, boxWidth - 20)
  y += 112

  drawBox(margin, y, contentWidth, 76, [248, 250, 252])
  setText(9, 'bold', [51, 65, 85])
  doc.text('TOTAL SUMMARY', margin + 10, y + 17)
  const cardGap = 7
  const cardWidth = (contentWidth - (cardGap * 4) - 20) / 5
  const cardTop = y + 24
  drawSummaryCard('Payment Status', statusLabel(summaryData.payment_status), margin + 10, cardTop, cardWidth)
  drawSummaryCard('Orders', Number(summaryData.orders_count || 0), margin + 10 + (cardWidth + cardGap), cardTop, cardWidth)
  drawSummaryCard('Order Commission', `LKR ${toMoney(summaryData.order_commission_value)}`, margin + 10 + ((cardWidth + cardGap) * 2), cardTop, cardWidth)
  drawSummaryCard('Affiliate', `LKR ${toMoney(summaryData.affiliate_commission_value)}`, margin + 10 + ((cardWidth + cardGap) * 3), cardTop, cardWidth)
  drawSummaryCard('Total Payable', `LKR ${toMoney(summaryData.total_commission_value)}`, margin + 10 + ((cardWidth + cardGap) * 4), cardTop, cardWidth)
  y += 94

  drawSectionTitle('Order List')
  drawTable(
    ['#', 'Order', 'Waybill', 'Customer', 'Completed', 'Commission'],
    orders.map((order, index) => [
      index + 1,
      `#${pdfPlain(order.id)}`,
      order.waybill_no,
      order.customer_name,
      formatPdfDateTime(order.completed_at),
      `LKR ${toMoney(order.commission_amount)}`,
    ]),
    [26, 58, 78, 140, 120, 80]
  )

  if (affiliateCommissions.length) {
    drawSectionTitle('Affiliate Commission List')
    drawTable(
      ['#', 'Order', 'Waybill', 'Referred Seller', 'Available At', 'Amount', 'Status'],
      affiliateCommissions.map((commission, index) => [
        index + 1,
        `#${pdfPlain(commission.order_id)}`,
        commission.order_waybill_no,
        commission.seller_name,
        formatPdfDateTime(commission.available_at),
        `LKR ${toMoney(commission.amount)}`,
        commission.status,
      ]),
      [24, 48, 70, 128, 94, 78, 60]
    )
  }

  const shortDisclaimer = 'System generated invoice. No signature required.'
  doc.setDrawColor(203, 213, 225)
  doc.setLineWidth(0.8)
  doc.line(margin, pageHeight - 42, pageWidth - margin, pageHeight - 42)
  setText(7, 'normal', [100, 116, 139])
  doc.text(shortDisclaimer, pageWidth / 2, pageHeight - 29, { align: 'center' })
  doc.text(`Generated by Nextep Sellers at ${generatedAt}`, pageWidth / 2, pageHeight - 18, { align: 'center' })

  return doc.output('blob')
}

const openMarkPaidConfirm = (invoice) => {
  selectedInvoiceForMarkPaid.value = invoice
  showMarkPaidModal.value = true
}

const closeMarkPaidConfirm = () => {
  showMarkPaidModal.value = false
  selectedInvoiceForMarkPaid.value = null
}

const confirmMarkPaid = async () => {
  const invoice = selectedInvoiceForMarkPaid.value
  if (!invoice?.id) return

  markingPaidId.value = invoice.id
  try {
    const { data } = await axios.post(`/api/admin/finance/invoices/${invoice.id}/mark-paid`)
    toast.success(data?.message || 'Invoice marked as paid.')
    closeMarkPaidConfirm()
    fetchInvoices()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to mark invoice as paid.')
  } finally {
    markingPaidId.value = null
  }
}
</script>
