<template>
  <div class="space-y-5">
    <section class="overflow-hidden rounded-3xl border border-blue-100/80 bg-gradient-to-br from-blue-50 via-white to-cyan-50 p-6 shadow-sm dark:border-blue-500/20 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950/40">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-blue-600 dark:text-blue-300">Single Order</p>
          <h1 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">Create Order</h1>
          <p class="mt-2 max-w-3xl text-sm text-slate-600 dark:text-slate-300">
            Add customer details, select products with variants, and submit one draft order for processing.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white/80 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-200">
            <i class="fas fa-user-check" aria-hidden="true"></i>
            Customer
          </span>
          <span class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white/80 px-3 py-1.5 text-xs font-semibold text-cyan-700 dark:border-cyan-500/30 dark:bg-cyan-500/10 dark:text-cyan-200">
            <i class="fas fa-boxes-stacked" aria-hidden="true"></i>
            Products
          </span>
          <a
            href="/seller/orders"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white/90 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 shadow-sm hover:bg-white dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-800"
          >
            <i class="fas fa-list" aria-hidden="true"></i>
            My Orders
          </a>
        </div>
      </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-12">
      <section class="rounded-3xl border border-slate-200/70 bg-white/95 p-5 shadow-sm ring-1 ring-slate-900/5 dark:border-slate-800/70 dark:bg-slate-900/85 dark:ring-white/5 xl:col-span-4">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-start gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-sm font-black text-blue-700 dark:bg-blue-500/15 dark:text-blue-200">1</span>
            <div>
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Customer Details</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Search existing customers or enter a new one.</p>
            </div>
          </div>
          <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-800"
            title="Clear customer"
            @click="clearCustomer"
          >
            <i class="fas fa-eraser text-xs" aria-hidden="true"></i>
          </button>
        </div>

        <div class="mt-4 space-y-3">
          <FormField label="Phone Number" required :error="errors.phone">
            <div class="relative">
              <input
                ref="phoneInput"
                v-model.trim="form.phone"
                type="text"
                inputmode="tel"
                autocomplete="tel"
                placeholder="07XXXXXXXX"
                :class="inputClass('phone', 'pl-10 py-3')"
                @input="onPhoneInput"
                @focus="openCustomerDropdown"
                @blur="closeCustomerDropdownWithDelay"
              />
              <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i>

              <div
                v-if="customerDropdownOpen"
                class="absolute z-40 mt-1 max-h-64 w-full overflow-auto rounded-2xl border border-slate-200 bg-white p-1 shadow-xl dark:border-slate-700 dark:bg-slate-900"
              >
                <div v-if="loadingCustomers" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">Searching customers...</div>
                <button
                  v-for="customer in customerOptions"
                  :key="customer.id"
                  type="button"
                  class="block w-full rounded-xl px-3 py-2 text-left hover:bg-blue-50 dark:hover:bg-blue-500/10"
                  @mousedown.prevent="selectCustomer(customer)"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ customer.name || 'Customer' }}</p>
                      <p class="text-xs text-slate-500 dark:text-slate-400">{{ customer.phone || '-' }} <span v-if="customer.additional_phone">| {{ customer.additional_phone }}</span></p>
                      <p v-if="customer.address" class="truncate text-xs text-slate-400">{{ customer.address }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                      {{ customer.orders_count || 0 }} orders
                    </span>
                  </div>
                </button>
                <div v-if="!loadingCustomers && !customerOptions.length && form.phone.length >= 2" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                  No matching customer found.
                </div>
              </div>
            </div>
          </FormField>

          <FormField label="Customer Name" required :error="errors.customer_name">
            <input v-model.trim="form.customer_name" type="text" autocomplete="name" :class="inputClass('customer_name', 'py-3')" placeholder="Customer name" @blur="validateField('customer_name')" />
          </FormField>

          <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Address <span class="text-rose-500">*</span></label>
            <textarea v-model.trim="form.address" rows="4" :class="inputClass('address', 'py-3')" placeholder="House no, street, area landmarks" @blur="validateField('address')"></textarea>
            <p v-if="errors.address" class="mt-1 text-xs text-rose-600">{{ errors.address }}</p>
          </div>

          <FormField label="City" :error="errors.city">
            <div class="relative">
              <input
                v-model.trim="citySearch"
                type="text"
                :class="inputClass('city')"
                placeholder="Search city"
                autocomplete="off"
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

              <div v-if="cityDropdownOpen" class="absolute z-40 mt-1 max-h-56 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                <div v-if="loadingCities" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">Loading cities...</div>
                <button
                  v-for="city in cityOptions"
                  :key="city.id"
                  type="button"
                  class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm text-slate-700 last:border-b-0 hover:bg-slate-100 dark:border-slate-800 dark:text-slate-200 dark:hover:bg-slate-800"
                  @mousedown.prevent="selectCity(city)"
                >
                  {{ city.name_en }}
                </button>
                <div v-if="!loadingCities && !cityOptions.length" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">No cities found.</div>
              </div>
            </div>
          </FormField>

          <FormField label="Email" :error="errors.email">
            <input v-model.trim="form.email" type="email" autocomplete="email" :class="inputClass('email', 'py-3')" placeholder="Optional email" @blur="validateField('email')" />
          </FormField>

          <FormField label="Notes">
            <textarea v-model.trim="form.notes" rows="3" :class="inputClass('notes', 'py-3')" placeholder="Optional delivery notes"></textarea>
          </FormField>
        </div>
      </section>

      <section class="rounded-3xl border border-slate-200/70 bg-white/95 p-5 shadow-sm ring-1 ring-slate-900/5 dark:border-slate-800/70 dark:bg-slate-900/85 dark:ring-white/5 xl:col-span-5">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-start gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-sm font-black text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-200">2</span>
            <div>
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Product Cart</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Search products, pick variants, and add multiple items.</p>
            </div>
          </div>
          <span class="rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-[11px] font-semibold text-cyan-700 dark:border-cyan-500/30 dark:bg-cyan-500/10 dark:text-cyan-200">
            {{ totalQty }} Qty
          </span>
        </div>

        <div class="mt-4">
          <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Search Product</label>
          <div class="relative">
            <input
              v-model.trim="productQuery"
              type="search"
              placeholder="Search by product name or code"
              class="w-full rounded-2xl border border-slate-300 bg-white px-3 py-3 pl-10 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
              @focus="openProductDropdown"
              @input="onProductInput"
              @keydown.down.prevent="moveActiveProduct(1)"
              @keydown.up.prevent="moveActiveProduct(-1)"
              @keydown.enter.prevent="pickActiveProduct"
              @blur="closeProductDropdownWithDelay"
            />
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i>

            <div v-if="productDropdownOpen" class="absolute z-40 mt-2 max-h-80 w-full overflow-auto rounded-2xl border border-slate-200 bg-white p-1 shadow-xl dark:border-slate-700 dark:bg-slate-900">
              <button
                v-for="(product, idx) in suggestedProducts"
                :key="product.id"
                type="button"
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left"
                :class="idx === activeProductIndex ? 'bg-blue-50 dark:bg-blue-500/10' : 'hover:bg-slate-50 dark:hover:bg-slate-800'"
                @mousemove="activeProductIndex = idx"
                @mousedown.prevent="chooseProduct(product)"
              >
                <img v-if="product.image" :src="product.image" :alt="product.title" class="h-12 w-12 rounded-xl object-cover ring-1 ring-slate-200 dark:ring-slate-700" />
                <div v-else class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-[10px] text-slate-500 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700">No img</div>
                <div class="min-w-0 flex-1">
                  <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ product.title }}</p>
                  <p class="truncate text-xs text-slate-500 dark:text-slate-300">{{ product.product_code || 'No code' }}</p>
                  <p class="mt-0.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-300">
                    {{ product.pricing_model === 'reseller' ? 'Pricing Negotiable' : `Commission: ${commissionRuleLabel(product.commission_rule)}` }}
                  </p>
                </div>
                <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                  {{ product.variants?.length || 0 }}
                </span>
              </button>
              <div v-if="!suggestedProducts.length" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                <span v-if="loadingProducts">Loading products...</span>
                <span v-else>No matching products.</span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 max-h-[33rem] space-y-3 overflow-y-auto pr-1">
          <div v-if="!items.length" class="rounded-3xl border border-dashed border-slate-300 bg-slate-50/80 px-4 py-12 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-400">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-300">
              <i class="fas fa-cart-plus" aria-hidden="true"></i>
            </div>
            <p class="font-semibold text-slate-700 dark:text-slate-200">Cart is empty</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Search a product above to add variants.</p>
          </div>

          <article
            v-for="(item, index) in items"
            :key="item.key"
            class="rounded-3xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-950/70"
          >
            <div class="flex gap-3">
              <img v-if="item.image" :src="item.image" :alt="item.product_title" class="h-16 w-16 rounded-2xl object-cover ring-1 ring-slate-200 dark:ring-slate-700" />
              <div v-else class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-[10px] text-slate-500 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700">No img</div>

              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ item.product_title }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ item.product_code || '-' }}</p>
                  </div>
                  <button type="button" class="text-rose-600 hover:text-rose-700" title="Remove item" @click="removeItem(index)">
                    <i class="fas fa-trash text-xs" aria-hidden="true"></i>
                  </button>
                </div>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ item.variant_label }}</p>
                <div
                  class="mt-2 inline-flex flex-wrap items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[10px] font-bold"
                  :class="item.pricing_model === 'reseller'
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30'
                    : 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-500/30'"
                >
                  <i :class="item.pricing_model === 'reseller' ? 'fas fa-tags' : 'fas fa-tag'"></i>
                  <template v-if="item.pricing_model === 'reseller'">
                    Allowed selling price: LKR {{ toMoney(item.reseller_price) }}{{ hasMaximumPrice(item.maximum_selling_price) ? ` – LKR ${toMoney(item.maximum_selling_price)}` : ' or higher' }}
                  </template>
                  <template v-else>
                    Fixed selling price: LKR {{ toMoney(item.price) }}
                  </template>
                </div>
                <p class="mt-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-300">
                  {{ item.pricing_model === 'reseller' ? 'Margin' : 'Commission' }} earning: LKR {{ toMoney(lineCommission(item)) }}
                </p>
              </div>
            </div>

            <div v-if="item.pricing_model === 'reseller'" class="mt-3 rounded-xl bg-emerald-50 p-3 dark:bg-emerald-500/10">
              <label class="text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Customer selling price</label>
              <input v-model.number="item.price" type="number" step="0.01" :min="item.reseller_price" :max="hasMaximumPrice(item.maximum_selling_price) ? item.maximum_selling_price : undefined" class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm font-bold dark:border-emerald-700 dark:bg-slate-950" />
              <p class="mt-1 text-[10px] text-slate-500">Enter a price at or above the reseller price{{ hasMaximumPrice(item.maximum_selling_price) ? ' and within the maximum' : '' }}.</p>
            </div>

            <div class="mt-3 grid grid-cols-[auto_1fr] items-center gap-2">
              <div class="inline-flex items-center rounded-xl border border-slate-300 bg-white dark:border-slate-700 dark:bg-slate-900">
                <button type="button" class="px-3 py-1.5 text-sm font-bold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800" @click="changeQty(item, -1)">-</button>
                <input v-model.number="item.quantity" type="number" min="1" class="w-12 border-x border-slate-300 bg-transparent px-1 py-1.5 text-center text-sm outline-none dark:border-slate-700 dark:text-slate-100" @change="clampItem(item)" />
                <button type="button" class="px-3 py-1.5 text-sm font-bold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800" @click="changeQty(item, 1)">+</button>
              </div>

              <div class="text-right">
                <p class="text-sm font-bold text-slate-900 dark:text-white">LKR {{ toMoney(lineTotal(item)) }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ item.quantity }} x {{ toMoney(item.price) }}</p>
              </div>
            </div>
          </article>
        </div>
      </section>

      <aside class="space-y-4 xl:col-span-3 xl:sticky xl:top-24 xl:self-start">
        <div class="overflow-hidden rounded-3xl border border-slate-200/70 bg-white/95 shadow-sm ring-1 ring-slate-900/5 dark:border-slate-800/70 dark:bg-slate-900/85 dark:ring-white/5">
          <div class="bg-slate-950 px-5 py-4 text-white dark:bg-black">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-blue-200">Step 3</p>
                <h2 class="mt-1 text-base font-bold">Order Summary</h2>
              </div>
              <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-white/10 text-sm">
                <i class="fas fa-receipt" aria-hidden="true"></i>
              </span>
            </div>
          </div>

          <div class="p-5">
            <div class="grid grid-cols-2 gap-2">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/60">
                <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Products</p>
                <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">{{ items.length }}</p>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/60">
                <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Qty</p>
                <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">{{ totalQty }}</p>
              </div>
            </div>

            <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
              <p class="flex items-center justify-between"><span>Subtotal</span><span class="font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(subtotal) }}</span></p>
              <p class="flex items-center justify-between"><span>Delivery</span><span class="font-semibold text-slate-900 dark:text-white">LKR {{ toMoney(deliveryCharge) }}</span></p>
              <p class="flex items-center justify-between"><span>Seller Earnings</span><span class="font-semibold text-emerald-600 dark:text-emerald-300">LKR {{ toMoney(totalCommission) }}</span></p>
            </div>

            <div class="mt-4 rounded-2xl bg-blue-50 p-4 dark:bg-blue-500/10">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">Collectable Total</p>
              <p class="mt-1 text-2xl font-black text-slate-950 dark:text-white">LKR {{ toMoney(grandTotal) }}</p>
            </div>

            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-950/60">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Customer</p>
              <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ form.customer_name || '-' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ form.phone || '-' }}</p>
            </div>

            <button
              type="button"
              class="mt-5 w-full rounded-2xl bg-blue-600 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-white shadow-sm shadow-blue-200 transition hover:-translate-y-0.5 hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 dark:shadow-none"
              :disabled="submitting"
              @click="openSubmitConfirmation"
            >
              <span v-if="submitting">Submitting...</span>
              <span v-else>Create Draft Order</span>
            </button>

            <button
              type="button"
              class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="resetOrder"
            >
              Clear Order
            </button>
          </div>
        </div>
      </aside>
    </div>

    <div v-if="variantModalOpen" class="fixed inset-0 z-[1400] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="closeVariantModal"></div>
      <div class="relative w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-3">
          <div class="flex min-w-0 items-center gap-3">
            <img v-if="modalProduct?.image" :src="modalProduct.image" :alt="modalProduct.title" class="h-14 w-14 rounded-xl object-cover" />
            <div v-else class="flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-[10px] text-slate-500 dark:bg-slate-800 dark:text-slate-400">No img</div>
            <div class="min-w-0">
              <h3 class="truncate text-lg font-bold text-slate-900 dark:text-white">{{ modalProduct?.title || 'Select Variant' }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">{{ modalProduct?.product_code || '-' }}</p>
            </div>
          </div>
          <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="closeVariantModal">
            <i class="fas fa-xmark" aria-hidden="true"></i>
          </button>
        </div>

        <div class="mt-4 max-h-[24rem] overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700">
          <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
              <tr>
                <th class="px-3 py-2 text-center">Add</th>
                <th class="px-3 py-2">Variant</th>
                <th class="px-3 py-2 text-right">Selling Price</th>
                <th class="px-3 py-2 text-right">Earning</th>
                <th class="px-3 py-2 text-center">Qty</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="variant in modalVariants" :key="variant.id" class="border-t border-slate-200 dark:border-slate-700">
                <td class="px-3 py-2 text-center">
                  <input v-model="variant.selected" type="checkbox" class="rounded border-slate-300 accent-blue-600 dark:border-slate-700" />
                </td>
                <td class="px-3 py-2 font-semibold text-slate-800 dark:text-slate-100">{{ formatVariantLabel(variant) }}</td>
                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">
                  <input v-if="modalProduct?.pricing_model === 'reseller'" v-model.number="variant.selling_price" type="number" step="0.01" :min="variant.reseller_price" :max="hasMaximumPrice(variant.maximum_selling_price) ? variant.maximum_selling_price : undefined" class="w-28 rounded-lg border border-emerald-300 bg-white px-2 py-1.5 text-right text-xs dark:border-emerald-700 dark:bg-slate-950" />
                  <span v-else>LKR {{ toMoney(variant.price) }}</span>
                </td>
                <td class="px-3 py-2 text-right font-semibold text-emerald-600 dark:text-emerald-300">LKR {{ toMoney(variantCommission(variant, modalProduct)) }}</td>
                <td class="px-3 py-2">
                  <input v-model.number="variant.quantity" type="number" min="1" class="mx-auto block w-20 rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-center text-xs outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" :disabled="!variant.selected" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-5 flex items-center justify-end gap-2">
          <button type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" @click="closeVariantModal">
            Cancel
          </button>
          <button type="button" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700 disabled:opacity-50" :disabled="selectedModalCount === 0" @click="addModalVariantsToCart">
            Add Selected
          </button>
        </div>
      </div>
    </div>

    <div v-if="showSubmitModal" class="fixed inset-0 z-[1200] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="closeSubmitConfirmation"></div>
      <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Confirm Draft Order</h3>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
          Please review the details before creating this draft order.
        </p>

        <div class="mt-4 space-y-1 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm dark:border-slate-700 dark:bg-slate-800/60">
          <p class="flex items-center justify-between"><span>Products</span><span class="font-semibold">{{ items.length }}</span></p>
          <p class="flex items-center justify-between"><span>Qty</span><span class="font-semibold">{{ totalQty }}</span></p>
          <p class="flex items-center justify-between"><span>Customer</span><span class="font-semibold">{{ form.customer_name || '-' }}</span></p>
          <p class="flex items-center justify-between"><span>Phone</span><span class="font-semibold">{{ form.phone || '-' }}</span></p>
          <p class="flex items-center justify-between border-t border-slate-200 pt-2 dark:border-slate-700"><span>Total</span><span class="font-bold">LKR {{ toMoney(grandTotal) }}</span></p>
        </div>

        <div class="mt-5 flex items-center justify-end gap-2">
          <button
            type="button"
            class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
            :disabled="submitting"
            @click="closeSubmitConfirmation"
          >
            Cancel
          </button>
          <button
            type="button"
            class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-blue-700 disabled:opacity-50"
            :disabled="submitting"
            @click="submitOrder"
          >
            <span v-if="submitting">Submitting...</span>
            <span v-else>Confirm Create</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, reactive, ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const submitting = ref(false)
const showSubmitModal = ref(false)

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

const customerOptions = ref([])
const customerDropdownOpen = ref(false)
const loadingCustomers = ref(false)
let customerSearchTimer = null

const citySearch = ref('')
const cityOptions = ref([])
const cityDropdownOpen = ref(false)
const loadingCities = ref(false)
let citySearchTimer = null

const productQuery = ref('')
const productDropdownOpen = ref(false)
const loadingProducts = ref(false)
const activeProductIndex = ref(-1)
const products = ref([])
const items = ref([])
let productSearchTimer = null
let productSearchRequestId = 0

const variantModalOpen = ref(false)
const modalProduct = ref(null)
const modalVariants = ref([])

const FormField = defineComponent({
  props: {
    label: { type: String, required: true },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
  },
  setup(props, { slots }) {
    return () => h('div', [
      h('label', { class: 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400' }, [
        props.label,
        props.required ? h('span', { class: 'text-rose-500' }, ' *') : null,
      ]),
      slots.default?.(),
      props.error ? h('p', { class: 'mt-1 text-xs text-rose-600' }, props.error) : null,
    ])
  },
})

const inputClass = (field, extra = '') => {
  const base = 'w-full rounded-xl border bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:bg-slate-950 dark:text-slate-100'
  const classes = `${base} ${extra}`.trim()
  return errors[field]
    ? `${classes} border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 dark:border-rose-500`
    : `${classes} border-slate-300 dark:border-slate-700`
}

const sanitizeSriLankanPhone = (value) => {
  if (!value) return ''

  let cleaned = String(value).replace(/[^\d+]/g, '')
  cleaned = cleaned.replace(/^\+940?/, '0')
  cleaned = cleaned.replace(/^940?/, '0')

  if (!cleaned.startsWith('0') && /^\d{9}$/.test(cleaned)) {
    cleaned = `0${cleaned}`
  }

  return cleaned.substring(0, 10)
}

const normalizePhone = sanitizeSriLankanPhone

const validateField = (field) => {
  if (field === 'customer_name') {
    errors.customer_name = form.customer_name && form.customer_name.length >= 2 ? '' : 'Customer name is required.'
  } else if (field === 'phone') {
    const phone = normalizePhone(form.phone)
    form.phone = phone
    errors.phone = /^0\d{9}$/.test(phone) ? '' : 'Enter a valid Sri Lankan phone number.'
  } else if (field === 'additional_phone') {
    const phone = normalizePhone(form.additional_phone)
    errors.additional_phone = !phone || /^0\d{9}$/.test(phone) ? '' : 'Enter a valid additional phone.'
  } else if (field === 'email') {
    errors.email = !form.email || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email) ? '' : 'Enter a valid email.'
  } else if (field === 'address') {
    errors.address = form.address && form.address.length >= 6 ? '' : 'Address is required.'
  }
}

