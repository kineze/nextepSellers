<template>
  <div
    ref="bulkRoot"
    :class="[
      'space-y-6',
      isFullscreen ? 'fixed inset-0 z-[1300] overflow-y-auto bg-slate-50 p-4 dark:bg-slate-950 md:p-6' : '',
    ]"
  >
    <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-7 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-blue-600 dark:text-blue-300">Bulk Orders</p>
          <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white">Bulk Order Creation</h1>
          <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
            Build one product list, generate customer rows, then submit similar multi-product orders at once.
          </p>
        </div>

        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="toggleFullscreen"
        >
          <i :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
          {{ isFullscreen ? 'Exit Full Screen' : 'Full Screen' }}
        </button>
      </div>
    </div>

    <section class="rounded-3xl border border-slate-200/70 bg-white/90 p-6 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.24em] text-emerald-600 dark:text-emerald-300">Excel Upload</p>
          <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Upload orders with products from Excel</h2>
          <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
            Headers: order_ref, name, phone, additional_phone, address, city, product_code or product_name, qty, price, notes.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <input ref="uploadFileInput" type="file" accept=".csv,.txt,.xlsx,.xls" class="hidden" @change="handleUploadFileChange" />
          <button
            type="button"
            class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            :disabled="previewingUpload"
            @click="uploadFileInput?.click()"
          >
            {{ previewingUpload ? 'Reading...' : 'Choose Excel / CSV' }}
          </button>
          <button
            type="button"
            class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="submittingUpload || !uploadCreatableOrders.length"
            @click="submitUploadedOrders"
          >
            {{ submittingUpload ? 'Submitting...' : `Submit ${uploadCreatableOrders.length} Valid Orders` }}
          </button>
        </div>
      </div>

      <div class="mt-4 grid gap-3 sm:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Rows</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ uploadRows.length }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Valid Orders</p>
          <p class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-300">{{ uploadCreatableOrders.length }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Errors</p>
          <p class="mt-1 text-xl font-bold text-rose-600 dark:text-rose-300">{{ uploadErrorRows }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Commission</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">LKR {{ toMoney(uploadCommission) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Points</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ uploadPoints }}</p>
        </div>
      </div>

      <div v-if="uploadRows.length" class="mt-5 overflow-x-auto">
        <table class="min-w-[1180px] w-full text-left text-xs">
          <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
            <tr>
              <th class="px-2 py-2">Row</th>
              <th class="px-2 py-2">Ref</th>
              <th class="px-2 py-2">Status</th>
              <th class="px-2 py-2">Customer</th>
              <th class="px-2 py-2">Phone</th>
              <th class="px-2 py-2">Address</th>
              <th class="px-2 py-2">City</th>
              <th class="px-2 py-2">Product / SKU</th>
              <th class="px-2 py-2">Qty</th>
              <th class="px-2 py-2">Value</th>
              <th class="px-2 py-2">Commission</th>
              <th class="px-2 py-2">Points</th>
              <th class="px-2 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, index) in uploadRows"
              :key="row._id"
              class="group/upload-row border-t border-slate-200 align-top dark:border-slate-700"
              :class="Object.keys(row.errors || {}).length ? 'bg-rose-50/70 dark:bg-rose-950/20' : ''"
            >
              <td class="px-2 py-2 font-semibold text-slate-500">{{ row.row_number }}</td>
              <td class="px-2 py-2"><input v-model.trim="row.order_ref" :class="cellClass" @input="row.group_key = row.order_ref || `row-${row.row_number}`" /></td>
              <td class="relative px-2 py-2">
                <span class="rounded-full px-2 py-0.5 text-[11px] font-bold" :class="Object.keys(row.errors || {}).length ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200'">
                  {{ Object.keys(row.errors || {}).length ? 'Error' : 'Ready' }}
                </span>
                <div
                  v-if="Object.keys(row.errors || {}).length"
                  class="pointer-events-none absolute left-2 top-8 z-50 hidden w-72 rounded-xl border border-rose-200 bg-white p-3 text-[11px] text-rose-700 shadow-xl group-hover/upload-row:block dark:border-rose-500/30 dark:bg-slate-950 dark:text-rose-200"
                >
                  <p class="mb-1 font-bold text-slate-900 dark:text-white">Validation errors</p>
                  <ul class="space-y-1">
                    <li v-for="message in Object.values(row.errors || {})" :key="message" class="flex gap-1.5">
                      <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-current"></span>
                      <span>{{ message }}</span>
                    </li>
                  </ul>
                </div>
              </td>
              <td class="px-2 py-2"><input v-model.trim="row.name" :class="cellClass" @input="validateUploadRow(row)" /></td>
              <td class="px-2 py-2"><input v-model.trim="row.phone" :class="cellClass" @input="validateUploadRow(row)" /></td>
              <td class="px-2 py-2"><input v-model.trim="row.address" :class="cellClass" @input="validateUploadRow(row)" /></td>
              <td class="px-2 py-2">
                <div class="relative min-w-44">
                  <input v-model.trim="row.city" :class="cellClass" placeholder="Search city" @focus="openCityDropdown(row)" @input="onCityInput(row)" @blur="closeCityDropdownWithDelay(row)" />
                  <div v-if="row._cityOpen" class="absolute z-30 mt-1 max-h-52 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                    <button v-for="cityOption in row._cityOptions" :key="`upload-city-${row._id}-${cityOption.id}`" type="button" class="block w-full border-b border-slate-100 px-3 py-2 text-left text-xs last:border-b-0 hover:bg-slate-100 dark:border-slate-800 dark:hover:bg-slate-800" @mousedown.prevent="selectCityForRow(row, cityOption)">
                      {{ cityOption.name_en }}
                    </button>
                    <div v-if="!row._cityLoading && !row._cityOptions.length" class="px-3 py-2 text-[11px] text-slate-500">No cities found</div>
                  </div>
                </div>
              </td>
              <td class="px-2 py-2">
                <div class="relative min-w-64">
                  <input v-model.trim="row.product_query" :class="cellClass" placeholder="Name, product code, or SKU" @focus="row._productOpen = true" @input="searchProductsForUploadRow(row)" @blur="closeProductForUploadWithDelay(row)" />
                  <div v-if="row._productOpen" class="absolute z-40 mt-1 max-h-72 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                    <template v-if="row._variantOptions?.length">
                      <button v-for="variant in row._variantOptions" :key="`upload-variant-${row._id}-${variant.id}`" type="button" class="block w-full border-b border-slate-100 px-3 py-2 text-left last:border-b-0 hover:bg-slate-100 dark:border-slate-800 dark:hover:bg-slate-800" @mousedown.prevent="selectVariantForUploadRow(row, row._pendingProduct, variant)">
                        <span class="block font-semibold text-slate-900 dark:text-white">{{ row._pendingProduct?.title }}</span>
                        <span class="block text-[11px] text-slate-500">{{ variant.sku }} | {{ formatVariantLabel(variant) }} | LKR {{ toMoney(variant.price) }}</span>
                      </button>
                    </template>
                    <template v-else>
                      <button v-for="product in row._productOptions" :key="`upload-product-${row._id}-${product.id}`" type="button" class="block w-full border-b border-slate-100 px-3 py-2 text-left last:border-b-0 hover:bg-slate-100 dark:border-slate-800 dark:hover:bg-slate-800" @mousedown.prevent="selectProductForUploadRow(row, product)">
                        <span class="block font-semibold text-slate-900 dark:text-white">{{ product.title }}</span>
                        <span class="block text-[11px] text-slate-500">{{ product.product_code || 'No code' }} | {{ product.variants?.length || 0 }} variants</span>
                      </button>
                      <div v-if="row._productLoading" class="px-3 py-2 text-[11px] text-slate-500">Searching...</div>
                      <div v-if="!row._productLoading && !row._productOptions?.length" class="px-3 py-2 text-[11px] text-slate-500">No products found.</div>
                    </template>
                  </div>
                  <p v-if="row.product_title" class="mt-1 text-[11px] text-slate-500">{{ row.product_title }} | {{ row.variant_label }}</p>
                </div>
              </td>
              <td class="px-2 py-2"><input v-model.number="row.qty" min="1" type="number" :class="[cellClass, 'w-20']" @input="recalculateUploadRow(row)" /></td>
              <td class="px-2 py-2 font-semibold">
                <input v-if="row.pricing_model === 'reseller'" v-model.number="row.price" type="number" step="0.01" :min="row.reseller_price" :max="hasMaximumPrice(row.maximum_selling_price) ? row.maximum_selling_price : undefined" :class="[cellClass, 'w-28']" @input="recalculateUploadRow(row)" />
                <span v-else>LKR {{ toMoney(uploadRowTotal(row)) }}</span>
                <p v-if="row.pricing_model === 'reseller'" class="mt-1 text-[9px] text-slate-500">Total: LKR {{ toMoney(uploadRowTotal(row)) }}</p>
              </td>
              <td class="px-2 py-2 font-semibold text-emerald-700 dark:text-emerald-300">LKR {{ toMoney(row.commission_amount) }}</td>
              <td class="px-2 py-2 font-semibold">{{ row.points_earned || 0 }}</td>
              <td class="px-2 py-2 text-right">
                <button type="button" class="rounded-lg p-2 text-rose-600 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10" @click="uploadRows.splice(index, 1)">
                  <i class="fas fa-trash text-xs" aria-hidden="true"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-400">
        Upload an Excel or CSV file to preview valid orders, commission, and points before submitting.
      </div>
    </section>

    <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-6 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
      <div class="grid gap-4 lg:grid-cols-12">
        <div class="lg:col-span-5">
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Search Product</label>
          <div class="relative">
            <input
              v-model.trim="productQuery"
              type="search"
              placeholder="Search by product / code"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @focus="openProductDropdown"
              @input="onProductInput"
              @keydown.down.prevent="moveActiveProduct(1)"
              @keydown.up.prevent="moveActiveProduct(-1)"
              @keydown.enter.prevent="pickActiveProduct"
              @blur="closeProductDropdownWithDelay"
            />

            <div v-if="productDdOpen" class="absolute z-20 mt-2 max-h-64 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
              <button
                v-for="(product, idx) in suggestedProducts"
                :key="product.id"
                type="button"
                class="flex w-full items-start justify-between gap-3 border-b border-slate-100 px-3 py-2 text-left last:border-b-0 dark:border-slate-800"
                :class="idx === activeProductIndex ? 'bg-slate-100 dark:bg-slate-800' : ''"
                @mousemove="activeProductIndex = idx"
                @mousedown.prevent="chooseProduct(product)"
              >
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ product.title }}</p>
                  <p class="truncate text-xs text-slate-500 dark:text-slate-300">{{ product.product_code || 'No code' }}</p>
                </div>
                <span class="shrink-0 text-[11px] text-slate-500 dark:text-slate-300">{{ product.variants?.length || 0 }} variants</span>
              </button>

              <div v-if="!suggestedProducts.length" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                <span v-if="loadingProducts">Loading products...</span>
                <span v-else>No matching products.</span>
              </div>
            </div>
          </div>

          <p v-if="draftProduct" class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
            Selected: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ draftProduct.title }}</span>
          </p>
        </div>

        <div class="lg:col-span-5">
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Variant Selection</label>

          <div v-if="draftProduct" class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/60">
            <div v-if="variantAttributeKeys.length" class="grid gap-3 sm:grid-cols-2">
              <div v-for="attrKey in variantAttributeKeys" :key="attrKey">
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ attrKey }}</p>

                <div v-if="isColorAttribute(attrKey)" class="flex flex-wrap gap-2">
                  <button
                    v-for="option in variantAttributeOptions[attrKey]"
                    :key="`${attrKey}-${option.value}`"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-2 py-1 text-xs text-slate-700 hover:border-blue-500 dark:border-slate-700 dark:text-slate-200"
                    :class="selectedAttributes[attrKey] === option.value ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200' : ''"
                    @click="selectedAttributes[attrKey] = option.value"
                  >
                    <span class="inline-block h-4 w-4 rounded-full border border-white/70" :style="{ backgroundColor: option.color }"></span>
                    <span>{{ option.label }}</span>
                  </button>
                </div>

                <select
                  v-else
                  v-model="selectedAttributes[attrKey]"
                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                >
                  <option value="">Select {{ attrKey }}</option>
                  <option v-for="option in variantAttributeOptions[attrKey]" :key="`${attrKey}-${option.value}`" :value="option.value">{{ option.label }}</option>
                </select>
              </div>
            </div>

            <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">
              <span v-if="selectedVariant">
                Selected: <span class="font-semibold">{{ formatVariantLabel(selectedVariant) }}</span> | LKR {{ toMoney(selectedVariant.price) }}
              </span>
              <span v-else>Select all variant keys.</span>
            </p>
          </div>

          <p v-else class="rounded-xl border border-dashed border-slate-300 px-3 py-2 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
            Select a product first to choose variant keys.
          </p>
        </div>

        <div class="lg:col-span-2">
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Product List</label>
          <button
            type="button"
            class="w-full rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="!canAddDraftItem"
            @click="addSelectedProductToDraft"
          >
            Add Product
          </button>
        </div>
      </div>

      <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/50">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Order Product List</p>
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">These products will be copied to every generated or imported customer row.</p>
          </div>
          <div class="text-sm font-bold text-slate-900 dark:text-white">
            LKR {{ toMoney(draftTotal) }}
            <span class="ml-2 text-xs font-semibold text-emerald-600 dark:text-emerald-300">Seller earnings: LKR {{ toMoney(draftCommission) }}</span>
          </div>
        </div>

        <div v-if="!draftItems.length" class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
          Search a product, choose a variant, then add it here before generating rows.
        </div>

        <div v-else class="mt-4 grid gap-3 lg:grid-cols-2">
          <div
            v-for="(item, itemIndex) in draftItems"
            :key="item.id"
            class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ item.product_title }}</p>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ item.variant_label }}</p>
                <p class="mt-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-300">{{ item.pricing_model === 'reseller' ? 'Margin' : 'Commission' }}: LKR {{ toMoney(rowItemCommission(item)) }}</p>
              </div>
              <button
                type="button"
                class="rounded-lg p-2 text-rose-600 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10"
                title="Remove product"
                @click="removeDraftItem(itemIndex)"
              >
                <i class="fas fa-trash text-xs" aria-hidden="true"></i>
              </button>
            </div>
            <div v-if="item.pricing_model === 'reseller'" class="mt-3">
              <label class="text-[10px] font-bold uppercase text-emerald-700">Customer selling price</label>
              <input v-model.number="item.price" type="number" step="0.01" :min="item.reseller_price" :max="hasMaximumPrice(item.maximum_selling_price) ? item.maximum_selling_price : undefined" class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-2 py-1.5 text-xs dark:border-emerald-700 dark:bg-slate-950" />
              <p class="mt-1 text-[10px] text-slate-500">Allowed LKR {{ toMoney(item.reseller_price) }}{{ hasMaximumPrice(item.maximum_selling_price) ? ` – ${toMoney(item.maximum_selling_price)}` : ' or higher' }}</p>
            </div>
            <div class="mt-3 flex items-center justify-between gap-3">
              <input
                v-model.number="item.quantity"
                type="number"
                min="1"
                class="w-24 rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-center text-xs text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                @change="clampRowItemQty(item)"
              />
              <span class="text-sm font-bold text-slate-900 dark:text-white">LKR {{ toMoney(rowItemTotal(item)) }}</span>
            </div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end">
          <div>
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Rows</label>
          <div class="flex gap-2">
            <input
              v-model.number="draft.rowsToGenerate"
              type="number"
              min="1"
              max="100"
              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            />
            <button
              type="button"
              class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700 disabled:opacity-50"
              :disabled="!canCreateBatch"
              @click="createBatch"
            >
              Generate
            </button>
          </div>
          </div>
          <div class="mt-2">
            <input
              ref="csvFileInput"
              type="file"
              accept=".csv,text/csv"
              class="hidden"
              @change="onCsvSelected"
            />
            <button
              type="button"
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              :disabled="!draftItems.length || importingCsv"
              @click="openCsvPicker"
            >
              <span v-if="importingCsv">Importing...</span>
              <span v-else>Import CSV</span>
            </button>
            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">CSV headers: name, phone, address, city, notes</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!batches.length" class="rounded-2xl border border-dashed border-slate-300 bg-white/80 p-10 text-center text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-400">
      Build a product list, then generate or import customer rows.
    </div>

    <div v-else class="space-y-5">
      <article
        v-for="(batch, batchIndex) in batches"
        :key="batch.id"
        class="rounded-3xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80"
      >
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ batch.product_title }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ batch.items_template.length }} product(s) | Total: LKR {{ toMoney(batchTemplateTotal(batch)) }} | Commission: LKR {{ toMoney(batchTemplateCommission(batch)) }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="appendRows(batch, 1)"
            >
              + Row
            </button>
            <button
              type="button"
              class="rounded-xl border border-rose-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-rose-700 hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-300 dark:hover:bg-rose-500/10"
              @click="removeBatch(batchIndex)"
            >
              Remove Batch
            </button>
          </div>
        </div>

        <div class="mt-4 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
          <div
            v-for="item in batch.items_template"
            :key="item.id"
            class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/60"
          >
            <p class="truncate text-xs font-bold text-slate-900 dark:text-white">{{ item.product_title }}</p>
            <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ item.variant_label }}</p>
            <p class="mt-1 text-[11px] font-semibold text-slate-700 dark:text-slate-200">
              {{ item.quantity }} x LKR {{ toMoney(item.price) }}
              <span class="text-emerald-600 dark:text-emerald-300"> | LKR {{ toMoney(rowItemCommission(item)) }} commission</span>
            </p>
          </div>
        </div>

        <div class="mt-4 overflow-x-auto">
          <table class="min-w-[900px] w-full text-left">
            <thead>
              <tr class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                <th class="px-2 py-2">#</th>
                <th class="px-2 py-2">Customer Name</th>
                <th class="px-2 py-2">Phone</th>
                <th class="px-2 py-2">Additional Phone</th>
                <th class="px-2 py-2">Address</th>
                <th class="px-2 py-2">City</th>
                <th class="px-2 py-2">Notes</th>
                <th class="px-2 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, rowIndex) in batch.rows"
                :key="row.id"
                class="border-t border-slate-200 dark:border-slate-700"
              >
                <td class="px-2 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ rowIndex + 1 }}</td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.customer_name" type="text" :class="cellClass" placeholder="Customer name" />
                </td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.phone" type="text" :class="cellClass" placeholder="+9477xxxxxxx" />
                </td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.additional_phone" type="text" :class="cellClass" placeholder="Optional" />
                </td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.address" type="text" :class="cellClass" placeholder="Address" />
                </td>
                <td class="px-2 py-2">
                  <div class="relative">
                    <input
                      v-model.trim="row.city"
                      type="text"
                      :class="[
                        cellClass,
                        row._cityInvalid ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 dark:border-rose-500' : '',
                      ]"
                      placeholder="Search city"
                      @focus="openCityDropdown(row)"
                      @input="onCityInput(row)"
                      @blur="closeCityDropdownWithDelay(row)"
                    />
                    <div
                      v-if="row._cityOpen"
                      class="absolute z-20 mt-1 max-h-52 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                    >
                      <div v-if="row._cityLoading" class="px-3 py-2 text-[11px] text-slate-500 dark:text-slate-400">
                        Loading cities...
                      </div>
                      <button
                        v-for="cityOption in row._cityOptions"
                        :key="`city-${row.id}-${cityOption.id}`"
                        type="button"
                        class="block w-full border-b border-slate-100 px-3 py-2 text-left text-xs text-slate-700 last:border-b-0 hover:bg-slate-100 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-800"
                        @mousedown.prevent="selectCityForRow(row, cityOption)"
                      >
                        {{ cityOption.name_en }}
                      </button>
                      <div v-if="!row._cityLoading && !row._cityOptions.length" class="px-3 py-2 text-[11px] text-slate-500 dark:text-slate-400">
                        No cities found
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-2 py-2">
                  <input v-model.trim="row.notes" type="text" :class="cellClass" placeholder="Notes (optional)" />
                </td>
                <td class="px-2 py-2">
                  <div class="flex gap-1">
                    <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="duplicateRow(batch, row)">Copy</button>
                    <button type="button" class="rounded-lg border border-rose-300 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-rose-700 hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-300 dark:hover:bg-rose-500/10" @click="removeRow(batch, rowIndex)">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </div>

    <aside class="rounded-2xl border border-slate-200/70 bg-white/90 p-5 shadow-sm dark:border-slate-800/70 dark:bg-slate-900/80">
      <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Order Groups</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ batches.length }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Total Orders</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ validRows }} / {{ totalRows }}</p>
          <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">Ready rows counted</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
          <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Estimated Value</p>
          <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">LKR {{ toMoney(estimatedValue) }}</p>
          <p class="mt-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-300">Commission: LKR {{ toMoney(estimatedCommission) }}</p>
        </div>
      </div>

      <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
        <button
          type="button"
          class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
          @click="resetAll"
        >
          Clear All
        </button>
        <button
          type="button"
          class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="submitting || !batches.length"
          @click="submitBulk"
        >
          <span v-if="submitting">Submitting...</span>
          <span v-else>Submit All Orders</span>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const bulkRoot = ref(null)
