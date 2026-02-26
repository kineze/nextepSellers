<template>
  <div class="space-y-6">
    <div class="rounded-3xl border border-slate-200/70 bg-white/80 p-7 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
      <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-blue-600 dark:text-blue-300">Order Submission</p>
      <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white">Checkout</h1>
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
        Review your cart and add customer details before submitting the order.
      </p>
    </div>

    <div v-if="!items.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/80 p-10 text-center text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-400">
      <p class="text-sm">Your cart is empty.</p>
      <a :href="productsUrl" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900">
        <i class="fas fa-box-open"></i>
        Browse Products
      </a>
    </div>

    <div v-else class="grid gap-6 xl:grid-cols-12">
      <section class="space-y-4 xl:col-span-8">
        <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
          <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Order Items</h2>

          <div class="mt-4 space-y-3">
            <article v-for="item in items" :key="item.key" class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900/60">
              <div class="flex gap-3">
                <img v-if="item.image" :src="item.image" :alt="item.title" class="h-16 w-16 rounded-lg object-cover" />
                <div v-else class="flex h-16 w-16 items-center justify-center rounded-lg bg-slate-100 text-[10px] text-slate-500 dark:bg-slate-800 dark:text-slate-400">No image</div>

                <div class="min-w-0 flex-1">
                  <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ item.title }}</p>
                  <p v-if="item.sku" class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">SKU {{ item.sku }}</p>
                  <p v-if="item.productCode" class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ item.productCode }}</p>
                  <p v-if="attributeText(item.attributes)" class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ attributeText(item.attributes) }}</p>
                  <p v-if="item.price !== null" class="mt-1 text-xs font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(item.price) }} each</p>
                </div>
              </div>

              <div class="mt-3 flex items-center justify-between gap-2">
                <div class="inline-flex items-center rounded-xl border border-slate-300 dark:border-slate-700">
                  <button type="button" class="px-3 py-1.5 text-sm font-bold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800" @click="updateItemQty(item.key, Math.max(1, item.qty - 1))">-</button>
                  <input
                    type="number"
                    min="1"
                    :value="item.qty"
                    class="w-14 border-x border-slate-300 bg-transparent px-1 py-1.5 text-center text-sm outline-none dark:border-slate-700"
                    @input="onQtyInput(item.key, $event)"
                  >
                  <button type="button" class="px-3 py-1.5 text-sm font-bold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800" @click="updateItemQty(item.key, item.qty + 1)">+</button>
                </div>

                <div class="flex items-center gap-4">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">
                    LKR {{ toMoney((Number(item.price || 0) * Number(item.qty || 0))) }}
                  </p>
                  <button type="button" class="text-xs font-semibold text-rose-600 hover:text-rose-700" @click="removeItem(item.key)">Remove</button>
                </div>
              </div>
            </article>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Customer Details</h2>
            <div v-if="recentCustomers.length" class="sm:w-72">
              <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Recent Customers</label>
              <div ref="recentDropdownWrapper" class="relative">
                <input
                  v-model.trim="recentCustomerSearch"
                  type="text"
                  placeholder="Search by phone or name"
                  autocomplete="off"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                  @focus="openRecentDropdown"
                  @input="showRecentDropdown = true"
                  @blur="closeRecentDropdownWithDelay"
                />

                <div
                  v-if="showRecentDropdown"
                  class="absolute z-20 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                  :class="recentOpenUpward ? 'bottom-full mb-1' : 'mt-1'"
                >
                  <ul class="max-h-56 overflow-y-auto py-1">
                    <li
                      v-for="(item, idx) in filteredRecentCustomers"
                      :key="`recent-${idx}`"
                      class="cursor-pointer px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                      @mousedown.prevent="applyRecentCustomer(item)"
                    >
                      <p class="font-semibold">{{ item.customer_name || 'Customer' }}</p>
                      <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ item.phone || '-' }}</p>
                    </li>
                    <li v-if="!filteredRecentCustomers.length" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                      No recent customer found
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <form class="mt-4 grid gap-4 sm:grid-cols-2" @submit.prevent>
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Customer Name <span class="text-rose-500">*</span></label>
              <input
                v-model.trim="form.customer_name"
                type="text"
                placeholder="e.g. Nimal Perera"
                autocomplete="name"
                :class="inputClass('customer_name')"
                @blur="touchField('customer_name')"
              />
              <p v-if="errors.customer_name" class="mt-1 text-xs text-rose-600">{{ errors.customer_name }}</p>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Primary Phone <span class="text-rose-500">*</span></label>
              <input
                v-model.trim="form.phone"
                type="text"
                placeholder="e.g. +94771234567"
                autocomplete="tel"
                :class="inputClass('phone')"
                @blur="touchField('phone')"
              />
              <p v-if="errors.phone" class="mt-1 text-xs text-rose-600">{{ errors.phone }}</p>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Additional Phone</label>
              <input
                v-model.trim="form.additional_phone"
                type="text"
                placeholder="Optional alternate number"
                autocomplete="tel"
                :class="inputClass('additional_phone')"
                @blur="touchField('additional_phone')"
              />
              <p v-if="errors.additional_phone" class="mt-1 text-xs text-rose-600">{{ errors.additional_phone }}</p>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Email</label>
              <input
                v-model.trim="form.email"
                type="email"
                placeholder="e.g. customer@email.com"
                autocomplete="email"
                :class="inputClass('email')"
                @blur="touchField('email')"
              />
              <p v-if="errors.email" class="mt-1 text-xs text-rose-600">{{ errors.email }}</p>
            </div>

            <div class="sm:col-span-2">
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Address <span class="text-rose-500">*</span></label>
              <textarea
                v-model.trim="form.address"
                rows="3"
                placeholder="House no, street, area landmarks"
                autocomplete="street-address"
                :class="inputClass('address')"
                @blur="touchField('address')"
              ></textarea>
              <p v-if="errors.address" class="mt-1 text-xs text-rose-600">{{ errors.address }}</p>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">City</label>
              <div ref="cityDropdownWrapper" class="relative">
                <input
                  v-model.trim="citySearch"
                  type="text"
                  placeholder="Search and select city"
                  autocomplete="off"
                  :class="inputClass('city')"
                  @focus="openCityDropdown"
                  @input="onCitySearchInput"
                  @blur="closeCityDropdownWithDelay"
                />
                <button
                  v-if="form.city_id"
                  type="button"
                  class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                  @click="clearSelectedCity"
                >
                  Clear
                </button>

                <div
                  v-if="showCityDropdown"
                  class="absolute z-20 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                  :class="cityOpenUpward ? 'bottom-full mb-1' : 'mt-1'"
                >
                  <div v-if="cityLoading" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">Loading cities...</div>
                  <ul v-else class="max-h-56 overflow-y-auto py-1">
                    <li
                      v-for="city in cityOptions"
                      :key="city.id"
                      class="cursor-pointer px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                      @mousedown.prevent="selectCity(city)"
                    >
                      {{ city.name_en }}
                    </li>
                    <li v-if="!cityOptions.length" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                      No cities found
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Notes</label>
              <input
                v-model.trim="form.notes"
                type="text"
                placeholder="Optional delivery notes"
                :class="inputClass('notes')"
              />
            </div>

            <div class="sm:col-span-2">
              <label class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                <input v-model="saveCustomerForReuse" type="checkbox" class="rounded border-slate-300 accent-slate-700 dark:border-slate-700 dark:accent-slate-300" />
                Save customer for quick reuse
              </label>
            </div>
          </form>
        </div>
      </section>

      <aside class="space-y-4 xl:col-span-4 xl:sticky xl:top-24 xl:self-start">
        <div class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
          <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Order Summary</h2>

          <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
            <p class="flex items-center justify-between"><span>Items</span><span class="font-semibold text-slate-900 dark:text-white">{{ totalItems }}</span></p>
            <p class="flex items-center justify-between"><span>Subtotal</span><span class="font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(subtotal) }}</span></p>
            <p class="flex items-center justify-between"><span>Delivery</span><span class="font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(deliveryCharge) }}</span></p>
            <p class="flex items-center justify-between"><span>Discount</span><span class="font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(discount) }}</span></p>
            <p class="mt-2 flex items-center justify-between border-t border-slate-200 pt-2 text-base dark:border-slate-700"><span class="font-semibold">Total</span><span class="font-bold text-slate-900 dark:text-white">LKR {{ toMoney(grandTotal) }}</span></p>
          </div>

          <button
            type="button"
            class="mt-4 w-full rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="submitting"
            @click="submitOrder"
          >
            <span v-if="submitting">Submitting...</span>
            <span v-else>Submit Order</span>
          </button>

          <button
            type="button"
            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            @click="clear"
          >
            Clear Cart
          </button>

          <p class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
            Submitting will create the customer (or reuse existing by phone) and create the order in the backend.
          </p>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useSellerCart } from '../composables/useSellerCart'