const validateForm = () => {
  ;['customer_name', 'phone', 'email', 'address'].forEach(validateField)
  return !errors.customer_name && !errors.phone && !errors.email && !errors.address
}

const fetchCustomers = async (search = '') => {
  const term = String(search || '').trim()
  if (term.length < 3) {
    customerOptions.value = []
    loadingCustomers.value = false
    return
  }

  loadingCustomers.value = true
  try {
    const { data } = await axios.get('/api/seller/customers/search', {
      params: { search: term, limit: 12 },
    })
    customerOptions.value = Array.isArray(data?.customers) ? data.customers : []
  } catch {
    customerOptions.value = []
  } finally {
    loadingCustomers.value = false
  }
}

const openCustomerDropdown = () => {
  customerDropdownOpen.value = customerOptions.value.length > 0
}

const closeCustomerDropdownWithDelay = () => {
  window.setTimeout(() => {
    customerDropdownOpen.value = false
  }, 120)
}

const onPhoneInput = (event) => {
  const phone = sanitizeSriLankanPhone(event?.target?.value || form.phone)
  form.phone = phone
  errors.phone = ''

  if (event?.target) {
    event.target.value = phone
  }

  window.clearTimeout(customerSearchTimer)
  if (phone.length < 3) {
    customerOptions.value = []
    customerDropdownOpen.value = false
    loadingCustomers.value = false
    return
  }

  customerDropdownOpen.value = true
  loadingCustomers.value = true
  customerSearchTimer = window.setTimeout(() => fetchCustomers(phone), 220)
}

