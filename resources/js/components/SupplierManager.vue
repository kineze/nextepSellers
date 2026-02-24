<template>
  <div class="p-6 w-full">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
      <div>
        <p class="text-[0.7rem] uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Inventory</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Supplier Manager</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Manage suppliers and link products with supplier costs, lead days, and variant supply data.</p>
      </div>
      <button
        @click="openCreate"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 transition hover:bg-black dark:bg-white dark:text-slate-900"
      >
        <i class="fas fa-plus"></i> New Supplier
      </button>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
      <div class="lg:col-span-2 rounded-2xl border border-slate-200/70 bg-white/80 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="flex flex-col gap-3 border-b border-slate-200/70 p-4 dark:border-slate-800/70 sm:flex-row sm:items-center sm:justify-between">
          <input
            v-model="search"
            @input="debounceSearch"
            type="search"
            placeholder="Search suppliers..."
            class="w-full sm:w-80 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
          />
        </div>

        <div class="overflow-x-auto p-4">
          <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
            <thead class="text-[0.7rem] uppercase tracking-wider text-slate-500 dark:text-slate-400">
              <tr>
                <th class="px-3 py-3">Name</th>
                <th class="px-3 py-3">Company</th>
                <th class="px-3 py-3">Email</th>
                <th class="px-3 py-3">Linked Products</th>
                <th class="px-3 py-3">Status</th>
                <th class="px-3 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
              <tr v-if="suppliers.length === 0">
                <td colspan="6" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">No suppliers found</td>
              </tr>
              <tr v-for="supplier in suppliers" :key="supplier.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                <td class="px-3 py-4 font-medium text-slate-900 dark:text-white">{{ supplier.name }}</td>
                <td class="px-3 py-4">{{ supplier.company_name || '-' }}</td>
                <td class="px-3 py-4">{{ supplier.email || '-' }}</td>
                <td class="px-3 py-4">{{ supplier.supplier_products_count ?? 0 }}</td>
                <td class="px-3 py-4">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide"
                    :class="supplier.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
                  >
                    {{ supplier.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-3 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      type="button"
                      @click="openLinkModal(supplier)"
                      class="rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                      Link Products
                    </button>
                    <button
                      type="button"
                      @click="startEdit(supplier)"
                      class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-semibold text-white hover:bg-black dark:bg-white dark:text-slate-900"
                    >
                      Edit
                    </button>
                    <button
                      type="button"
                      @click="removeSupplier(supplier)"
                      class="rounded-lg bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-700"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200/70 px-4 py-4 text-sm text-slate-600 dark:border-slate-800/70 dark:text-slate-300 sm:flex-row sm:items-center sm:justify-between">
          <div>
            Showing
            <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.from || 0 }}</span>
            to
            <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.to || 0 }}</span>
            of
            <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.total || 0 }}</span>
          </div>

          <div class="flex items-center gap-2">
            <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">Prev</button>
            <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">Next</button>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ form.id ? 'Edit Supplier' : 'Create Supplier' }}</h3>
          <button v-if="form.id" type="button" @click="openCreate" class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Clear</button>
        </div>

        <form @submit.prevent="saveSupplier" class="space-y-3">
          <div>
            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Name</label>
            <input v-model="form.name" required type="text" placeholder="Supplier name" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Company</label>
            <input v-model="form.company_name" type="text" placeholder="Company name" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Email</label>
              <input v-model="form.email" type="email" placeholder="supplier@email.com" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Phone</label>
              <input v-model="form.phone" type="text" placeholder="+94..." class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Website</label>
              <input v-model="form.website" type="text" placeholder="https://..." class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Tax Number</label>
              <input v-model="form.tax_number" type="text" placeholder="Tax number" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">General Details</label>
            <textarea v-model="form.general_details" rows="3" placeholder="Payment terms, lead time, notes..." class="w-full resize-none rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
          </div>

          <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
            <input type="checkbox" v-model="form.is_active" class="rounded border-slate-300 accent-slate-700 dark:border-slate-700 dark:accent-slate-300" />
            Active
          </label>

          <button type="submit" :disabled="saving" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-black disabled:opacity-50 dark:bg-white dark:text-slate-900">
            {{ saving ? 'Saving...' : (form.id ? 'Update Supplier' : 'Save Supplier') }}
          </button>
        </form>
      </div>
    </div>

    <transition name="fade">
      <div
        v-if="showLinkProductModal"
        class="fixed inset-0 z-[990] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        @click.self="closeLinkModal"
      >
        <div class="max-h-[92vh] w-full max-w-6xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-slate-700">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Link Product to Supplier</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ selectedSupplier?.name }} {{ selectedSupplier?.company_name ? `(${selectedSupplier.company_name})` : '' }}
              </p>
            </div>
            <button type="button" @click="closeLinkModal" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Close</button>
          </div>

          <div class="grid max-h-[calc(92vh-58px)] gap-4 overflow-y-auto p-5 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
              <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                <h4 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">Select Product</h4>
                <div class="grid gap-3 md:grid-cols-2">
                  <input
                    v-model="productSearch"
                    @input="debounceProductSearch"
                    type="search"
                    placeholder="Search products by title/code"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                  />
                  <select
                    v-model="linkForm.product_id"
                    @change="onProductSelected"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                  >
                    <option :value="null">Select product</option>
                    <option v-for="product in productOptions" :key="product.id" :value="product.id">
                      {{ product.title }} ({{ product.product_code }})
                    </option>
                  </select>
                </div>
              </div>

              <div v-if="selectedProduct" class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                <div class="mb-3 flex items-start justify-between gap-3">
                  <div>
                    <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ selectedProduct.title }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Code: {{ selectedProduct.product_code }} | Variants: {{ selectedProduct.has_varients ? 'Yes' : 'No' }}</p>
                  </div>
                </div>

                <div class="mb-3 grid gap-3 md:grid-cols-2">
                  <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Supplier Product Code</label>
                    <input v-model="linkForm.supplier_product_code" type="text" placeholder="Supplier code" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" />
                  </div>
                  <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Notes</label>
                    <input v-model="linkForm.notes" type="text" placeholder="General notes" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" />
                  </div>
                </div>

                <div v-if="!selectedProduct.has_varients" class="grid gap-3 md:grid-cols-3">
                  <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Cost</label>
                    <input v-model.number="linkForm.cost" type="number" min="0" step="0.01" placeholder="0.00" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" />
                  </div>
                  <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Lead Days</label>
                    <input v-model.number="linkForm.lead_days" type="number" min="0" placeholder="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" />
                  </div>
                  <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">MOQ</label>
                    <input v-model.number="linkForm.moq" type="number" min="0" placeholder="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500/20 dark:border-slate-700 dark:bg-slate-950" />
                  </div>
                </div>

                <div v-else>
                  <div class="mb-2 flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Supplier Variant Offering</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Select the variants this supplier can provide</p>
                  </div>

                  <div class="max-h-80 overflow-auto rounded-lg border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-xs">
                      <thead class="bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <tr>
                          <th class="px-2 py-2 text-left">Provide</th>
                          <th class="px-2 py-2 text-left">Variant</th>
                          <th class="px-2 py-2 text-left">Supplier SKU</th>
                          <th class="px-2 py-2 text-left">Cost</th>
                          <th class="px-2 py-2 text-left">Lead Days</th>
                          <th class="px-2 py-2 text-left">MOQ</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="row in variantRows" :key="row.varient_id" class="border-t border-slate-200 dark:border-slate-700">
                          <td class="px-2 py-2 align-top">
                            <input type="checkbox" v-model="row.enabled" class="mt-1 rounded border-slate-300 accent-slate-700 dark:border-slate-700 dark:accent-slate-300" />
                          </td>
                          <td class="px-2 py-2 align-top">{{ formatVariantLabel(row.varient) }}</td>
                          <td class="px-2 py-2"><input v-model="row.supplier_sku" type="text" :disabled="!row.enabled" placeholder="SKU" class="w-28 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950" /></td>
                          <td class="px-2 py-2"><input v-model.number="row.cost" type="number" min="0" step="0.01" :disabled="!row.enabled" placeholder="0.00" class="w-20 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950" /></td>
                          <td class="px-2 py-2"><input v-model.number="row.lead_days" type="number" min="0" :disabled="!row.enabled" placeholder="0" class="w-16 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950" /></td>
                          <td class="px-2 py-2"><input v-model.number="row.moq" type="number" min="0" :disabled="!row.enabled" placeholder="0" class="w-16 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950" /></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2">
                  <button type="button" @click="resetLinkForm" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Reset</button>
                  <button type="button" @click="saveLink" :disabled="linkSaving" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-black disabled:opacity-50 dark:bg-white dark:text-slate-900">{{ linkSaving ? 'Saving...' : 'Save Link' }}</button>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                <h4 class="mb-2 text-sm font-semibold text-slate-900 dark:text-white">Supplier Details</h4>
                <div class="space-y-1 text-xs text-slate-600 dark:text-slate-300">
                  <p><span class="font-semibold">Name:</span> {{ supplierDetail?.name || '-' }}</p>
                  <p><span class="font-semibold">Company:</span> {{ supplierDetail?.company_name || '-' }}</p>
                  <p><span class="font-semibold">Email:</span> {{ supplierDetail?.email || '-' }}</p>
                  <p><span class="font-semibold">Phone:</span> {{ supplierDetail?.phone || '-' }}</p>
                </div>
              </div>

              <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                <div class="mb-2 flex items-center justify-between">
                  <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Linked Products</h4>
                  <span class="text-xs text-slate-500 dark:text-slate-400">{{ linkedProducts.length }}</span>
                </div>

                <div v-if="!linkedProducts.length" class="text-xs text-slate-500 dark:text-slate-400">No linked products.</div>

                <div v-else class="max-h-80 space-y-2 overflow-auto pr-1">
                  <div
                    v-for="row in linkedProducts"
                    :key="row.id"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs dark:border-slate-700"
                  >
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ row.product?.title }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ row.product?.product_code }}</p>
                    <p class="text-slate-500 dark:text-slate-400">Cost: {{ row.cost ?? '-' }} | Lead: {{ row.lead_days ?? '-' }} day(s)</p>
                    <p class="text-slate-500 dark:text-slate-400">Variants linked: {{ (row.supplier_varients || []).length }}</p>
                    <div class="mt-2 flex items-center gap-2">
                      <button type="button" @click="editLinkedProduct(row)" class="rounded border border-slate-300 px-2 py-1 text-[10px] font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Edit</button>
                      <button type="button" @click="unlinkProduct(row)" class="rounded bg-rose-600 px-2 py-1 text-[10px] font-semibold text-white hover:bg-rose-700">Unlink</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const suppliers = ref([])
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 })
const search = ref('')
const saving = ref(false)
let searchTimer = null