const props = defineProps({
  productsUrl: { type: String, required: true },
})

const toast = useToast()

const {
  items,
  subtotal,
  totalItems,
  removeItem,
  updateItemQty,
  clear,
} = useSellerCart()

const form = reactive({
  customer_name: '',
  phone: '',
  additional_phone: '',
  email: '',
  address: '',
  city_id: null,
  city: '',
  notes: '',
})
const errors = reactive({})
const recentCustomers = ref([])
const saveCustomerForReuse = ref(true)
const recentCustomerSearch = ref('')
const showRecentDropdown = ref(false)
const recentDropdownWrapper = ref(null)
const recentOpenUpward = ref(false)

const submitting = ref(false)
const discount = ref(0)
const RECENT_CUSTOMERS_KEY = 'nextep-seller-recent-customers'
const citySearch = ref('')
const cityOptions = ref([])
const cityLoading = ref(false)
const showCityDropdown = ref(false)
const cityDropdownWrapper = ref(null)
const cityOpenUpward = ref(false)
let citySearchTimeout = null

const deliveryCharge = computed(() => {
  return items.value.reduce((maxFee, item) => {
    if (item?.isFreeShipping) return maxFee
    const fee = Number(item?.deliveryFee || 0)
    if (!Number.isFinite(fee)) return maxFee
    return Math.max(maxFee, fee)
  }, 0)
})