const selectCustomer = (customer) => {
  form.customer_name = customer?.name || ''
  form.phone = sanitizeSriLankanPhone(customer?.phone || '')
  form.additional_phone = sanitizeSriLankanPhone(customer?.additional_phone || '')
  form.email = customer?.email || ''
  form.address = customer?.address || ''
  form.city_id = customer?.city_id || null
  form.city = customer?.city || ''
  form.notes = customer?.notes || ''
  citySearch.value = customer?.city || ''
  customerDropdownOpen.value = false
  ;['customer_name', 'phone', 'email', 'address'].forEach(validateField)
}

const clearCustomer = () => {
  Object.assign(form, {
    customer_name: '',
    phone: '',
    additional_phone: '',
    email: '',
    address: '',
    city_id: null,
    city: '',
    notes: '',
  })
  citySearch.value = ''
  customerOptions.value = []
  customerDropdownOpen.value = false
  Object.keys(errors).forEach((key) => {
    errors[key] = ''
  })
}

const fetchCities = async (search = '') => {
  loadingCities.value = true
  try {
    const { data } = await axios.get('/api/seller/cities', {
      params: { search: search || undefined, limit: 20 },
    })
    cityOptions.value = Array.isArray(data?.cities) ? data.cities : []
  } catch {
    cityOptions.value = []
  } finally {
    loadingCities.value = false
  }
}