const isFullscreen = ref(false)
const loadingProducts = ref(false)
const importingCsv = ref(false)
const submitting = ref(false)
const previewingUpload = ref(false)
const submittingUpload = ref(false)
const productQuery = ref('')
const productDdOpen = ref(false)
const activeProductIndex = ref(-1)
let productSearchTimer = null
const csvFileInput = ref(null)
const uploadFileInput = ref(null)
const products = ref([])
const batches = ref([])
const draftItems = ref([])
const uploadRows = ref([])
const uploadFileName = ref('')

const draft = reactive({
  productId: null,
  rowsToGenerate: 5,
})

const cellClass = 'w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100'

const selectedAttributes = reactive({})
const draftProduct = computed(() => products.value.find((p) => p.id === Number(draft.productId)) || null)
const normalizedDraftVariants = computed(() => {
  const variants = draftProduct.value?.variants || []
  return variants.map((variant) => {
    const rawAttrs = variant?.attributes || {}
    const attrs = {}

    for (const [key, rawMeta] of Object.entries(rawAttrs)) {
      if (rawMeta && typeof rawMeta === 'object') {
        const label = String(rawMeta.label || rawMeta.name || rawMeta.value || rawMeta.color || '').trim()
        const value = String(rawMeta.value || rawMeta.name || rawMeta.color || label).trim().toLowerCase()
        attrs[key] = { value, label: label || value, color: rawMeta.color || null }
      } else {
        const label = String(rawMeta || '').trim()
        attrs[key] = { value: label.toLowerCase(), label, color: null }
      }
    }

    return {
      ...variant,
      attributes: attrs,
    }
  })
})
const variantAttributeOptions = computed(() => {
  const map = {}

  for (const variant of normalizedDraftVariants.value) {
    const attrs = variant?.attributes || {}
    for (const [key, meta] of Object.entries(attrs)) {
      if (!map[key]) map[key] = []
      const exists = map[key].some((row) => row.value === meta.value)
      if (!exists) {
        map[key].push({ value: meta.value, label: meta.label, color: meta.color || null })
      }
    }
  }

  return map
})
const variantAttributeKeys = computed(() => Object.keys(variantAttributeOptions.value))
const selectedVariant = computed(() => {
  const keys = variantAttributeKeys.value
  if (!keys.length) return normalizedDraftVariants.value[0] || null

  for (const key of keys) {
    if (!selectedAttributes[key]) return null
  }

  return normalizedDraftVariants.value.find((variant) => {
    const attrs = variant?.attributes || {}
    return keys.every((key) => (attrs[key]?.value || '') === (selectedAttributes[key] || ''))
  }) || null
})
const canAddDraftItem = computed(() => !!draftProduct.value && !!selectedVariant.value)
const canCreateBatch = computed(() => draftItems.value.length > 0 && Number(draft.rowsToGenerate || 0) > 0)
const suggestedProducts = computed(() => {
  const q = productQuery.value.trim().toLowerCase()
  if (!q) return products.value.slice(0, 60)

  return products.value
    .filter((product) => {
      const title = String(product?.title || '').toLowerCase()
      const code = String(product?.product_code || '').toLowerCase()
      return title.includes(q) || code.includes(q)
    })
    .slice(0, 80)
})

