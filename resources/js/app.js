import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';

import Toast, { POSITION } from "vue-toastification";

import { createApp } from 'vue'

import DarkModeToggle from './components/DarkModeToggle.vue';
import RolePermissionManager from './components/RolePermissionManager.vue';
import UserManager from './components/UserManager.vue';
import SellerRegistrationForm from './components/SellerRegistrationForm.vue';

const app = createApp({})

.component('dark-mode-toggle', DarkModeToggle)
.component('roles-and-permission-manager', RolePermissionManager)
.component('user-manager', UserManager)
.component('seller-registration-form', SellerRegistrationForm)

.mount('#app')