const form = ref({
  id: null,
  name: '',
  company_name: '',
  email: '',
  phone: '',
  website: '',
  tax_number: '',
  general_details: '',
  is_active: true,
})

const showLinkProductModal = ref(false)
const selectedSupplier = ref(null)
const supplierDetail = ref(null)
const productOptions = ref([])
const productSearch = ref('')
const linkSaving = ref(false)
const linkForm = ref({
  product_id: null,
  supplier_product_code: '',
  cost: null,
  lead_days: null,
  moq: null,
  notes: '',
})
const variantRows = ref([])
let productSearchTimer = null

const linkedProducts = computed(() => supplierDetail.value?.supplier_products || [])
const selectedProduct = computed(() => {
  return productOptions.value.find((product) => product.id === linkForm.value.product_id) || null
})

const fetchSuppliers = async (page = 1) => {
  try {
    const res = await axios.get('/api/suppliers', {
      params: { search: search.value, page, per_page: pagination.value.per_page },
    })
    suppliers.value = res.data.suppliers || []
    pagination.value = res.data.pagination || pagination.value
  } catch {
    toast.error('Failed to load suppliers.')
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchSuppliers(1), 350)
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchSuppliers(page)
}

const openCreate = () => {
  form.value = {
    id: null,
    name: '',
    company_name: '',
    email: '',
    phone: '',
    website: '',
    tax_number: '',
    general_details: '',
    is_active: true,
  }
}