const totalRows = computed(() => batches.value.reduce((sum, batch) => sum + batch.rows.length, 0))
const validRows = computed(() => batches.value.reduce((sum, batch) => {
  return sum + batch.rows.filter((row) => isRowReady(row)).length
}, 0))
const estimatedValue = computed(() => batches.value.reduce((sum, batch) => {
  return sum + batch.rows.reduce((batchSum, row) => {
    return isRowReady(row) ? batchSum + rowTotal(row) : batchSum
  }, 0)
}, 0))
const estimatedCommission = computed(() => batches.value.reduce((sum, batch) => {
  return sum + batch.rows.reduce((batchSum, row) => {
    return isRowReady(row) ? batchSum + rowCommission(row) : batchSum
  }, 0)
}, 0))
const draftTotal = computed(() => draftItems.value.reduce((sum, item) => sum + rowItemTotal(item), 0))
const draftCommission = computed(() => draftItems.value.reduce((sum, item) => sum + rowItemCommission(item), 0))
const uploadErrorRows = computed(() => uploadRows.value.filter((row) => Object.keys(row.errors || {}).length > 0).length)
const uploadCreatableOrders = computed(() => {
  const groups = new Map()
  uploadRows.value.forEach((row) => {
    const key = row.group_key || `row-${row.row_number}`
    if (!groups.has(key)) groups.set(key, [])
    groups.get(key).push(row)
  })

  return Array.from(groups.values()).filter((rows) => rows.length && rows.every((row) => Object.keys(row.errors || {}).length === 0))
})
const uploadCommission = computed(() => uploadCreatableOrders.value.flat().reduce((sum, row) => sum + Number(row.commission_amount || 0), 0))
const uploadPoints = computed(() => uploadCreatableOrders.value.flat().reduce((sum, row) => sum + Number(row.points_earned || 0), 0))

