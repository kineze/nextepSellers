<template>
  <div class="overflow-hidden rounded-[2rem] border border-slate-200/70 bg-gradient-to-br from-white via-slate-50 to-sky-50/40 shadow-2xl shadow-slate-900/10 dark:border-slate-700/70 dark:from-slate-950 dark:via-slate-900 dark:to-slate-900">
    <div class="mx-auto w-full">
      <div class="grid gap-8 lg:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-700/80 dark:bg-slate-900/80">
          <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-600 dark:text-sky-300">Seller Registration</p>
          <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Become a nextepSellers Partner</h1>
          <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Complete the steps below to submit your seller onboarding request.</p>

          <div class="mt-8 space-y-4">
            <div
              v-for="(item, idx) in steps"
              :key="item.id"
              class="relative pl-12"
            >
              <span
                v-if="idx < steps.length - 1"
                class="absolute left-[0.9rem] top-7 h-[calc(100%+1rem)] w-px"
                :class="step > item.id ? 'bg-gradient-to-b from-emerald-400/70 to-emerald-300/30 dark:from-emerald-400/70 dark:to-emerald-500/20' : 'bg-gradient-to-b from-slate-300/80 to-slate-200/20 dark:from-slate-700 dark:to-slate-800/20'"
              />
              <span
                class="absolute left-0 top-1.5 inline-flex h-7 w-7 items-center justify-center rounded-full border text-xs font-black"
                :class="{
                  'border-emerald-500 bg-emerald-500 text-white shadow-lg shadow-emerald-500/30': getStepState(item.id) === 'completed',
                  'border-sky-500 bg-sky-500 text-white shadow-lg shadow-sky-500/30': getStepState(item.id) === 'active',
                  'border-slate-300 bg-white text-slate-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300': ['available', 'locked'].includes(getStepState(item.id)),
                }"
              >
                {{ getStepState(item.id) === 'completed' ? '✓' : item.id }}
              </span>
              <button
                type="button"
                class="w-full rounded-2xl border p-3 text-left transition"
                :class="{
                  'border-emerald-300 dark:border-emerald-500/40 bg-emerald-50/90 dark:bg-emerald-500/10': getStepState(item.id) === 'completed',
                  'border-sky-300 dark:border-sky-500/40 bg-sky-50 dark:bg-sky-500/10 shadow-sm shadow-sky-500/10': getStepState(item.id) === 'active',
                  'border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-600': ['available', 'locked'].includes(getStepState(item.id)),
                }"
                @click="openStep(item.id)"
              >
                <p
                  class="text-[11px] font-bold uppercase tracking-[0.14em]"
                  :class="{
                    'text-emerald-700 dark:text-emerald-300': getStepState(item.id) === 'completed',
                    'text-sky-700 dark:text-sky-300': getStepState(item.id) === 'active',
                    'text-slate-500 dark:text-slate-400': ['available', 'locked'].includes(getStepState(item.id)),
                  }"
                >
                  Step {{ item.id }}
                </p>
                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ item.label }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ item.hint }}</p>
              </button>
            </div>
          </div>
        </aside>

        <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-700/80 dark:bg-slate-900/80 sm:p-8">
          <p
            v-if="successMessage"
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
          >
            {{ successMessage }}
          </p>

          <p
            v-if="errors.verification"
            class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"
          >
            {{ errors.verification[0] }}
          </p>
          <p
            v-if="errors.general"
            class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"
          >
            {{ errors.general[0] }}
          </p>

          <div class="space-y-4">
            <section id="step-panel-1" class="rounded-2xl border border-slate-200/80 bg-white/85 p-1.5 shadow-sm dark:border-slate-700/80 dark:bg-slate-900/80">
              <button
                type="button"
                :class="[accordionHeadClasses, step === 1 ? accordionHeadActiveClasses : '']"
                @click="openStep(1)"
              >
                <span class="text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-sky-700 dark:text-slate-300">Step 1</span>
                <span class="flex-1 text-[0.95rem] font-extrabold text-slate-900 dark:text-slate-100">General Information</span>
                <span v-if="getStepState(1) === 'completed'" class="rounded-full border border-emerald-300 bg-emerald-50 px-2 py-1 text-[0.66rem] font-extrabold uppercase tracking-[0.08em] text-emerald-700 dark:border-emerald-400/40 dark:bg-emerald-500/10 dark:text-emerald-300">Completed</span>
              </button>

              <div v-if="step === 1" class="space-y-5 px-2 pb-2 pt-4">
                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">First Name</label>
                    <input v-model="form.first_name" type="text" :class="inputClasses" placeholder="Enter first name" autocomplete="given-name" />
                    <p v-if="errors.first_name" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.first_name[0] }}</p>
                  </div>

                  <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Last Name</label>
                    <input v-model="form.last_name" type="text" :class="inputClasses" placeholder="Enter last name" autocomplete="family-name" />
                    <p v-if="errors.last_name" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.last_name[0] }}</p>
                  </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Email</label>
                    <div class="flex gap-2">
                      <input v-model="form.email" type="email" :class="[inputClasses, 'flex-1']" placeholder="name@example.com" autocomplete="email" />
                      <button type="button" :class="[buttonBaseClasses, form.email_verified ? verifyButtonVerifiedClasses : verifyButtonClasses]" @click="verifyEmail">
                        {{ form.email_verified ? 'Verified' : 'Verify' }}
                      </button>
                    </div>
                    <p v-if="errors.email" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.email[0] }}</p>
                  </div>

                  <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Phone</label>
                    <input v-model="form.phone" type="text" :class="inputClasses" placeholder="+94 77 123 4567" autocomplete="tel" />
                    <p v-if="errors.phone" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.phone[0] }}</p>
                  </div>
                </div>

                <p class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-medium text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200">
                  To continue to the next step, email verification is mandatory.
                </p>

                <div class="flex justify-end">
                  <button type="button" :class="primaryButtonClasses" @click="goToStepTwo">Continue</button>
                </div>
              </div>

              <p v-else-if="getStepState(1) === 'completed'" class="mx-2 mb-2 mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                {{ steps[0].summary }}
              </p>
            </section>

            <section id="step-panel-2" class="rounded-2xl border border-slate-200/80 bg-white/85 p-1.5 shadow-sm dark:border-slate-700/80 dark:bg-slate-900/80">
              <button
                type="button"
                :class="[accordionHeadClasses, step === 2 ? accordionHeadActiveClasses : '']"
                @click="openStep(2)"
              >
                <span class="text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-sky-700 dark:text-slate-300">Step 2</span>
                <span class="flex-1 text-[0.95rem] font-extrabold text-slate-900 dark:text-slate-100">Detailed Information</span>
                <span v-if="getStepState(2) === 'completed'" class="rounded-full border border-emerald-300 bg-emerald-50 px-2 py-1 text-[0.66rem] font-extrabold uppercase tracking-[0.08em] text-emerald-700 dark:border-emerald-400/40 dark:bg-emerald-500/10 dark:text-emerald-300">Completed</span>
              </button>

              <div v-if="step === 2" class="space-y-5 px-2 pb-2 pt-4">
                <div>
                  <p class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Registration Type</p>
                  <div class="grid gap-3 sm:grid-cols-2">
                    <label class="group block cursor-pointer">
                      <input
                        v-model="form.seller_type"
                        type="radio"
                        name="seller_type"
                        value="individual"
                        class="peer sr-only"
                      />
                      <div class="relative rounded-2xl border border-slate-300 bg-white p-4 transition duration-200 hover:-translate-y-0.5 hover:shadow-sm peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:shadow-[0_10px_24px_rgba(14,165,233,0.16)] peer-focus-visible:ring-4 peer-focus-visible:ring-sky-100 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600 dark:peer-checked:border-sky-400/70 dark:peer-checked:bg-sky-500/10 dark:peer-checked:shadow-[0_10px_24px_rgba(14,165,233,0.22)] dark:peer-focus-visible:ring-sky-500/20">
                        <div class="flex items-start justify-between gap-3">
                          <div>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">Individual</p>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Register as a single user.</p>
                          </div>
                          <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-300 bg-white text-transparent transition peer-checked:border-sky-500 peer-checked:bg-sky-500 peer-checked:text-white dark:border-slate-600 dark:bg-slate-800 dark:peer-checked:border-sky-400 dark:peer-checked:bg-sky-400 dark:peer-checked:text-slate-950">
                            ✓
                          </span>
                        </div>
                      </div>
                    </label>

                    <label class="group block cursor-pointer">
                      <input
                        v-model="form.seller_type"
                        type="radio"
                        name="seller_type"
                        value="business"
                        class="peer sr-only"
                      />
                      <div class="relative rounded-2xl border border-slate-300 bg-white p-4 transition duration-200 hover:-translate-y-0.5 hover:shadow-sm peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:shadow-[0_10px_24px_rgba(14,165,233,0.16)] peer-focus-visible:ring-4 peer-focus-visible:ring-sky-100 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600 dark:peer-checked:border-sky-400/70 dark:peer-checked:bg-sky-500/10 dark:peer-checked:shadow-[0_10px_24px_rgba(14,165,233,0.22)] dark:peer-focus-visible:ring-sky-500/20">
                        <div class="flex items-start justify-between gap-3">
                          <div>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">Business</p>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Register with company information.</p>
                          </div>
                          <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-300 bg-white text-transparent transition peer-checked:border-sky-500 peer-checked:bg-sky-500 peer-checked:text-white dark:border-slate-600 dark:bg-slate-800 dark:peer-checked:border-sky-400 dark:peer-checked:bg-sky-400 dark:peer-checked:text-slate-950">
                            ✓
                          </span>
                        </div>
                      </div>
                    </label>
                  </div>
                  <p v-if="errors.seller_type" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.seller_type[0] }}</p>
                </div>

                <div v-if="form.seller_type === 'business'" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 p-4">
                  <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Business Information</p>
                  <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Business Name</label>
                      <input v-model="form.business_name" type="text" :class="inputClasses" placeholder="Business legal name" />
                      <p v-if="errors.business_name" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.business_name[0] }}</p>
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Registration Number</label>
                      <input v-model="form.business_registration_number" type="text" :class="inputClasses" placeholder="Registration number" />
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Business Type</label>
                      <input v-model="form.business_type" type="text" :class="inputClasses" placeholder="e.g. Sole Proprietorship" />
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Registered Date</label>
                      <input v-model="form.business_registered_date" type="date" :class="inputClasses" />
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Address Line 1</label>
                      <input v-model="form.address_line_1" type="text" :class="inputClasses" placeholder="Street and number" />
                      <p v-if="errors.address_line_1" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.address_line_1[0] }}</p>
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Address Line 2</label>
                      <input v-model="form.address_line_2" type="text" :class="inputClasses" placeholder="Apartment, suite, etc. (optional)" />
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">City</label>
                      <input v-model="form.city" type="text" :class="inputClasses" placeholder="City" />
                      <p v-if="errors.city" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.city[0] }}</p>
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">District</label>
                      <input v-model="form.district" type="text" :class="inputClasses" placeholder="District" />
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Postal Code</label>
                      <input v-model="form.postal_code" type="text" :class="inputClasses" placeholder="Postal code" />
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Country</label>
                      <input v-model="form.country" type="text" :class="inputClasses" placeholder="Country" />
                    </div>
                  </div>
                </div>

                <div v-if="form.seller_type === 'individual'" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 p-4">
                  <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Individual Information</p>
                  <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                    You are registering as an individual. Personal tax and NIC details below are required.
                  </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Tax Number</label>
                    <input v-model="form.tax_number" type="text" :class="inputClasses" placeholder="Tax number" />
                    <p v-if="errors.tax_number" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.tax_number[0] }}</p>
                  </div>
                  <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">NIC Number</label>
                    <input v-model="form.nic_number" type="text" :class="inputClasses" placeholder="NIC number" />
                    <p v-if="errors.nic_number" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.nic_number[0] }}</p>
                  </div>
                </div>

                <div class="flex justify-between gap-3">
                  <button type="button" :class="secondaryButtonClasses" @click="openStep(1)">Back</button>
                  <button type="button" :class="primaryButtonClasses" @click="goToStepThree">Continue</button>
                </div>
              </div>

              <p v-else-if="getStepState(2) === 'completed'" class="mx-2 mb-2 mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                {{ steps[1].summary }}
              </p>
            </section>

            <section id="step-panel-3" class="rounded-2xl border border-slate-200/80 bg-white/85 p-1.5 shadow-sm dark:border-slate-700/80 dark:bg-slate-900/80">
              <button
                type="button"
                :class="[accordionHeadClasses, step === 3 ? accordionHeadActiveClasses : '']"
                @click="openStep(3)"
              >
                <span class="text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-sky-700 dark:text-slate-300">Step 3</span>
                <span class="flex-1 text-[0.95rem] font-extrabold text-slate-900 dark:text-slate-100">Uploads & Agreement</span>
              </button>

              <div v-if="step === 3" class="space-y-5 px-2 pb-2 pt-4">
                <div class="grid gap-4 sm:grid-cols-2">
                  <div v-for="field in uploadFields" :key="field.key" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-900/50">
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ field.label }}</label>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ field.helper }}</p>
                      </div>
                      <button type="button" class="rounded-xl border border-sky-500 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700 transition hover:bg-sky-100 dark:border-sky-400/50 dark:bg-sky-500/10 dark:text-sky-200 dark:hover:bg-sky-500/20" @click="triggerFilePicker(field.key)">Choose</button>
                    </div>

                    <input
                      :ref="(el) => setFileInputRef(field.key, el)"
                      type="file"
                      class="sr-only"
                      :accept="field.accept"
                      @change="onFileChange($event, field.key)"
                    />

                    <div v-if="filePreviewUrls[field.key]" class="mt-3 flex min-h-[8.5rem] items-center justify-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-white dark:border-slate-700 dark:bg-slate-900">
                      <img :src="filePreviewUrls[field.key]" :alt="`${field.label} preview`" class="h-full max-h-56 w-full object-cover" />
                    </div>
                    <div v-else class="mt-3 flex min-h-[8.5rem] items-center justify-center rounded-xl border border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-indigo-50 dark:border-slate-700 dark:from-slate-900 dark:to-slate-800">
                      <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">No preview yet</span>
                    </div>

                    <div v-if="fileMeta[field.key]" class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-900">
                      <div class="min-w-0">
                        <p class="truncate text-xs font-semibold text-slate-700 dark:text-slate-200">{{ fileMeta[field.key].name }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ formatFileSize(fileMeta[field.key].size) }}</p>
                      </div>
                      <button type="button" class="text-xs font-semibold text-rose-600 hover:text-rose-700" @click="clearFile(field.key)">Remove</button>
                    </div>

                    <p v-if="errors[field.key]" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors[field.key][0] }}</p>
                  </div>
                </div>

                <div class="rounded-2xl border border-sky-200 dark:border-slate-700 bg-sky-50 dark:bg-slate-900/60 p-4">
                  <p class="text-sm font-semibold text-sky-900 dark:text-white">Seller Agreement</p>
                  <p class="mt-2 text-sm leading-6 text-sky-800 dark:text-slate-300">
                    By submitting this form, you confirm that all provided information is accurate, documents are valid, and
                    nextepSellers may review and verify your submission for onboarding compliance.
                  </p>

                  <label class="mt-4 flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 p-3">
                    <input v-model="form.agreement_accepted" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">I agree to the Seller Agreement and consent to verification.</span>
                  </label>
                  <p v-if="errors.agreement_accepted" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ errors.agreement_accepted[0] }}</p>
                </div>

                <div class="flex justify-between gap-3">
                  <button type="button" :class="secondaryButtonClasses" @click="openStep(2)">Back</button>
                  <button type="button" :class="primaryButtonClasses" @click="goToStepFour">Continue</button>
                </div>
              </div>
            </section>

            <section id="step-panel-4" class="rounded-2xl border border-slate-200/80 bg-white/85 p-1.5 shadow-sm dark:border-slate-700/80 dark:bg-slate-900/80">
              <button
                type="button"
                :class="[accordionHeadClasses, step === 4 ? accordionHeadActiveClasses : '']"
                @click="openStep(4)"
              >
                <span class="text-[0.68rem] font-extrabold uppercase tracking-[0.08em] text-sky-700 dark:text-slate-300">Step 4</span>
                <span class="flex-1 text-[0.95rem] font-extrabold text-slate-900 dark:text-slate-100">Review & Submit</span>
              </button>

              <div v-if="step === 4" class="space-y-5 px-2 pb-2 pt-4">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-400/30 dark:bg-emerald-500/10">
                  <div class="flex items-start gap-4">
                    <svg class="h-14 w-14 shrink-0 text-emerald-500 dark:text-emerald-300" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                      <circle cx="32" cy="32" r="30" stroke="currentColor" stroke-width="2" opacity="0.25" />
                      <path d="M20 18h24v7a12 12 0 0 1-24 0v-7Z" fill="currentColor" opacity="0.18" />
                      <path d="M22 18h20v7a10 10 0 0 1-20 0v-7Z" stroke="currentColor" stroke-width="2" />
                      <path d="M32 35v9M25 52h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                      <path d="M19 23h-4a5 5 0 0 0 5 5M45 23h4a5 5 0 0 1-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                      <path d="m28 27 3 3 5-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div>
                      <p class="text-sm font-extrabold uppercase tracking-[0.08em] text-emerald-700 dark:text-emerald-300">Congratulations</p>
                      <p class="mt-1 text-base font-semibold text-emerald-900 dark:text-emerald-100">You completed all registration steps.</p>
                      <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-200/90">Review your details below and submit your onboarding request.</p>
                    </div>
                  </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">Profile</p>
                    <div class="mt-3 space-y-2 text-sm">
                      <p class="text-slate-700 dark:text-slate-200"><span class="font-semibold">Name:</span> {{ form.first_name }} {{ form.last_name }}</p>
                      <p class="text-slate-700 dark:text-slate-200"><span class="font-semibold">Email:</span> {{ form.email }}</p>
                      <p class="text-slate-700 dark:text-slate-200"><span class="font-semibold">Phone:</span> {{ form.phone || '-' }}</p>
                      <p class="text-slate-700 dark:text-slate-200"><span class="font-semibold">Type:</span> {{ form.seller_type === 'business' ? 'Business' : 'Individual' }}</p>
                    </div>
                  </div>

                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-slate-500 dark:text-slate-400">Documents</p>
                    <ul class="mt-3 space-y-2 text-sm">
                      <li v-for="doc in submissionDocuments" :key="doc.label" class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-900">
                        <span class="font-medium text-slate-700 dark:text-slate-200">{{ doc.label }}</span>
                        <span class="truncate text-xs text-slate-500 dark:text-slate-400">{{ doc.name }}</span>
                      </li>
                    </ul>
                  </div>
                </div>

                <div class="flex justify-between gap-3">
                  <button type="button" :class="secondaryButtonClasses" @click="openStep(3)">Back</button>
                  <button type="button" :class="primaryButtonClasses" :disabled="submitting" @click="submitForm">
                    {{ submitting ? 'Submitting...' : 'Submit Registration' }}
                  </button>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showEmailOtpModal" class="fixed inset-0 z-[1500] flex items-center justify-center bg-slate-900/70 px-4">
      <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-2xl">
        <div class="mb-4 flex items-start justify-between">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Verify Email</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
              Enter the 6-digit code sent to <span class="font-semibold">{{ form.email }}</span>.
            </p>
          </div>
          <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="showEmailOtpModal = false">
            ✕
          </button>
        </div>

        <p v-if="emailOtpMessage" class="mb-3 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-700">
          {{ emailOtpMessage }}
        </p>

        <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Verification Code</label>
        <input
          v-model="emailOtpCode"
          type="text"
          maxlength="6"
          :class="[inputClasses, otpInputClasses]"
          placeholder="Enter 6-digit OTP"
        />
        <p v-if="emailOtpError" class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ emailOtpError }}</p>

        <div class="mt-4 flex justify-end gap-2">
          <button type="button" :class="secondaryButtonClasses" :disabled="emailOtpSending" @click="verifyEmail">
            {{ emailOtpSending ? 'Sending...' : 'Resend Code' }}
          </button>
          <button type="button" :class="primaryButtonClasses" :disabled="emailOtpVerifying" @click="confirmEmailOtp">
            {{ emailOtpVerifying ? 'Verifying...' : 'Verify Email' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { useToast } from 'vue-toastification'

const props = defineProps({
  submitUrl: {
    type: String,
    required: true,
  },
  emailOtpSendUrl: {
    type: String,
    required: true,
  },
  emailOtpVerifyUrl: {
    type: String,
    required: true,
  },
  csrfToken: {
    type: String,
    required: true,
  },
})

const step = ref(1)
const completedStep = ref(0)
const submitting = ref(false)
const successMessage = ref('')
const errors = ref({})
const showEmailOtpModal = ref(false)
const emailOtpCode = ref('')
const emailOtpSending = ref(false)
const emailOtpVerifying = ref(false)
const emailOtpMessage = ref('')
const emailOtpError = ref('')
const toast = useToast()

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  email_verified: false,
  seller_type: 'individual',
  tax_number: '',
  nic_number: '',

  business_name: '',
  business_registration_number: '',
  business_type: '',
  business_registered_date: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  district: '',
  postal_code: '',
  country: 'Sri Lanka',

  seller_image: null,
  nic_front: null,
  nic_back: null,
  business_registration_document: null,
  agreement_accepted: false,
})