const grandTotal = computed(() => {
  return Number(subtotal.value || 0) + Number(deliveryCharge.value || 0) - Number(discount.value || 0)
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const attributeText = (attributes) => {
  if (!attributes || typeof attributes !== 'object') return ''

  return Object.entries(attributes)
    .map(([key, meta]) => {
      const label = String(meta?.label || meta?.value || '').trim()
      return label ? `${key}: ${label}` : ''
    })
    .filter(Boolean)
    .join(', ')
}

const onQtyInput = (itemKey, event) => {
  const value = Number(event.target.value || 1)
  updateItemQty(itemKey, Number.isFinite(value) && value > 0 ? value : 1)
}

const inputClass = (field) => {
  const base = 'w-full rounded-xl border bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:bg-slate-950 dark:text-slate-100'
  const good = ' border-slate-300 dark:border-slate-700'
  const bad = ' border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 dark:border-rose-500'
  return `${base}${errors[field] ? bad : good}`
}

const normalizePhone = (value) => String(value || '').replace(/\s+/g, '').trim()

const validateField = (field) => {
  if (field === 'customer_name') {
    errors.customer_name = form.customer_name && form.customer_name.length >= 2 ? '' : 'Customer name is required.'
  } else if (field === 'phone') {
    const phone = normalizePhone(form.phone)
    errors.phone = /^\+?[0-9]{8,15}$/.test(phone) ? '' : 'Enter a valid phone number.'
  } else if (field === 'additional_phone') {
    const phone = normalizePhone(form.additional_phone)
    errors.additional_phone = !phone || /^\+?[0-9]{8,15}$/.test(phone) ? '' : 'Enter a valid additional phone.'
  } else if (field === 'email') {
    errors.email = !form.email || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email) ? '' : 'Enter a valid email.'
  } else if (field === 'address') {
    errors.address = form.address && form.address.length >= 6 ? '' : 'Address is required.'
  }
}

const touchField = (field) => {
  validateField(field)
}

const validateForm = () => {
  ;['customer_name', 'phone', 'additional_phone', 'email', 'address'].forEach(validateField)
  return !errors.customer_name && !errors.phone && !errors.additional_phone && !errors.email && !errors.address
}

const filteredRecentCustomers = computed(() => {
  const term = recentCustomerSearch.value.trim().toLowerCase()
  if (!term) return recentCustomers.value

  return recentCustomers.value.filter((row) => {
    const phone = String(row?.phone || '').toLowerCase()
    const name = String(row?.customer_name || '').toLowerCase()
    return phone.includes(term) || name.includes(term)
  })
})

const loadRecentCustomers = () => {
  try {
    const rows = JSON.parse(window.localStorage.getItem(RECENT_CUSTOMERS_KEY) || '[]')
    recentCustomers.value = Array.isArray(rows) ? rows : []
  } catch {
    recentCustomers.value = []
  }
}

const applyRecentCustomer = (customer) => {
  if (!customer) return

  form.customer_name = customer.customer_name || ''
  form.phone = customer.phone || ''
  form.additional_phone = customer.additional_phone || ''
  form.email = customer.email || ''
  form.address = customer.address || ''
  form.city_id = customer.city_id || null
  form.city = customer.city || ''
  citySearch.value = customer.city || ''
  form.notes = customer.notes || ''
  recentCustomerSearch.value = `${customer.customer_name || 'Customer'} - ${customer.phone || ''}`
  showRecentDropdown.value = false
  ;['customer_name', 'phone', 'additional_phone', 'email', 'address'].forEach(validateField)
}

const updateRecentDropdownDirection = () => {
  const wrapper = recentDropdownWrapper.value
  if (!wrapper) return

  const rect = wrapper.getBoundingClientRect()
  const estimatedDropdownHeight = 240
  const spaceBelow = window.innerHeight - rect.bottom
  const spaceAbove = rect.top

  recentOpenUpward.value = spaceBelow < estimatedDropdownHeight && spaceAbove > spaceBelow
}

const openRecentDropdown = () => {
  updateRecentDropdownDirection()
  showRecentDropdown.value = true
  nextTick(updateRecentDropdownDirection)
}

const closeRecentDropdownWithDelay = () => {
  setTimeout(() => {
    showRecentDropdown.value = false
  }, 120)
}

const persistRecentCustomer = () => {
  if (!saveCustomerForReuse.value) return

  const snapshot = {
    customer_name: form.customer_name,
    phone: form.phone,
    additional_phone: form.additional_phone || '',
    email: form.email || '',
    address: form.address,
    city_id: form.city_id || null,
    city: form.city || '',
    notes: form.notes || '',
  }

  const deduped = recentCustomers.value.filter((row) => !(row.phone === snapshot.phone && row.customer_name === snapshot.customer_name))
  deduped.unshift(snapshot)
  recentCustomers.value = deduped.slice(0, 10)
  window.localStorage.setItem(RECENT_CUSTOMERS_KEY, JSON.stringify(recentCustomers.value))
}

const fetchCities = async (search = '') => {
  cityLoading.value = true
  try {
    const { data } = await axios.get('/api/seller/cities', {
      params: { search, limit: 20 },
    })
    cityOptions.value = data?.cities || []
  } catch {
    cityOptions.value = []
  } finally {
    cityLoading.value = false
  }
}

const openCityDropdown = () => {
  updateCityDropdownDirection()
  showCityDropdown.value = true
  fetchCities(citySearch.value)
  nextTick(updateCityDropdownDirection)
}

const closeCityDropdownWithDelay = () => {
  setTimeout(() => {
    showCityDropdown.value = false
  }, 120)
}

const onCitySearchInput = () => {
  form.city_id = null
  form.city = citySearch.value

  clearTimeout(citySearchTimeout)
  citySearchTimeout = setTimeout(() => {
    fetchCities(citySearch.value)
    nextTick(updateCityDropdownDirection)
  }, 250)
}

const selectCity = (city) => {
  form.city_id = city.id
  form.city = city.name_en
  citySearch.value = city.name_en
  showCityDropdown.value = false
}

const clearSelectedCity = () => {
  form.city_id = null
  form.city = ''
  citySearch.value = ''
}

const updateCityDropdownDirection = () => {
  const wrapper = cityDropdownWrapper.value
  if (!wrapper) return

  const rect = wrapper.getBoundingClientRect()
  const estimatedDropdownHeight = 240
  const spaceBelow = window.innerHeight - rect.bottom
  const spaceAbove = rect.top

  cityOpenUpward.value = spaceBelow < estimatedDropdownHeight && spaceAbove > spaceBelow
}

const submitOrder = async () => {
  if (!items.value.length) {
    toast.error('Cart is empty.')
    return
  }

  const isValid = validateForm()
  if (!isValid) {
    toast.error('Please correct customer form errors.')
    return
  }

  submitting.value = true

  try {
    const payload = {
      order_datetime: new Date().toISOString(),
      status: 'draft',
      net_total: Number(subtotal.value || 0),
      delivery_charge: Number(deliveryCharge.value || 0),
      total_discount: Number(discount.value || 0),
      commission_amount: 0,
      total_collectable_amount: Number(grandTotal.value || 0),
      customer: {
        name: form.customer_name,
        phone: form.phone,
        additional_phone: form.additional_phone || null,
        email: form.email || null,
        address: form.address,
        city: form.city || null,
        city_id: form.city_id || null,
        notes: form.notes || null,
      },
      items: items.value.map((item) => ({
        product_id: item.productId,
        product_variant_id: item.variantId,
        quantity: Number(item.qty || 0),
        price: Number(item.price || 0),
      })),
    }

    const { data } = await axios.post('/api/seller/orders', payload)
    persistRecentCustomer()

    clear()

    form.customer_name = ''
    form.phone = ''
    form.additional_phone = ''
    form.email = ''
    form.address = ''
    form.city_id = null
    form.city = ''
    citySearch.value = ''
    form.notes = ''
    Object.keys(errors).forEach((k) => { errors[k] = '' })

    toast.success(data?.message || 'Order submitted successfully.')
    window.dispatchEvent(new CustomEvent('seller-order-created'))
  } catch (error) {
    const message = error?.response?.data?.message || 'Unable to submit order.'
    toast.error(message)
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadRecentCustomers()
  if (recentCustomers.value.length > 0) {
    recentCustomerSearch.value = ''
  }
  window.addEventListener('resize', updateCityDropdownDirection)
  window.addEventListener('scroll', updateCityDropdownDirection, true)
  window.addEventListener('resize', updateRecentDropdownDirection)
  window.addEventListener('scroll', updateRecentDropdownDirection, true)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateCityDropdownDirection)
  window.removeEventListener('scroll', updateCityDropdownDirection, true)
  window.removeEventListener('resize', updateRecentDropdownDirection)
  window.removeEventListener('scroll', updateRecentDropdownDirection, true)
  clearTimeout(citySearchTimeout)
})
</script>