const rowId = () => `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`
const hasMaximumPrice = (value) => value !== null && value !== undefined && value !== ''

const createRowItem = (selectedProduct, chosenVariant, quantity = 1) => ({
  id: rowId(),
  product_id: Number(selectedProduct.id),
  product_title: selectedProduct.title,
  product_code: selectedProduct.product_code || '',
  variant_id: Number(chosenVariant.id),
  variant_label: formatVariantLabel(chosenVariant),
  price: Number(selectedProduct.pricing_model === 'reseller' ? chosenVariant.reseller_price : chosenVariant.price || 0),
  pricing_model: selectedProduct.pricing_model === 'reseller' ? 'reseller' : 'commission',
  reseller_price: Number(chosenVariant.reseller_price || 0),
  maximum_selling_price: hasMaximumPrice(chosenVariant.maximum_selling_price)
    ? Number(chosenVariant.maximum_selling_price)
    : null,
  quantity: Math.max(1, Number(quantity || 1)),
  commission_rule: selectedProduct.commission_rule || null,
})

const createEmptyRow = (items = []) => ({
  id: rowId(),
  customer_name: '',
  phone: '',
  additional_phone: '',
  email: '',
  address: '',
  city_id: null,
  city: '',
  items,
  notes: '',
  _cityOpen: false,
  _cityLoading: false,
  _cityOptions: [],
  _cityTimer: null,
  _cityInvalid: false,
})