const startEdit = (supplier) => {
  form.value = {
    id: supplier.id,
    name: supplier.name || '',
    company_name: supplier.company_name || '',
    email: supplier.email || '',
    phone: supplier.phone || '',
    website: supplier.website || '',
    tax_number: supplier.tax_number || '',
    general_details: supplier.general_details || '',
    is_active: !!supplier.is_active,
  }
}

const saveSupplier = async () => {
  const payload = {
    name: form.value.name,
    company_name: form.value.company_name || null,
    email: form.value.email || null,
    phone: form.value.phone || null,
    website: form.value.website || null,
    tax_number: form.value.tax_number || null,
    general_details: form.value.general_details || null,
    is_active: form.value.is_active ? 1 : 0,
  }

  try {
    saving.value = true
    if (form.value.id) {
      await axios.put(`/api/suppliers/${form.value.id}`, payload)
      toast.success('Supplier updated.')
    } else {
      await axios.post('/api/suppliers', payload)
      toast.success('Supplier created.')
    }
    openCreate()
    fetchSuppliers(pagination.value.current_page || 1)
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors || {}
      Object.values(errors).flat().forEach((m) => toast.error(m))
    } else {
      toast.error(error.response?.data?.message || 'Failed to save supplier.')
    }
  } finally {
    saving.value = false
  }
}

