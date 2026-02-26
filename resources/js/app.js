import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'vue-toastification/dist/index.css';
import './toast-theme.css';

import Toast, { POSITION } from "vue-toastification";

import { createApp } from 'vue'

import DarkModeToggle from './components/DarkModeToggle.vue';
import RolePermissionManager from './components/RolePermissionManager.vue';
import UserManager from './components/UserManager.vue';
import SellerRegistrationForm from './components/SellerRegistrationForm.vue';
import SellerManager from './components/SellerManager.vue';
import ActiveSellerManager from './components/ActiveSellerManager.vue';
import SellerProfile from './components/SellerProfile.vue';
import LevelManager from './components/LevelManager.vue';
import CategoryManager from './components/CategoryManager.vue';
import ProductManager from './components/ProductManager.vue';
import SupplierManager from './components/SupplierManager.vue';
import AttributeManager from './components/AttributeManager.vue';
import ProductCreateManager from './components/ProductCreateManager.vue';
import SellerProductVariantSelector from './components/SellerProductVariantSelector.vue';
import SellerProductShow from './components/SellerProductShow.vue';
import SellerCartNavLink from './components/SellerCartNavLink.vue';
import SellerFloatingCart from './components/SellerFloatingCart.vue';
import CurfoxLogin from './components/CurfoxLogin.vue';
import CitySync from './components/CitySync.vue';
import StateMatcher from './components/StateMatcher.vue';
import SellerOrderSubmit from './components/SellerOrderSubmit.vue';
import SellerMyOrders from './components/SellerMyOrders.vue';
import SellerOrderShow from './components/SellerOrderShow.vue';
import AdminDraftOrders from './components/AdminDraftOrders.vue';
import AdminApprovedOrders from './components/AdminApprovedOrders.vue';
import AdminPackedOrders from './components/AdminPackedOrders.vue';
import AdminShippedOrders from './components/AdminShippedOrders.vue';
import AdminCompletedOrders from './components/AdminCompletedOrders.vue';
import AdminCancelledOrders from './components/AdminCancelledOrders.vue';
import AdminDispatchNotes from './components/AdminDispatchNotes.vue';
import AdminDispatchNoteShow from './components/AdminDispatchNoteShow.vue';
import AdminOrderShow from './components/AdminOrderShow.vue';
import AdminGlobalFilterBar from './components/AdminGlobalFilterBar.vue';
import GrnManager from './components/GrnManager.vue';
import LotManager from './components/LotManager.vue';
import DeliveryFeeManager from './components/DeliveryFeeManager.vue';

const app = createApp({})

app.use(Toast, {
  position: POSITION.TOP_CENTER,
  timeout: 3500,
  closeOnClick: true,
  pauseOnHover: true,
  draggable: true,
  newestOnTop: true,
})

.component('dark-mode-toggle', DarkModeToggle)
.component('roles-and-permission-manager', RolePermissionManager)
.component('user-manager', UserManager)
.component('seller-registration-form', SellerRegistrationForm)
.component('seller-manager', SellerManager)
.component('active-seller-manager', ActiveSellerManager)
.component('seller-profile', SellerProfile)
.component('level-manager', LevelManager)
.component('category-manager', CategoryManager)
.component('product-manager', ProductManager)
.component('supplier-manager', SupplierManager)
.component('attribute-manager', AttributeManager)
.component('product-create-manager', ProductCreateManager)
.component('seller-product-variant-selector', SellerProductVariantSelector)
.component('seller-product-show', SellerProductShow)
.component('seller-cart-nav-link', SellerCartNavLink)
.component('seller-floating-cart', SellerFloatingCart)
.component('curfox-login', CurfoxLogin)
.component('city-sync', CitySync)
.component('state-matcher', StateMatcher)
.component('seller-order-submit', SellerOrderSubmit)
.component('seller-my-orders', SellerMyOrders)
.component('seller-order-show', SellerOrderShow)
.component('admin-draft-orders', AdminDraftOrders)
.component('admin-approved-orders', AdminApprovedOrders)
.component('admin-packed-orders', AdminPackedOrders)
.component('admin-shipped-orders', AdminShippedOrders)
.component('admin-completed-orders', AdminCompletedOrders)
.component('admin-cancelled-orders', AdminCancelledOrders)
.component('admin-dispatch-notes', AdminDispatchNotes)
.component('admin-dispatch-note-show', AdminDispatchNoteShow)
.component('admin-order-show', AdminOrderShow)
.component('admin-global-filter-bar', AdminGlobalFilterBar)
.component('grn-manager', GrnManager)
.component('lot-manager', LotManager)
.component('delivery-fee-manager', DeliveryFeeManager)

.mount('#app')
