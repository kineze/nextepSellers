<template>
  <div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-300">Invoices</p>
        <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">Seller Invoices</h2>
      </div>

      <div class="grid gap-2 sm:grid-cols-4">
        <select
          v-model="filters.status"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @change="onFilterChanged"
        >
          <option value="all">All</option>
          <option value="draft">Draft</option>
          <option value="paid">Paid</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <input
          v-model="filters.date_from"
          type="date"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @change="onFilterChanged"
        />

        <input
          v-model="filters.date_to"
          type="date"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @change="onFilterChanged"
        />

        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="Invoice ID"
          class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
          @keyup.enter="onFilterChanged"
        />
      </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
      <article class="rounded-xl border border-sky-200 bg-sky-50/70 p-3 dark:border-sky-500/30 dark:bg-sky-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Invoice Count</p>
        <p class="mt-1 text-lg font-bold text-sky-800 dark:text-sky-200">{{ summary.invoice_count }}</p>
      </article>
      <article class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Total Commission</p>
        <p class="mt-1 text-lg font-bold text-emerald-800 dark:text-emerald-200">LKR {{ toMoney(summary.total_commission_value) }}</p>
      </article>
    </div>

    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
      <p>Total {{ meta.total }} invoice records</p>
      <button
        type="button"
        class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
        @click="fetchInvoices"
      >
        Refresh
      </button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          <tr>
            <th class="px-3 py-3">Invoice</th>
            <th class="px-3 py-3">Date</th>
            <th class="px-3 py-3">Time</th>
            <th class="px-3 py-3 text-right">Orders</th>
            <th class="px-3 py-3 text-right">Commission</th>
            <th class="px-3 py-3">Status</th>
            <th class="px-3 py-3 text-right">PDF</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
          <tr v-if="loading">
            <td colspan="7" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">Loading invoices...</td>
          </tr>
          <tr v-else-if="!invoices.length">
            <td colspan="7" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No invoices found.</td>
          </tr>
          <tr v-for="invoice in invoices" :key="invoice.id" class="bg-white dark:bg-slate-900/40">
            <td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">#{{ invoice.id }}</td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(invoice.invoice_date) }}</td>
            <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ formatTime(invoice.invoice_time) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-sky-700 dark:text-sky-300">{{ Number(invoice.orders_count || 0) }}</td>
            <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(invoice.total_commission_value) }}</td>
            <td class="px-3 py-3">
              <span :class="statusClass(invoice.status)" class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">{{ invoice.status }}</span>
            </td>
            <td class="px-3 py-3 text-right">
              <button
                type="button"
                class="rounded-lg border border-sky-300 bg-sky-50 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wide text-sky-700 hover:bg-sky-100 disabled:opacity-50 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-300"
                :disabled="pdfGeneratingId === invoice.id"
                @click="openInvoicePdf(invoice)"
              >
                {{ pdfGeneratingId === invoice.id ? 'Opening...' : 'Open' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
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
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { jsPDF } from 'jspdf'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const invoices = ref([])
const pdfGeneratingId = ref(null)

const filters = reactive({
  status: 'all',
  search: '',
  date_from: '',
  date_to: '',
  page: 1,
  per_page: 10,
})

const meta = reactive({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const summary = reactive({ invoice_count: 0, total_commission_value: 0 })

const toMoney = (value) => Number(value || 0).toFixed(2)
const pdfPlain = (value, fallback = '-') => {
  const text = String(value ?? '').trim()
  return text || fallback
}

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return date.toLocaleDateString()
}

const formatDateTime = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return date.toLocaleString()
}

const formatTime = (value) => {
  if (!value) return '-'
  const date = new Date(String(value))
  if (!Number.isNaN(date.getTime())) {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
  }
  const match = String(value).match(/\b(\d{2}:\d{2}(?::\d{2})?)\b/)
  return match ? match[1] : String(value)
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

const fetchInvoices = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/seller/invoices', {
      params: {
        status: filters.status,
        search: filters.search || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        page: filters.page,
        per_page: filters.per_page,
      },
    })

    invoices.value = Array.isArray(data?.invoices) ? data.invoices : []
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

const changePage = (page) => {
  filters.page = page
  fetchInvoices()
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
      if (!context || !canvas.width || !canvas.height) return resolve(null)
      context.clearRect(0, 0, canvas.width, canvas.height)
      context.drawImage(image, 0, 0)
      resolve({ dataUrl: canvas.toDataURL('image/png'), width: canvas.width, height: canvas.height })
    }
    image.onerror = () => resolve(null)
    image.src = dataUrl
  })
}

const openInvoicePdf = async (invoice) => {
  const targetWindow = window.open('', '_blank')
  if (!targetWindow) {
    toast.error('Popup blocked. Please allow popups to open invoice PDFs.')
    return
  }

  targetWindow.document.write('<p style="font-family: Arial, sans-serif; padding: 24px;">Generating invoice PDF...</p>')
  pdfGeneratingId.value = invoice.id
  try {
    const { data } = await axios.get(`/api/seller/invoices/${invoice.id}/pdf-data`)
    const blob = await generateInvoicePdfBlob(data)
    const url = window.URL.createObjectURL(blob)
    targetWindow.location.href = url
    setTimeout(() => window.URL.revokeObjectURL(url), 60000)
  } catch (error) {
    targetWindow.close()
    toast.error(error?.response?.data?.message || 'Failed to open invoice PDF.')
  } finally {
    pdfGeneratingId.value = null
  }
}