const removeSupplier = async (supplier) => {
  if (!confirm(`Delete supplier \"${supplier.name}\"?`)) return

  try {
    await axios.delete(`/api/suppliers/${supplier.id}`)
    toast.success('Supplier deleted.')
    if (form.value.id === supplier.id) openCreate()
    fetchSuppliers(1)
  } catch {
    toast.error('Failed to delete supplier.')
  }
}

const fetchSupplierDetail = async (supplierId) => {
  const res = await axios.get(`/api/suppliers/${supplierId}`)
  supplierDetail.value = res.data
}

const fetchProductOptions = async () => {
  try {
    const res = await axios.get('/api/suppliers/product-options', {
      params: { search: productSearch.value || '' },
    })
    productOptions.value = res.data.products || []
  } catch {
    toast.error('Failed to load product options.')
  }
}

const debounceProductSearch = () => {
  clearTimeout(productSearchTimer)
  productSearchTimer = setTimeout(fetchProductOptions, 300)
}

const resetLinkForm = () => {
  linkForm.value = {
    product_id: null,
    supplier_product_code: '',
    cost: null,
    lead_days: null,
    moq: null,
    notes: '',
  }
  variantRows.value = []
}

const buildVariantRows = (product, existingMapping = null) => {
  const existingVarients = new Map((existingMapping?.supplier_varients || []).map((item) => [item.varient_id, item]))

  variantRows.value = (product?.varients || []).map((varient) => {
    const existing = existingVarients.get(varient.id)
    return {
      varient_id: varient.id,
      varient,
      enabled: !!existing,
      supplier_sku: existing?.supplier_sku || '',
      cost: existing?.cost ?? null,
      lead_days: existing?.lead_days ?? null,
      moq: existing?.moq ?? null,
      notes: existing?.notes || '',
      is_active: existing ? !!existing.is_active : true,
    }
  })
}

const applyExistingMapping = (productId) => {
  const mapping = linkedProducts.value.find((item) => item.product_id === productId)
  if (!mapping) {
    linkForm.value.supplier_product_code = ''
    linkForm.value.cost = null
    linkForm.value.lead_days = null
    linkForm.value.moq = null
    linkForm.value.notes = ''
    buildVariantRows(selectedProduct.value, null)
    return
  }

  linkForm.value.supplier_product_code = mapping.supplier_product_code || ''
  linkForm.value.cost = mapping.cost ?? null
  linkForm.value.lead_days = mapping.lead_days ?? null
  linkForm.value.moq = mapping.moq ?? null
  linkForm.value.notes = mapping.notes || ''
  buildVariantRows(selectedProduct.value, mapping)
}