const openCityDropdown = () => {
  cityDropdownOpen.value = true
  fetchCities(citySearch.value)
}

const closeCityDropdownWithDelay = () => {
  window.setTimeout(() => {
    cityDropdownOpen.value = false
  }, 120)
}

const onCitySearchInput = () => {
  form.city_id = null
  form.city = citySearch.value
  window.clearTimeout(citySearchTimer)
  citySearchTimer = window.setTimeout(() => fetchCities(citySearch.value), 220)
}

const selectCity = (city) => {
  form.city_id = city?.id || null
  form.city = city?.name_en || ''
  citySearch.value = city?.name_en || ''
  cityDropdownOpen.value = false
}

const clearSelectedCity = () => {
  form.city_id = null
  form.city = ''
  citySearch.value = ''
}

const hasMaximumPrice = (value) => value !== null && value !== undefined && value !== ''

const normalizeVariant = (variant) => {
  const rawAttrs = variant?.attributes || {}
  const attrs = {}
  Object.entries(rawAttrs).forEach(([key, rawMeta]) => {
    if (rawMeta && typeof rawMeta === 'object') {
      attrs[key] = rawMeta.label || rawMeta.name || rawMeta.value || rawMeta.color || ''
    } else {
      attrs[key] = rawMeta || ''
    }
  })
  return {
    ...variant,
    attributes: attrs,
    selling_price: variant?.reseller_price === null || variant?.reseller_price === undefined
      ? Number(variant?.price || 0)
      : Number(variant.reseller_price),
  }
}