const steps = computed(() => [
  { id: 1, label: 'General Information', hint: 'Profile and email verification', summary: 'Profile details verified.' },
  { id: 2, label: 'Detailed Information', hint: 'Registration and tax details', summary: 'Registration details saved.' },
  { id: 3, label: 'Uploads & Agreement', hint: 'Documents and final consent', summary: 'Documents ready for submission.' },
  { id: 4, label: 'Review & Submit', hint: 'Submission summary and confirmation', summary: 'Ready to submit.' },
])

const inputClasses = 'w-full rounded-xl border border-slate-300/90 bg-white px-3.5 py-2.5 text-[0.92rem] text-slate-900 shadow-sm transition duration-200 placeholder:text-slate-400 hover:border-slate-400 focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:hover:border-slate-600 dark:focus:border-sky-400 dark:focus:ring-sky-500/20'
const otpInputClasses = 'text-center text-[1.04rem] font-bold tracking-[0.32em]'
const buttonBaseClasses = 'rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-[0.06em] transition duration-200 hover:-translate-y-0.5 focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:opacity-60 disabled:transform-none'
const verifyButtonClasses = 'border border-sky-300 bg-sky-50 text-sky-700 focus:ring-sky-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:focus:ring-slate-700/50'
const verifyButtonVerifiedClasses = 'border border-emerald-300 bg-emerald-50 text-emerald-700 focus:ring-emerald-100 dark:border-emerald-500/50 dark:bg-emerald-500/10 dark:text-emerald-300 dark:focus:ring-emerald-500/20'
const primaryButtonClasses = `${buttonBaseClasses} border border-sky-500 bg-gradient-to-r from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-500/25 focus:ring-sky-200 dark:border-sky-300 dark:from-sky-400 dark:to-cyan-400 dark:text-slate-950 dark:focus:ring-sky-500/20`
const secondaryButtonClasses = `${buttonBaseClasses} border border-slate-300 bg-white text-slate-700 focus:ring-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:ring-slate-700/50`
const accordionHeadClasses = 'flex w-full items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-left transition duration-200 dark:border-slate-700 dark:bg-slate-900'
const accordionHeadActiveClasses = 'border-sky-300 bg-sky-50 shadow-sm shadow-sky-500/10 dark:border-slate-600 dark:bg-slate-800'

