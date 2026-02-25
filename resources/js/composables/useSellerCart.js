import { computed, reactive } from 'vue'

const STORAGE_KEY = 'nextep-seller-cart-v1'

const state = reactive({
  items: [],
  hydrated: false,
})

const toNumber = (value) => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : null
}

const buildItemKey = (productId, variantId) => `${Number(productId)}:${variantId ? Number(variantId) : 'base'}`

const hydrate = () => {
  if (state.hydrated || typeof window === 'undefined') return
  state.hydrated = true

  try {
    const raw = window.localStorage.getItem(STORAGE_KEY)
    if (!raw) return

    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) return

    state.items = parsed
      .map((item) => ({
        key: String(item.key || buildItemKey(item.productId, item.variantId)),
        productId: Number(item.productId || 0),
        variantId: item.variantId ? Number(item.variantId) : null,
        title: String(item.title || 'Product'),
        productCode: String(item.productCode || ''),
        image: String(item.image || ''),
        sku: String(item.sku || ''),
        attributes: item.attributes && typeof item.attributes === 'object' ? item.attributes : {},
        price: toNumber(item.price),
        qty: Math.max(1, Number(item.qty || 1)),
      }))
      .filter((item) => item.productId > 0)
  } catch {
    state.items = []
  }
}

const persist = () => {
  if (typeof window === 'undefined') return
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(state.items))
}

export function useSellerCart() {
  hydrate()

  const addItem = (payload) => {
    const key = buildItemKey(payload.productId, payload.variantId)
    const qty = Math.max(1, Number(payload.qty || 1))

    const existing = state.items.find((item) => item.key === key)
    if (existing) {
      existing.qty += qty
      persist()
      return existing
    }

    const next = {
      key,
      productId: Number(payload.productId),
      variantId: payload.variantId ? Number(payload.variantId) : null,
      title: String(payload.title || 'Product'),
      productCode: String(payload.productCode || ''),
      image: String(payload.image || ''),
      sku: String(payload.sku || ''),
      attributes: payload.attributes && typeof payload.attributes === 'object' ? payload.attributes : {},
      price: toNumber(payload.price),
      qty,
    }

    state.items.unshift(next)
    persist()
    return next
  }

  const removeItem = (itemKey) => {
    state.items = state.items.filter((item) => item.key !== itemKey)
    persist()
  }

  const updateItemQty = (itemKey, qty) => {
    const target = state.items.find((item) => item.key === itemKey)
    if (!target) return

    const nextQty = Math.max(1, Number(qty || 1))
    target.qty = nextQty
    persist()
  }

  const clear = () => {
    state.items = []
    persist()
  }

  const totalItems = computed(() => state.items.reduce((sum, item) => sum + Number(item.qty || 0), 0))

  const subtotal = computed(() => state.items.reduce((sum, item) => {
    const price = Number(item.price)
    if (!Number.isFinite(price)) return sum
    return sum + (price * Number(item.qty || 0))
  }, 0))

  return {
    items: computed(() => state.items),
    totalItems,
    subtotal,
    addItem,
    removeItem,
    updateItemQty,
    clear,
  }
}