const onProductSelected = () => {
  if (!linkForm.value.product_id) {
    variantRows.value = []
    return
  }
  applyExistingMapping(linkForm.value.product_id)
}

const openLinkModal = async (supplier) => {
  selectedSupplier.value = supplier
  showLinkProductModal.value = true
  resetLinkForm()

  try {
    await Promise.all([
      fetchSupplierDetail(supplier.id),
      fetchProductOptions(),
    ])
  } catch {
    closeLinkModal()
  }
}

const closeLinkModal = () => {
  showLinkProductModal.value = false
  selectedSupplier.value = null
  supplierDetail.value = null
  productSearch.value = ''
  productOptions.value = []
  resetLinkForm()
}

const formatVariantLabel = (varient) => {
  const attributes = varient?.attributes || {}
  const pairs = Object.entries(attributes).map(([k, v]) => {
    if (v && typeof v === 'object') {
      return `${k}: ${v.name || v.color || '-'}`
    }
    return `${k}: ${v}`
  })
  return `${varient?.sku || '-'}${pairs.length ? ` (${pairs.join(', ')})` : ''}`
}

const saveLink = async () => {
  if (!selectedSupplier.value?.id) return
  if (!linkForm.value.product_id) {
    toast.error('Select a product first.')
    return
  }

  const payload = {
    product_id: linkForm.value.product_id,
    supplier_product_code: linkForm.value.supplier_product_code || null,
    cost: linkForm.value.cost,
    lead_days: linkForm.value.lead_days,
    moq: linkForm.value.moq,
    notes: linkForm.value.notes || null,
    is_active: 1,
  }

  if (selectedProduct.value?.has_varients) {
    const selectedVarients = variantRows.value
      .filter((row) => row.enabled)
      .map((row) => ({
        varient_id: row.varient_id,
        supplier_sku: row.supplier_sku || null,
        cost: row.cost,
        lead_days: row.lead_days,
        moq: row.moq,
        notes: row.notes || null,
        is_active: row.is_active ? 1 : 0,
      }))

    if (!selectedVarients.length) {
      toast.error('Select at least one supplier variant for this product.')
      return
    }

    payload.varients = selectedVarients
  }

  try {
    linkSaving.value = true
    await axios.post(`/api/suppliers/${selectedSupplier.value.id}/link-product`, payload)
    toast.success('Product linked successfully.')
    await fetchSupplierDetail(selectedSupplier.value.id)
    await fetchSuppliers(pagination.value.current_page || 1)
    applyExistingMapping(linkForm.value.product_id)
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors || {}
      const message = error.response.data.message
      if (message) toast.error(message)
      Object.values(errors).flat().forEach((m) => toast.error(m))
    } else {
      toast.error(error.response?.data?.message || 'Failed to link product.')
    }
  } finally {
    linkSaving.value = false
  }
}

const editLinkedProduct = (row) => {
  if (!productOptions.value.some((item) => item.id === row.product_id)) {
    productOptions.value.unshift({
      id: row.product_id,
      title: row.product?.title || 'Product',
      product_code: row.product?.product_code || '-',
      has_varients: !!row.product?.has_varients,
      varients: (row.supplier_varients || []).map((item) => item.varient).filter(Boolean),
    })
  }
  linkForm.value.product_id = row.product_id
  onProductSelected()
}

const unlinkProduct = async (row) => {
  if (!selectedSupplier.value?.id) return
  if (!confirm(`Unlink product \"${row.product?.title || ''}\" from this supplier?`)) return

  try {
    await axios.delete(`/api/suppliers/${selectedSupplier.value.id}/linked-products/${row.id}`)
    toast.success('Product unlinked.')
    await fetchSupplierDetail(selectedSupplier.value.id)
    await fetchSuppliers(pagination.value.current_page || 1)
    if (linkForm.value.product_id === row.product_id) {
      resetLinkForm()
    }
  } catch {
    toast.error('Failed to unlink product.')
  }
}

onMounted(() => {
  fetchSuppliers(1)
})
</script>