const unlockedStep = computed(() => Math.min(4, completedStep.value + 1))

const filePreviewUrls = reactive({
  seller_image: '',
  nic_front: '',
  nic_back: '',
  business_registration_document: '',
})

const fileMeta = reactive({
  seller_image: null,
  nic_front: null,
  nic_back: null,
  business_registration_document: null,
})

const fileInputRefs = ref({})

const uploadFields = computed(() => {
  const baseFields = [
    {
      key: 'seller_image',
      label: 'Seller Image (Optional)',
      accept: '.jpg,.jpeg,.png,.webp',
      helper: 'Recommended: square image, JPG/PNG/WebP.',
    },
    {
      key: 'nic_front',
      label: 'NIC Front',
      accept: '.jpg,.jpeg,.png,.pdf',
      helper: 'Upload clear front side of NIC.',
    },
    {
      key: 'nic_back',
      label: 'NIC Back',
      accept: '.jpg,.jpeg,.png,.pdf',
      helper: 'Upload clear back side of NIC.',
    },
  ]

  if (form.seller_type === 'business') {
    baseFields.push({
      key: 'business_registration_document',
      label: 'Business Registration Document',
      accept: '.jpg,.jpeg,.png,.pdf',
      helper: 'Certificate or equivalent registration proof.',
    })
  }

  return baseFields
})