const suggestedProducts = computed(() => {
  return products.value.slice(0, 80)
})

const fetchProducts = async (search = '') => {
  if (!search.trim()) {
    products.value = []
    loadingProducts.value = false
    return
  }

  const requestId = productSearchRequestId + 1
  productSearchRequestId = requestId
  loadingProducts.value = true
  try {
    const { data } = await axios.get('/api/seller/order-products', {
      params: { search: search || undefined, limit: 100 },
    })
    if (requestId !== productSearchRequestId) return
    products.value = Array.isArray(data?.products) ? data.products : []
  } catch (error) {
    if (requestId !== productSearchRequestId) return
    toast.error(error?.response?.data?.message || 'Failed to load products.')
  } finally {
    if (requestId === productSearchRequestId) {
      loadingProducts.value = false
    }
  }
}

const openProductDropdown = () => {
  productDropdownOpen.value = true
  if (productQuery.value.trim()) {
    fetchProducts(productQuery.value.trim())
  }
}

const closeProductDropdownWithDelay = () => {
  window.setTimeout(() => {
    productDropdownOpen.value = false
  }, 120)
}

const onProductInput = () => {
  productDropdownOpen.value = true
  activeProductIndex.value = -1
  products.value = []
  window.clearTimeout(productSearchTimer)
  const search = productQuery.value.trim()
  if (!search) {
    loadingProducts.value = false
    return
  }

  loadingProducts.value = true
  productSearchTimer = window.setTimeout(() => fetchProducts(search), 220)
}