const toMoney = (value) => Number(value || 0).toFixed(2)

const commissionFor = (price, quantity, rule) => {
  if (!rule) return 0

  const qty = Math.max(0, Number(quantity || 0))
  const unitPrice = Math.max(0, Number(price || 0))
  const value = Math.max(0, Number(rule.value || 0))

  if (rule.type === 'percentage') {
    return (unitPrice * (value / 100)) * qty
  }

  return value * qty
}

const rowItemTotal = (item) => Math.max(0, Number(item.price || 0) * Number(item.quantity || 0))
const rowItemCommission = (item) => item.pricing_model === 'reseller'
  ? Math.max(0, Number(item.price || 0) - Number(item.reseller_price || 0)) * Number(item.quantity || 0)
  : commissionFor(item.price, item.quantity, item.commission_rule)
const rowTotal = (row) => (row.items || []).reduce((sum, item) => sum + rowItemTotal(item), 0)
const rowCommission = (row) => (row.items || []).reduce((sum, item) => sum + rowItemCommission(item), 0)
const uploadRowTotal = (row) => Math.max(0, Number(row.price || 0) * Number(row.qty || 0))

const normalizeUploadPreviewRow = (row) => ({
  ...row,
  _id: rowId(),
  _upload: true,
  group_key: row.group_key || row.order_ref || `row-${row.row_number}`,
  product_query: row.product_code || row.product_name || row.product_title || '',
  _productOpen: false,
  _productLoading: false,
  _productOptions: [],
  _variantOptions: [],
  _pendingProduct: null,
  _productTimer: null,
  _cityOpen: false,
  _cityLoading: false,
  _cityOptions: [],
  _cityTimer: null,
  _cityInvalid: false,
  errors: row.errors || {},
})

const isRowReady = (row) => {
  const customerName = String(row.customer_name || '').trim()
  const phone = normalizePhone(row.phone)
  const address = String(row.address || '').trim()
  const cityId = Number(row.city_id || 0)
  const items = Array.isArray(row.items) ? row.items : []

  return customerName.length >= 2
    && /^\+?[0-9]{8,15}$/.test(phone)
    && address.length >= 6
    && Number.isInteger(cityId)
    && cityId > 0
    && items.length > 0
    && items.every((item) => Number(item.quantity || 0) >= 1)
}

const isColorAttribute = (key) => {
  const options = variantAttributeOptions.value[key] || []
  return options.length > 0 && options.every((opt) => !!opt.color)
}

const formatVariantLabel = (variant) => {
  const attrs = variant?.attributes && typeof variant.attributes === 'object'
    ? Object.entries(variant.attributes).map(([key, meta]) => `${key}: ${meta?.label || meta?.value || ''}`).filter(Boolean)
    : []
  const attrText = attrs.join(', ')
  const sku = variant?.sku ? `SKU ${variant.sku}` : 'Variant'
  return attrText ? `${sku} | ${attrText}` : sku
}

const fetchProducts = async (search = '') => {
  loadingProducts.value = true
  try {
    const { data } = await axios.get('/api/seller/order-products', {
      params: {
        search: search || undefined,
        limit: 100,
      },
    })
    products.value = Array.isArray(data?.products) ? data.products : []
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to load products.')
  } finally {
    loadingProducts.value = false
  }
}

const openProductDropdown = () => {
  productDdOpen.value = true
}

const closeProductDropdownWithDelay = () => {
  window.setTimeout(() => {
    productDdOpen.value = false
  }, 120)
}

const chooseProduct = (product) => {
  draft.productId = product?.id || null
  productQuery.value = product?.title || ''
  productDdOpen.value = false
  activeProductIndex.value = -1
}

const moveActiveProduct = (delta) => {
  if (!productDdOpen.value) {
    productDdOpen.value = true
  }

  const list = suggestedProducts.value
  if (!list.length) return

  const current = Number(activeProductIndex.value || 0)
  const next = (current + delta + list.length) % list.length
  activeProductIndex.value = next
}

const pickActiveProduct = () => {
  const list = suggestedProducts.value
  if (!list.length) return

  const idx = activeProductIndex.value >= 0 ? activeProductIndex.value : 0
  chooseProduct(list[idx])
}

const onProductInput = () => {
  productDdOpen.value = true
  activeProductIndex.value = -1

  if (productSearchTimer) {
    window.clearTimeout(productSearchTimer)
  }
  productSearchTimer = window.setTimeout(() => {
    fetchProducts(productQuery.value.trim())
  }, 220)
}

const addSelectedProductToDraft = () => {
  const selectedProduct = draftProduct.value
  const chosenVariant = selectedVariant.value

  if (!selectedProduct || !chosenVariant) {
    toast.error('Select product and variant first.')
    return
  }

  const existing = draftItems.value.find((item) => Number(item.variant_id) === Number(chosenVariant.id))
  if (existing) {
    existing.quantity = Math.max(1, Number(existing.quantity || 1) + 1)
    return
  }

  draftItems.value.push(createRowItem(selectedProduct, chosenVariant))
}

const removeDraftItem = (itemIndex) => {
  draftItems.value.splice(itemIndex, 1)
}