const generateInvoicePdfBlob = async (data) => {
  const system = data?.system_data || {}
  const invoice = data?.invoice || {}
  const seller = data?.seller || {}
  const payment = data?.payment_information || {}
  const summaryData = data?.summary || {}
  const orders = Array.isArray(data?.orders) ? data.orders : []
  const affiliateCommissions = Array.isArray(data?.affiliate_commissions) ? data.affiliate_commissions : []
  const logoImage = await loadLogoForPdf(system.logo_url)
  const generatedAt = new Date().toLocaleString()

  const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' })
  const pageWidth = doc.internal.pageSize.getWidth()
  const pageHeight = doc.internal.pageSize.getHeight()
  const margin = 36
  const contentWidth = pageWidth - margin * 2
  let y = margin

  const setText = (size = 9, style = 'normal', color = [17, 24, 39]) => {
    doc.setFont('helvetica', style)
    doc.setFontSize(size)
    doc.setTextColor(...color)
  }
  const addPageIfNeeded = (height = 24) => {
    if (y + height <= pageHeight - margin - 42) return
    doc.addPage('a4', 'portrait')
    y = margin
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
    drawWrappedText(value, x + 72, top, maxWidth - 72, 10)
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
  doc.text(`Date: ${formatDate(invoice.invoice_date)}`, pageWidth - margin, margin + 38, { align: 'right' })
  doc.text(`Time: ${formatTime(invoice.invoice_time)}`, pageWidth - margin, margin + 52, { align: 'right' })

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
  doc.text(pdfPlain(sellerName(seller)), margin + 10, y + 36)
  drawKeyValue('Email', seller.email, margin + 10, y + 52, boxWidth - 20)
  drawKeyValue('Phone', seller.phone, margin + 10, y + 66, boxWidth - 20)
  drawKeyValue('Bank', payment.bank_name, margin + boxWidth + boxGap + 10, y + 36, boxWidth - 20)
  drawKeyValue('Account', payment.account_name, margin + boxWidth + boxGap + 10, y + 50, boxWidth - 20)
  drawKeyValue('Account No', payment.account_number, margin + boxWidth + boxGap + 10, y + 64, boxWidth - 20)
  drawKeyValue('Branch', `${pdfPlain(payment.branch)} / SWIFT ${pdfPlain(payment.swift_code)}`, margin + boxWidth + boxGap + 10, y + 78, boxWidth - 20)
  y += 112

  drawBox(margin, y, contentWidth, 76, [248, 250, 252])
  setText(9, 'bold', [51, 65, 85])
  doc.text('TOTAL SUMMARY', margin + 10, y + 17)
  const cardWidth = (contentWidth - 48) / 4
  const labels = [
    ['Payment Status', String(summaryData.payment_status || 'draft').toUpperCase()],
    ['Orders', Number(summaryData.orders_count || 0)],
    ['Affiliate', `LKR ${toMoney(summaryData.affiliate_commission_value)}`],
    ['Total Payable', `LKR ${toMoney(summaryData.total_commission_value)}`],
  ]
  labels.forEach(([label, value], index) => {
    const x = margin + 10 + index * (cardWidth + 7)
    drawBox(x, y + 24, cardWidth, 48, [255, 255, 255], [226, 232, 240])
    setText(7, 'bold', [100, 116, 139])
    doc.text(String(label).toUpperCase(), x + 8, y + 39)
    setText(10, 'bold', [15, 23, 42])
    doc.text(doc.splitTextToSize(pdfPlain(value), cardWidth - 16).slice(0, 2), x + 8, y + 55)
  })
  y += 94

  setText(12, 'bold', [15, 23, 42])
  doc.text('Order List', margin, y)
  y += 10
  drawTable(
    ['#', 'Order', 'Waybill', 'Customer', 'Completed', 'Commission'],
    orders.map((order, index) => [
      index + 1,
      `#${pdfPlain(order.id)}`,
      order.waybill_no,
      order.customer_name,
      formatDateTime(order.completed_at),
      `LKR ${toMoney(order.commission_amount)}`,
    ]),
    [26, 58, 78, 140, 120, 80]
  )

  if (affiliateCommissions.length) {
    addPageIfNeeded(40)
    y += 12
    setText(12, 'bold', [15, 23, 42])
    doc.text('Affiliate Commission List', margin, y)
    y += 10
    drawTable(
      ['#', 'Order', 'Waybill', 'Referred Seller', 'Available At', 'Amount', 'Status'],
      affiliateCommissions.map((commission, index) => [
        index + 1,
        `#${pdfPlain(commission.order_id)}`,
        commission.order_waybill_no,
        commission.seller_name,
        formatDateTime(commission.available_at),
        `LKR ${toMoney(commission.amount)}`,
        commission.status,
      ]),
      [24, 48, 70, 128, 94, 78, 60]
    )
  }

  doc.setDrawColor(203, 213, 225)
  doc.line(margin, pageHeight - 42, pageWidth - margin, pageHeight - 42)
  setText(7, 'normal', [100, 116, 139])
  doc.text('System generated invoice. No signature required.', pageWidth / 2, pageHeight - 29, { align: 'center' })
  doc.text(`Generated by Nextep Sellers at ${generatedAt}`, pageWidth / 2, pageHeight - 18, { align: 'center' })

  return doc.output('blob')
}

onMounted(fetchInvoices)
</script>