const chooseProduct = (product) => {
  productDropdownOpen.value = false
  productQuery.value = ''

  const variants = Array.isArray(product?.variants) ? product.variants.map(normalizeVariant) : []
  if (variants.length <= 1) {
    addItemToCart(product, variants[0] || { id: null, price: 0, attributes: {} }, 1)
    return
  }

  modalProduct.value = product
  modalVariants.value = variants.map((variant) => ({
    ...variant,
    selected: false,
    quantity: 1,
  }))
  variantModalOpen.value = true
}

const moveActiveProduct = (delta) => {
  productDropdownOpen.value = true
  const list = suggestedProducts.value
  if (!list.length) return
  activeProductIndex.value = (Number(activeProductIndex.value || 0) + delta + list.length) % list.length
}

const pickActiveProduct = () => {
  const list = suggestedProducts.value
  if (!list.length) return
  const index = activeProductIndex.value >= 0 ? activeProductIndex.value : 0
  chooseProduct(list[index])
}

const formatVariantLabel = (variant) => {
  const attrs = variant?.attributes && typeof variant.attributes === 'object'
    ? Object.entries(variant.attributes).map(([key, value]) => `${key}: ${value}`).filter(Boolean)
    : []
  const sku = variant?.sku ? `SKU ${variant.sku}` : 'Variant'
  return attrs.length ? `${sku} | ${attrs.join(', ')}` : sku
}