const createBatch = () => {
  if (!canCreateBatch.value) return

  const count = Math.max(1, Number(draft.rowsToGenerate || 1))
  const items = cloneItems(draftItems.value)

  batches.value.push({
    ...buildBatchHeader(items),
    rows: Array.from({ length: count }, () => createEmptyRow(cloneItems(items))),
  })

  draft.rowsToGenerate = 5
}

const buildBatchHeader = (items) => ({
  id: rowId(),
  product_title: items.length === 1 ? items[0].product_title : `${items.length} products`,
  variant_label: items.length === 1 ? items[0].variant_label : 'Multi-product order template',
  items_template: cloneItems(items),
})

const cloneItems = (items = []) => items.map((item) => ({
  ...item,
  id: rowId(),
  quantity: Math.max(1, Number(item.quantity || 1)),
}))

const appendRows = (batch, count = 1) => {
  const rows = Array.from({ length: Math.max(1, Number(count || 1)) }, () => createEmptyRow(cloneItems(batch.items_template || [])))
  batch.rows.push(...rows)
}

const clampRowItemQty = (item) => {
  item.quantity = Math.max(1, Number.parseInt(item.quantity || 1, 10))
}

const batchTemplateTotal = (batch) => (batch.items_template || []).reduce((sum, item) => sum + rowItemTotal(item), 0)
const batchTemplateCommission = (batch) => (batch.items_template || []).reduce((sum, item) => sum + rowItemCommission(item), 0)

const removeBatch = (batchIndex) => {
  batches.value.splice(batchIndex, 1)
}

const removeRow = (batch, rowIndex) => {
  batch.rows.splice(rowIndex, 1)
  if (!batch.rows.length) {
    batch.rows.push(createEmptyRow(cloneItems(batch.items_template || [])))
  }
}

const duplicateRow = (batch, sourceRow) => {
  batch.rows.push({
    ...sourceRow,
    id: rowId(),
    items: cloneItems(sourceRow.items || []),
    _cityOpen: false,
    _cityLoading: false,
    _cityOptions: [],
    _cityTimer: null,
    _cityInvalid: false,
  })
}

const loadCitiesForRow = async (row, search = '') => {
  row._cityLoading = true
  try {
    const { data } = await axios.get('/api/seller/cities', {
      params: {
        search: search || undefined,
        limit: 20,
      },
    })
    row._cityOptions = Array.isArray(data?.cities) ? data.cities : []
  } catch {
    row._cityOptions = []
  } finally {
    row._cityLoading = false
  }
}

const openCityDropdown = (row) => {
  row._cityOpen = true
  loadCitiesForRow(row, row.city || '')
}

const closeCityDropdownWithDelay = (row) => {
  window.setTimeout(() => {
    row._cityOpen = false
  }, 120)
}

const onCityInput = (row) => {
  row.city_id = null
  row._cityInvalid = false
  row._cityOpen = true
  if (row._upload) validateUploadRow(row)

  if (row._cityTimer) {
    window.clearTimeout(row._cityTimer)
  }
  row._cityTimer = window.setTimeout(() => {
    loadCitiesForRow(row, row.city || '')
  }, 200)
}

const selectCityForRow = (row, cityOption) => {
  row.city_id = Number(cityOption?.id || 0) || null
  row.city = String(cityOption?.name_en || '').trim()
  row._cityInvalid = false
  row._cityOpen = false
  if (row._upload) validateUploadRow(row)
}