const submissionDocuments = computed(() => {
  const docs = [
    { label: 'Seller Image', name: form.seller_image?.name || 'Not provided' },
    { label: 'NIC Front', name: form.nic_front?.name || 'Missing' },
    { label: 'NIC Back', name: form.nic_back?.name || 'Missing' },
  ]

  if (form.seller_type === 'business') {
    docs.push({
      label: 'Business Registration',
      name: form.business_registration_document?.name || 'Missing',
    })
  }

  return docs
})

watch(() => form.email, () => {
  form.email_verified = false
  emailOtpCode.value = ''
  emailOtpError.value = ''
  emailOtpMessage.value = ''
})

function resetErrors() {
  errors.value = {}
}

function getStepState(stepId) {
  if (stepId <= completedStep.value) return 'completed'
  if (stepId === step.value) return 'active'
  if (stepId <= unlockedStep.value) return 'available'
  return 'locked'
}

function openStep(stepId) {
  if (stepId > unlockedStep.value && stepId > completedStep.value) return
  step.value = stepId
}

function scrollToStep(stepId) {
  nextTick(() => {
    const target = document.getElementById(`step-panel-${stepId}`)
    target?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

function notifyErrorMessages(payload) {
  Object.values(payload || {})
    .flat()
    .forEach((message) => toast.error(message))
}

async function verifyEmail() {
  resetErrors()
  emailOtpError.value = ''
  emailOtpMessage.value = ''

  if (!form.email || !/^\S+@\S+\.\S+$/.test(form.email)) {
    errors.value.email = ['Enter a valid email before verifying.']
    notifyErrorMessages(errors.value)
    return
  }

  emailOtpSending.value = true

  try {
    const response = await window.axios.post(props.emailOtpSendUrl, {
      _token: props.csrfToken,
      email: form.email,
      first_name: form.first_name,
    })

    emailOtpMessage.value = response.data.message || 'Verification code sent to your email.'
    showEmailOtpModal.value = true
  } catch (error) {
    if (error.response?.status === 422) {
      const message = error.response?.data?.message || 'Unable to send OTP.'
      errors.value.email = [message]
      notifyErrorMessages(errors.value)
      return
    }
    errors.value.email = ['Failed to send verification code. Please try again.']
    notifyErrorMessages(errors.value)
  } finally {
    emailOtpSending.value = false
  }
}

function goToStepTwo() {
  resetErrors()

  if (!form.first_name) errors.value.first_name = ['First name is required.']
  if (!form.last_name) errors.value.last_name = ['Last name is required.']
  if (!form.email || !/^\S+@\S+\.\S+$/.test(form.email)) errors.value.email = ['A valid email is required.']

  if (!form.email_verified) {
    errors.value.verification = ['Verify your email before continuing.']
  }

  if (Object.keys(errors.value).length) {
    notifyErrorMessages(errors.value)
    return
  }
  completedStep.value = Math.max(completedStep.value, 1)
  step.value = 2
  scrollToStep(2)
}

function goToStepThree() {
  resetErrors()

  if (!form.seller_type) errors.value.seller_type = ['Select registration type.']

  if (form.seller_type === 'business') {
    if (!form.business_name) errors.value.business_name = ['Business name is required.']
    if (!form.address_line_1) errors.value.address_line_1 = ['Address line 1 is required.']
    if (!form.city) errors.value.city = ['City is required.']
  }

  if (Object.keys(errors.value).length) {
    notifyErrorMessages(errors.value)
    return
  }
  completedStep.value = Math.max(completedStep.value, 2)
  step.value = 3
  scrollToStep(3)
}

function goToStepFour() {
  resetErrors()

  if (!form.nic_front) errors.value.nic_front = ['NIC front is required.']
  if (!form.nic_back) errors.value.nic_back = ['NIC back is required.']
  if (form.seller_type === 'business' && !form.business_registration_document) {
    errors.value.business_registration_document = ['Business registration document is required for business registration.']
  }
  if (!form.agreement_accepted) errors.value.agreement_accepted = ['You must accept the seller agreement.']

  if (Object.keys(errors.value).length) {
    notifyErrorMessages(errors.value)
    return
  }

  completedStep.value = Math.max(completedStep.value, 3)
  step.value = 4
  scrollToStep(4)
}

function onFileChange(event, key) {
  const [file] = event.target.files || []
  if (filePreviewUrls[key]) {
    URL.revokeObjectURL(filePreviewUrls[key])
  }
  form[key] = file || null

  if (!file) {
    filePreviewUrls[key] = ''
    fileMeta[key] = null
    return
  }

  fileMeta[key] = {
    name: file.name,
    size: file.size,
    type: file.type || '',
  }

  filePreviewUrls[key] = file.type.startsWith('image/') ? URL.createObjectURL(file) : ''
}

function setFileInputRef(key, el) {
  if (el) fileInputRefs.value[key] = el
}

function triggerFilePicker(key) {
  fileInputRefs.value[key]?.click()
}

function clearFile(key) {
  if (filePreviewUrls[key]) {
    URL.revokeObjectURL(filePreviewUrls[key])
  }
  filePreviewUrls[key] = ''
  fileMeta[key] = null
  form[key] = null
  if (fileInputRefs.value[key]) {
    fileInputRefs.value[key].value = ''
  }
}

function formatFileSize(bytes) {
  if (!bytes) return '0 KB'
  const units = ['B', 'KB', 'MB', 'GB']
  let value = bytes
  let unitIndex = 0
  while (value >= 1024 && unitIndex < units.length - 1) {
    value /= 1024
    unitIndex += 1
  }
  const decimals = value < 10 && unitIndex > 0 ? 1 : 0
  return `${value.toFixed(decimals)} ${units[unitIndex]}`
}

function clearAllFilePreviews() {
  Object.values(filePreviewUrls).forEach((url) => {
    if (url) URL.revokeObjectURL(url)
  })
}

async function confirmEmailOtp() {
  emailOtpError.value = ''

  if (!/^\d{6}$/.test(emailOtpCode.value)) {
    emailOtpError.value = 'Enter a valid 6-digit code.'
    toast.error(emailOtpError.value)
    return
  }

  emailOtpVerifying.value = true

  try {
    const response = await window.axios.post(props.emailOtpVerifyUrl, {
      _token: props.csrfToken,
      email: form.email,
      otp: emailOtpCode.value,
    })

    form.email_verified = true
    showEmailOtpModal.value = false
    emailOtpCode.value = ''
    emailOtpMessage.value = response.data.message || 'Email verified successfully.'
  } catch (error) {
    if (error.response?.status === 422) {
      emailOtpError.value = error.response?.data?.message || 'Invalid verification code.'
      toast.error(emailOtpError.value)
      return
    }
    emailOtpError.value = 'Unable to verify code right now. Please try again.'
    toast.error(emailOtpError.value)
  } finally {
    emailOtpVerifying.value = false
  }
}

function buildPayload() {
  const payload = new FormData()
  payload.append('_token', props.csrfToken)

  Object.entries(form).forEach(([key, value]) => {
    if (value === null || value === undefined) return

    if (typeof value === 'boolean') {
      payload.append(key, value ? '1' : '0')
      return
    }

    payload.append(key, value)
  })

  return payload
}

function resetForm() {
  clearAllFilePreviews()

  Object.assign(form, {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    email_verified: false,
    seller_type: 'individual',
    tax_number: '',
    nic_number: '',
    business_name: '',
    business_registration_number: '',
    business_type: '',
    business_registered_date: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    district: '',
    postal_code: '',
    country: 'Sri Lanka',
    seller_image: null,
    nic_front: null,
    nic_back: null,
    business_registration_document: null,
    agreement_accepted: false,
  })
  showEmailOtpModal.value = false
  emailOtpCode.value = ''
  emailOtpMessage.value = ''
  emailOtpError.value = ''
  completedStep.value = 0

  Object.keys(fileMeta).forEach((key) => {
    fileMeta[key] = null
  })
  Object.keys(filePreviewUrls).forEach((key) => {
    filePreviewUrls[key] = ''
  })
  Object.values(fileInputRefs.value).forEach((el) => {
    if (el) el.value = ''
  })
}

async function submitForm() {
  resetErrors()

  if (!form.nic_front) errors.value.nic_front = ['NIC front is required.']
  if (!form.nic_back) errors.value.nic_back = ['NIC back is required.']
  if (form.seller_type === 'business' && !form.business_registration_document) {
    errors.value.business_registration_document = ['Business registration document is required for business registration.']
  }
  if (!form.agreement_accepted) errors.value.agreement_accepted = ['You must accept the seller agreement.']

  if (Object.keys(errors.value).length) {
    notifyErrorMessages(errors.value)
    return
  }

  submitting.value = true

  try {
    const response = await window.axios.post(props.submitUrl, buildPayload(), {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    successMessage.value = response.data.message || 'Seller registration submitted successfully.'
    toast.success(successMessage.value)
    resetForm()
    step.value = 1
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {
        general: ['Validation failed. Please review your form.'],
      }
      notifyErrorMessages(errors.value)
      return
    }

    errors.value = {
      general: ['Something went wrong while submitting your registration. Please try again.'],
    }
    notifyErrorMessages(errors.value)
  } finally {
    submitting.value = false
  }
}

onBeforeUnmount(() => {
  clearAllFilePreviews()
})
</script>