const selectedModalCount = computed(() => modalVariants.value.filter((variant) => variant.selected).length)

const closeVariantModal = () => {
  variantModalOpen.value = false
  modalProduct.value = null
  modalVariants.value = []
}

const addModalVariantsToCart = () => {
  if (!modalProduct.value) return
  modalVariants.value
    .filter((variant) => variant.selected)
    .forEach((variant) => addItemToCart(modalProduct.value, variant, variant.quantity || 1))
  closeVariantModal()
}

const addItemToCart = (product, variant, quantity = 1) => {
  const existing = items.value.find((item) => Number(item.variant_id) === Number(variant.id))

  if (existing) {
    existing.quantity += Math.max(1, Number(quantity || 1))
    clampItem(existing)
    return
  }

  items.value.push({
    key: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    product_id: product.id,
    product_title: product.title,
    product_code: product.product_code || '',
    image: product.image || '',
    variant_id: variant.id,
    variant_label: formatVariantLabel(variant),
    quantity: Math.max(1, Number(quantity || 1)),
    price: Number(product.pricing_model === 'reseller' ? (variant.selling_price ?? variant.reseller_price) : variant.price || 0),
    pricing_model: product.pricing_model === 'reseller' ? 'reseller' : 'commission',
    reseller_price: Number(variant.reseller_price || 0),
    maximum_selling_price: hasMaximumPrice(variant.maximum_selling_price)
      ? Number(variant.maximum_selling_price)
      : null,
    delivery_fee: Number(product.delivery_fee || 0),
    is_free_shipping: Boolean(product.is_free_shipping),
    commission_rule: product.commission_rule || null,
  })
}