const handleUploadFileChange = async (event) => {
  const file = event?.target?.files?.[0]
  if (!file) return

  previewingUpload.value = true
  const form = new FormData()
  form.append('file', file)

  try {
    const { data } = await axios.post('/api/seller/orders/bulk/preview', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    uploadFileName.value = data?.upload_file_name || file.name
    uploadRows.value = Array.isArray(data?.rows) ? data.rows.map(normalizeUploadPreviewRow) : []
    toast.success(`${uploadRows.value.length} rows loaded for validation.`)
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to read upload file.')
  } finally {
    previewingUpload.value = false
    if (event?.target) event.target.value = ''
  }
}

const validateUploadRow = (row) => {
  const errors = { ...(row.errors || {}) }
  const phone = String(row.phone || '').replace(/\D+/g, '')

  row.phone = phone.slice(0, 10)

  if (!String(row.name || '').trim()) errors.name = 'Name is required.'
  else delete errors.name

  if (!row.phone) errors.phone = 'Phone is required.'
  else if (!/^[0-9]{10}$/.test(row.phone)) errors.phone = 'Phone must be 10 digits.'
  else delete errors.phone

  if (!String(row.address || '').trim()) errors.address = 'Address is required.'
  else delete errors.address

  if (!Number(row.city_id || 0)) errors.city = 'Select a valid city.'
  else delete errors.city

  if (!Number(row.product_id || 0) || !Number(row.variant_id || 0)) errors.product = 'Select a valid product variant.'
  else delete errors.product

  if (!Number(row.qty || 0) || Number(row.qty || 0) < 1) errors.qty = 'Quantity must be at least 1.'
  else delete errors.qty

  if (row.pricing_model === 'reseller' && (
    Number(row.price) < Number(row.reseller_price)
    || (hasMaximumPrice(row.maximum_selling_price) && Number(row.price) > Number(row.maximum_selling_price))
  )) errors.price = hasMaximumPrice(row.maximum_selling_price)
    ? `Price must be between LKR ${toMoney(row.reseller_price)} and LKR ${toMoney(row.maximum_selling_price)}.`
    : `Price must be at least LKR ${toMoney(row.reseller_price)}.`
  else delete errors.price

  row.errors = errors
}

const recalculateUploadRow = (row) => {
  row.qty = Math.max(1, Number.parseInt(row.qty || 1, 10))
  row.commission_amount = row.pricing_model === 'reseller'
    ? Math.max(0, Number(row.price) - Number(row.reseller_price)) * Number(row.qty || 1)
    : commissionFor(row.price, row.qty, row.commission_rule)
  row.points_earned = Math.floor(uploadRowTotal(row) / Math.max(1, Number(row.points_rate || 100)))
  validateUploadRow(row)
}

const searchProductsForUploadRow = (row) => {
  row.product_id = null
  row.variant_id = null
  row.product_title = ''
  row.variant_label = ''
  row.commission_amount = 0
  row.points_earned = 0
  row._variantOptions = []
  row._pendingProduct = null
  row._productOpen = true
  validateUploadRow(row)

  if (row._productTimer) window.clearTimeout(row._productTimer)
  row._productTimer = window.setTimeout(async () => {
    const search = String(row.product_query || '').trim()
    if (!search) {
      row._productOptions = []
      row._productLoading = false
      return
    }

    row._productLoading = true
    try {
      const { data } = await axios.get('/api/seller/order-products', {
        params: { search, limit: 20 },
      })
      row._productOptions = Array.isArray(data?.products) ? data.products : []
    } catch {
      row._productOptions = []
    } finally {
      row._productLoading = false
    }
  }, 220)
}

const selectProductForUploadRow = (row, product) => {
  const variants = Array.isArray(product?.variants) ? product.variants : []
  if (variants.length === 1) {
    selectVariantForUploadRow(row, product, variants[0])
    return
  }

  row._pendingProduct = product
  row._variantOptions = variants
  row._productOptions = []
  row._productOpen = true
}

const selectVariantForUploadRow = (row, product, variant) => {
  row.product_id = Number(product?.id || 0) || null
  row.product_title = product?.title || ''
  row.product_code = variant?.sku || product?.product_code || ''
  row.product_query = row.product_code || row.product_title
  row.variant_id = Number(variant?.id || 0) || null
  row.variant_label = formatVariantLabel(variant)
  row.pricing_model = product?.pricing_model === 'reseller' ? 'reseller' : 'commission'
  row.reseller_price = Number(variant?.reseller_price || 0)
  row.maximum_selling_price = hasMaximumPrice(variant?.maximum_selling_price)
    ? Number(variant.maximum_selling_price)
    : null
  row.price = Number(row.pricing_model === 'reseller' ? variant?.reseller_price : variant?.price || row.price || 0)
  row.commission_rule = product?.commission_rule || null
  row.commission_amount = row.pricing_model === 'reseller'
    ? Math.max(0, row.price - row.reseller_price) * Number(row.qty || 1)
    : commissionFor(row.price, row.qty, row.commission_rule)
  row.points_earned = Math.floor(uploadRowTotal(row) / Math.max(1, Number(row.points_rate || 100)))
  row._productOpen = false
  row._productOptions = []
  row._variantOptions = []
  row._pendingProduct = null
  validateUploadRow(row)
}

const closeProductForUploadWithDelay = (row) => {
  window.setTimeout(() => {
    row._productOpen = false
  }, 120)
}

const openCsvPicker = () => {
  if (!draftItems.value.length) {
    toast.error('Add at least one product to the order product list first.')
    return
  }
  csvFileInput.value?.click()
}

const normalizeHeader = (value) => String(value || '').trim().toLowerCase().replace(/[^a-z0-9]+/g, '')

const detectDelimiter = (text) => {
  const sample = String(text || '').split(/\r?\n/).find((line) => line.trim() !== '') || ''
  const candidates = [',', ';', '\t']
  let winner = ','
  let bestScore = -1

  candidates.forEach((delimiter) => {
    const score = sample.split(delimiter).length
    if (score > bestScore) {
      bestScore = score
      winner = delimiter
    }
  })

  return winner
}

const parseDelimited = (text, delimiter) => {
  const rows = []
  let current = []
  let field = ''
  let inQuotes = false

  for (let i = 0; i < text.length; i += 1) {
    const char = text[i]
    const next = text[i + 1]

    if (char === '"') {
      if (inQuotes && next === '"') {
        field += '"'
        i += 1
      } else {
        inQuotes = !inQuotes
      }
      continue
    }

    if (!inQuotes && char === delimiter) {
      current.push(field.trim())
      field = ''
      continue
    }

    if (!inQuotes && (char === '\n' || char === '\r')) {
      if (char === '\r' && next === '\n') i += 1
      current.push(field.trim())
      field = ''

      const hasData = current.some((cell) => String(cell || '').trim() !== '')
      if (hasData) rows.push(current)
      current = []
      continue
    }

    field += char
  }

  if (field !== '' || current.length) {
    current.push(field.trim())
    const hasData = current.some((cell) => String(cell || '').trim() !== '')
    if (hasData) rows.push(current)
  }

  return rows
}

const extractByAliases = (cells, indexByHeader, aliases, fallbackIndex = -1) => {
  const headerIdx = aliases.map((alias) => indexByHeader[alias]).find((idx) => Number.isInteger(idx) && idx >= 0)
  if (Number.isInteger(headerIdx) && headerIdx >= 0) return String(cells[headerIdx] || '').trim()
  if (fallbackIndex >= 0) return String(cells[fallbackIndex] || '').trim()
  return ''
}

const mapCsvRows = (matrix) => {
  if (!Array.isArray(matrix) || !matrix.length) return []

  const normalizedHeaders = matrix[0].map(normalizeHeader)
  const knownHeaderTokens = ['name', 'customername', 'phone', 'address', 'city', 'quantity', 'qty', 'notes']
  const hasHeader = normalizedHeaders.some((h) => knownHeaderTokens.includes(h))
  const dataRows = hasHeader ? matrix.slice(1) : matrix

  const indexByHeader = {}
  if (hasHeader) {
    normalizedHeaders.forEach((header, idx) => {
      if (header) indexByHeader[header] = idx
    })
  }

  return dataRows
    .map((cells) => {
      const customerName = extractByAliases(cells, indexByHeader, ['customername', 'name', 'customer'], 0)
      const phone = extractByAliases(cells, indexByHeader, ['phone', 'mobile', 'primaryphone', 'contactnumber'], 1)
      const additionalPhone = extractByAliases(cells, indexByHeader, ['additionalphone', 'altphone', 'secondaryphone'], 2)
      const email = extractByAliases(cells, indexByHeader, ['email', 'mail'], 3)
      const address = extractByAliases(cells, indexByHeader, ['address', 'customeraddress'], 4)
      const city = extractByAliases(cells, indexByHeader, ['city', 'town'], 5)
      const notes = extractByAliases(cells, indexByHeader, ['notes', 'note', 'remark', 'remarks'])
        || String(cells[7] || cells[6] || '').trim()

      if (!customerName && !phone && !address) return null

      return {
        ...createEmptyRow(),
        customer_name: customerName,
        phone,
        additional_phone: additionalPhone,
        email,
        address,
        city,
        notes,
      }
    })
    .filter(Boolean)
}

const resolveCitiesForImportedRows = async (rows) => {
  const names = rows
    .map((row) => String(row.city || '').trim())
    .filter((name) => name !== '')

  if (!names.length) return

  let matches = {}
  try {
    const { data } = await axios.post('/api/seller/cities/resolve', { names })
    matches = data?.matches && typeof data.matches === 'object' ? data.matches : {}
  } catch {
    matches = {}
  }

  rows.forEach((row) => {
    const key = String(row.city || '').trim().toLowerCase()
    if (!key) return

    const matched = matches[key]
    if (matched?.id) {
      row.city_id = Number(matched.id)
      row.city = String(matched.name_en || '').trim()
      row._cityInvalid = false
    } else {
      row.city_id = null
      row.city = ''
      row._cityInvalid = true
    }
  })
}

const onCsvSelected = async (event) => {
  const file = event?.target?.files?.[0]
  if (!file) return

  if (!draftItems.value.length) {
    toast.error('Add at least one product to the order product list first.')
    event.target.value = ''
    return
  }

  importingCsv.value = true
  try {
    const text = await file.text()
    const delimiter = detectDelimiter(text)
    const matrix = parseDelimited(text, delimiter)
    const importedRows = mapCsvRows(matrix)

    if (!importedRows.length) {
      toast.error('No valid customer rows found in CSV.')
      return
    }

    await resolveCitiesForImportedRows(importedRows)

    const templateItems = cloneItems(draftItems.value)
    importedRows.forEach((row) => {
      row.items = cloneItems(templateItems)
    })

    const batch = {
      ...buildBatchHeader(templateItems),
      rows: [],
    }
    batches.value.push(batch)

    batch.rows.push(...importedRows)
    const invalidCityCount = importedRows.filter((row) => row._cityInvalid).length
    if (invalidCityCount > 0) {
      toast.warning(`${importedRows.length} rows imported. ${invalidCityCount} city value(s) were not matched and need manual selection.`)
    } else {
      toast.success(`${importedRows.length} rows imported.`)
    }
  } catch (error) {
    toast.error('Failed to parse CSV file.')
  } finally {
    importingCsv.value = false
    if (event?.target) event.target.value = ''
  }
}

const normalizePhone = (value) => String(value || '').replace(/\s+/g, '').trim()

const validateBeforeSubmit = () => {
  if (!batches.value.length) {
    toast.error('Add at least one product batch.')
    return false
  }

  for (let bi = 0; bi < batches.value.length; bi += 1) {
    const batch = batches.value[bi]
    if (!batch.rows.length) {
      toast.error(`Batch ${bi + 1} has no rows.`)
      return false
    }

    for (let ri = 0; ri < batch.rows.length; ri += 1) {
      const row = batch.rows[ri]
      const customerName = String(row.customer_name || '').trim()
      const phone = normalizePhone(row.phone)
      const address = String(row.address || '').trim()
      const cityId = Number(row.city_id || 0)
      const items = Array.isArray(row.items) ? row.items : []

      if (customerName.length < 2) {
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: customer name is required.`)
        return false
      }
      if (!/^\+?[0-9]{8,15}$/.test(phone)) {
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: phone number is invalid.`)
        return false
      }
      if (address.length < 6) {
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: address is required.`)
        return false
      }
      if (!items.length) {
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: add at least one product.`)
        return false
      }
      for (let ii = 0; ii < items.length; ii += 1) {
        const qty = Number(items[ii].quantity || 0)
        if (!Number.isFinite(qty) || qty < 1) {
          toast.error(`Batch ${bi + 1}, Row ${ri + 1}, Product ${ii + 1}: quantity must be at least 1.`)
          return false
        }
        if (items[ii].pricing_model === 'reseller' && (
          Number(items[ii].price) < Number(items[ii].reseller_price)
          || (hasMaximumPrice(items[ii].maximum_selling_price) && Number(items[ii].price) > Number(items[ii].maximum_selling_price))
        )) {
          toast.error(`Batch ${bi + 1}, Row ${ri + 1}, Product ${ii + 1}: selling price is outside the allowed range.`)
          return false
        }
      }
      if (!Number.isInteger(cityId) || cityId <= 0) {
        row.city = ''
        row._cityInvalid = true
        toast.error(`Batch ${bi + 1}, Row ${ri + 1}: select a valid city from the list.`)
        return false
      }
    }
  }

  return true
}

