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

.mount('#app')