const removeItem = (index) => {
  items.value.splice(index, 1)
}

const changeQty = (item, delta) => {
  item.quantity = Math.max(1, Number(item.quantity || 1) + delta)
}

const clampItem = (item) => {
  item.quantity = Math.max(1, Number.parseInt(item.quantity || 1, 10))
}

const lineTotal = (item) => Math.max(0, Number(item.price || 0) * Number(item.quantity || 0))
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
const lineCommission = (item) => item.pricing_model === 'reseller'
  ? Math.max(0, Number(item.price || 0) - Number(item.reseller_price || 0)) * Number(item.quantity || 0)
  : commissionFor(item.price, item.quantity, item.commission_rule)
const variantCommission = (variant, product) => product?.pricing_model === 'reseller'
  ? Math.max(0, Number(variant?.selling_price ?? variant?.reseller_price ?? 0) - Number(variant?.reseller_price || 0)) * Number(variant?.quantity || 1)
  : commissionFor(variant?.price, variant?.quantity || 1, product?.commission_rule)
const subtotal = computed(() => items.value.reduce((sum, item) => sum + (Number(item.price || 0) * Number(item.quantity || 0)), 0))
const totalQty = computed(() => items.value.reduce((sum, item) => sum + Number(item.quantity || 0), 0))
const totalCommission = computed(() => items.value.reduce((sum, item) => sum + lineCommission(item), 0))
const deliveryCharge = computed(() => items.value.reduce((max, item) => {
  if (item.is_free_shipping) return max
  return Math.max(max, Number(item.delivery_fee || 0))
}, 0))
const grandTotal = computed(() => Math.max(0, subtotal.value + deliveryCharge.value))

const toMoney = (value) => Number(value || 0).toFixed(2)

const commissionRuleLabel = (rule) => {
  if (!rule) return 'LKR 0.00'

  const value = Math.max(0, Number(rule.value || 0))
  if (rule.type === 'percentage') {
    return `${value.toFixed(2).replace(/\.00$/, '')}%`
  }

  return `LKR ${toMoney(value)} per item`
}

const resetOrder = () => {
  clearCustomer()
  items.value = []
  productQuery.value = ''
  showSubmitModal.value = false
  closeVariantModal()
}

const openSubmitConfirmation = () => {
  if (!items.value.length) {
    toast.error('Add at least one product.')
    return
  }

  if (!validateForm()) {
    toast.error('Please correct customer details.')
    return
  }

  const invalidPrice = items.value.find((item) => item.pricing_model === 'reseller' && (
    Number(item.price) < Number(item.reseller_price)
    || (hasMaximumPrice(item.maximum_selling_price) && Number(item.price) > Number(item.maximum_selling_price))
  ))
  if (invalidPrice) {
    toast.error(`Selling price for ${invalidPrice.product_title} must be within its allowed range.`)
    return
  }

  showSubmitModal.value = true
}

const closeSubmitConfirmation = () => {
  if (submitting.value) return
  showSubmitModal.value = false
}

const submitOrder = async () => {
  showSubmitModal.value = false
  submitting.value = true
  try {
    const payload = {
      order_datetime: new Date().toISOString(),
      status: 'draft',
      total_discount: 0,
      customer: {
        name: form.customer_name,
        phone: form.phone,
        additional_phone: form.additional_phone || null,
        email: form.email || null,
        address: form.address,
        city_id: form.city_id || null,
        city: form.city || null,
        notes: form.notes || null,
      },
      items: items.value.map((item) => ({
        product_id: item.product_id,
        product_variant_id: item.variant_id,
        quantity: Number(item.quantity || 1),
        price: Number(item.price || 0),
      })),
    }

    const { data } = await axios.post('/api/seller/orders', payload)
    toast.success(data?.message || 'Order created successfully.')
    resetOrder()
    window.dispatchEvent(new CustomEvent('seller-order-created'))
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Unable to create order.')
  } finally {
    submitting.value = false
  }
}

</script>