const buildPayload = () => {
  const orders = []

  batches.value.forEach((batch) => {
    batch.rows.forEach((row) => {
      orders.push({
        customer: {
          name: String(row.customer_name || '').trim(),
          phone: normalizePhone(row.phone),
          additional_phone: String(row.additional_phone || '').trim() || null,
          email: String(row.email || '').trim() || null,
          address: String(row.address || '').trim(),
          city_id: Number(row.city_id || 0) || null,
          city: String(row.city || '').trim() || null,
          notes: String(row.notes || '').trim() || null,
        },
        commission_amount: Number(rowCommission(row) || 0),
        items: (row.items || []).map((item) => ({
          product_id: Number(item.product_id),
          product_variant_id: Number(item.variant_id),
          quantity: Math.max(1, Number(item.quantity || 1)),
          price: Number(item.price || 0),
        })),
      })
    })
  })

  return { orders }
}

const buildUploadPayload = () => {
  const orders = uploadCreatableOrders.value.map((rows) => {
    const first = rows[0]

    return {
      customer: {
        name: String(first.name || '').trim(),
        phone: normalizePhone(first.phone),
        additional_phone: String(first.additional_phone || '').trim() || null,
        email: String(first.email || '').trim() || null,
        address: String(first.address || '').trim(),
        city_id: Number(first.city_id || 0) || null,
        city: String(first.city || '').trim() || null,
        notes: String(first.notes || '').trim() || null,
      },
      items: rows.map((row) => ({
        product_id: Number(row.product_id),
        product_variant_id: Number(row.variant_id),
        quantity: Math.max(1, Number(row.qty || 1)),
        price: Number(row.price || 0),
      })),
    }
  })

  return { orders }
}

const submitUploadedOrders = async () => {
  uploadRows.value.forEach(validateUploadRow)

  if (!uploadCreatableOrders.value.length) {
    toast.error('No valid uploaded orders are ready to submit.')
    return
  }

  submittingUpload.value = true
  try {
    const { data } = await axios.post('/api/seller/orders/bulk', buildUploadPayload())
    toast.success(data?.message || `${Number(data?.created_count || 0)} uploaded orders submitted.`)
    uploadRows.value = []
    uploadFileName.value = ''
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to submit uploaded orders.')
  } finally {
    submittingUpload.value = false
  }
}

const submitBulk = async () => {
  if (!validateBeforeSubmit()) return

  submitting.value = true
  try {
    const { data } = await axios.post('/api/seller/orders/bulk', buildPayload())
    const createdCount = Number(data?.created_count || 0)
    toast.success(`${createdCount} orders submitted successfully.`)
    resetAll()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to submit bulk orders.')
  } finally {
    submitting.value = false
  }
}

const resetAll = () => {
  batches.value = []
}

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
}

onMounted(() => {
  fetchProducts('')
})

watch(() => draft.productId, () => {
  Object.keys(selectedAttributes).forEach((key) => {
    delete selectedAttributes[key]
  })

  const keys = variantAttributeKeys.value
  keys.forEach((key) => {
    selectedAttributes[key] = variantAttributeOptions.value[key]?.[0]?.value || ''
  })
})
</script>
