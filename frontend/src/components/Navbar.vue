<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import { authState, clearAuth } from '../services/auth'

const router = useRouter()
const route = useRoute()
const menuOpen = ref(false)
const menuToggle = ref(null)
const loggingOut = ref(false)
let mobileQuery

const closeMenu = () => { menuOpen.value = false }
const toggleMenu = () => { menuOpen.value = !menuOpen.value }
const onBreakpointChange = () => { closeMenu() }
const onEscape = (event) => {
  if (event.key === 'Escape' && menuOpen.value) {
    closeMenu()
    menuToggle.value?.focus()
  }
}

watch(() => route.fullPath, closeMenu)
watch(() => authState.user?.id, closeMenu)

onMounted(() => {
  mobileQuery = window.matchMedia('(max-width: 1280px)')
  mobileQuery.addEventListener('change', onBreakpointChange)
  document.addEventListener('keydown', onEscape)
})
onUnmounted(() => {
  mobileQuery?.removeEventListener('change', onBreakpointChange)
  document.removeEventListener('keydown', onEscape)
})

const logout = async () => {
  if (loggingOut.value) return
  loggingOut.value = true
  try {
    await api.post('/logout')
  } catch (error) {
    console.error(error)
  } finally {
    clearAuth()
    closeMenu()
    loggingOut.value = false
    router.push('/')
  }
}
</script>

<template>
  <header class="navbar">
    <div class="navbar-container">
      <RouterLink to="/" class="brand" @click="closeMenu">
        <div class="brand-icon" aria-hidden="true">🐾</div>
        <div class="brand-text">
          <strong>PetShop AI</strong>
          <small>Smart Care for Your Pet</small>
        </div>
      </RouterLink>

      <button
        ref="menuToggle"
        type="button"
        class="menu-toggle"
        :aria-expanded="menuOpen"
        aria-controls="petshop-navigation"
        :aria-label="menuOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'"
        @click="toggleMenu"
      >
        <svg v-if="!menuOpen" width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        <span>{{ menuOpen ? 'Tutup' : 'Menu' }}</span>
      </button>

      <div id="petshop-navigation" class="navigation-panel" :class="{ 'is-open': menuOpen }">
        <nav class="nav-menu" aria-label="Navigasi utama">
          <RouterLink to="/" @click="closeMenu">Beranda</RouterLink>
          <RouterLink to="/products" @click="closeMenu">Produk</RouterLink>
          <RouterLink to="/services" @click="closeMenu">Layanan</RouterLink>
          <RouterLink to="/ai-assistant" class="ai-link" @click="closeMenu">✨ AI Pet Assistant</RouterLink>
        </nav>

        <!-- GUEST START -->
        <div v-if="!authState.user" class="nav-actions">
          <RouterLink to="/login" class="login-button" @click="closeMenu">Masuk</RouterLink>
          <RouterLink to="/register" class="register-button" @click="closeMenu">Daftar</RouterLink>
        </div>
        <!-- GUEST END -->

        <!-- USER START -->
        <div v-else class="user-area">
          <!-- CUSTOMER START -->
          <nav v-if="authState.user?.role === 'customer'" class="role-links customer-links" aria-label="Menu customer">
            <RouterLink to="/customer/dashboard" @click="closeMenu">🏠 Dashboard</RouterLink>
            <RouterLink to="/profile" @click="closeMenu">👤 Profil</RouterLink>
            <RouterLink to="/my-pets" @click="closeMenu">🐾 Hewan</RouterLink>
            <RouterLink to="/customer/ai-history" @click="closeMenu">✨ Riwayat AI</RouterLink>
          </nav>
          <!-- CUSTOMER END -->

          <!-- ADMIN START -->
          <nav v-if="authState.user?.role === 'admin'" class="role-links admin-links" aria-label="Menu admin">
            <RouterLink to="/admin/dashboard" class="admin-dashboard-link" @click="closeMenu">⚙️ Dashboard Admin</RouterLink>
            <RouterLink to="/admin/ai-monitoring" class="admin-dashboard-link" @click="closeMenu">🤖 Monitoring AI</RouterLink>
          </nav>
          <!-- ADMIN END -->

          <div class="account-actions">
            <div class="user-info">
              <div class="avatar" aria-hidden="true">{{ authState.user.name?.charAt(0).toUpperCase() }}</div>
              <div class="user-text">
                <strong>{{ authState.user.name }}</strong>
                <small>{{ authState.user.role === 'admin' ? 'Administrator' : 'Customer' }}</small>
              </div>
            </div>
            <button type="button" class="logout-button" :disabled="loggingOut" @click="logout">
              {{ loggingOut ? 'Keluar...' : 'Keluar' }}
            </button>
          </div>
        </div>
        <!-- USER END -->
      </div>
    </div>
  </header>
</template>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  width: 100%;
  background: rgba(255, 255, 255, 0.97);
  border-bottom: 1px solid #e5e7eb;
  backdrop-filter: blur(10px);
}
.navbar *, .navbar *::before, .navbar *::after { box-sizing: border-box; }
.navbar-container {
  max-width: 1380px;
  min-height: 80px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}
.brand { display: flex; align-items: center; gap: 10px; flex-shrink: 0; color: #111827; text-decoration: none; }
.brand-icon { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 11px; background: #eff6ff; font-size: 22px; }
.brand strong, .brand small { display: block; }
.brand strong { font-size: 18px; }
.brand small { margin-top: 2px; color: #6b7280; font-size: 12px; }
.navigation-panel { display: flex; align-items: center; justify-content: space-between; flex: 1; min-width: 0; gap: 16px; }
.nav-menu { display: flex; align-items: center; justify-content: center; gap: 14px; flex: 1; }
.navbar a { text-decoration: none; }
.nav-menu a { display: inline-flex; align-items: center; min-height: 44px; color: #4b5563; font-size: 14px; font-weight: 500; white-space: nowrap; }
.nav-menu a:hover, .nav-menu a.router-link-exact-active { color: #2563eb; }
.nav-menu .ai-link { padding: 10px 12px; border-radius: 8px; background: #eff6ff; }
.nav-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.login-button, .register-button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px 15px; border-radius: 8px; font-size: 14px; font-weight: 600; }
.login-button { color: #2563eb; }
.register-button { background: #2563eb; color: #fff; }
.register-button:hover { background: #1d4ed8; }
.user-area, .account-actions { display: flex; align-items: center; gap: 10px; min-width: 0; }
.role-links { display: flex; align-items: center; gap: 6px; }
.role-links a { display: inline-flex; align-items: center; min-height: 44px; padding: 9px 8px; border-radius: 7px; color: #4b5563; font-size: 12px; font-weight: 600; white-space: nowrap; }
.customer-links a:hover, .customer-links a.router-link-exact-active { background: #eff6ff; color: #2563eb; }
.admin-links .admin-dashboard-link { background: #111827; color: #fff; }
.admin-links .admin-dashboard-link:hover { background: #1f2937; }
.admin-links .admin-dashboard-link.router-link-exact-active { background: #2563eb; }
.user-info { display: flex; align-items: center; gap: 9px; padding-left: 10px; border-left: 1px solid #e5e7eb; min-width: 0; }
.avatar { width: 36px; height: 36px; display: grid; place-items: center; flex-shrink: 0; border-radius: 50%; background: #2563eb; color: #fff; font-size: 15px; font-weight: 700; }
.user-text { min-width: 0; }
.user-text strong, .user-text small { display: block; }
.user-text strong { max-width: 100px; overflow: hidden; color: #111827; font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
.user-text small { margin-top: 2px; color: #6b7280; font-size: 12px; }
.logout-button { min-height: 44px; padding: 9px 12px; flex-shrink: 0; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; color: #4b5563; font: inherit; font-size: 12px; cursor: pointer; }
.logout-button:hover { border-color: #fecaca; background: #fef2f2; color: #dc2626; }
.logout-button:disabled { opacity: 0.6; cursor: wait; }
.menu-toggle { display: none; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 10px 12px; border: 1px solid #dbe3ef; border-radius: 10px; background: #eff6ff; color: #1d4ed8; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; }
.navbar a:focus-visible, .navbar button:focus-visible { outline: 3px solid #2563eb; outline-offset: 3px; }

@media (max-width: 1280px) {
  .navbar-container { min-height: 72px; flex-wrap: wrap; gap: 0; }
  .brand { margin: 14px 0; }
  .menu-toggle { display: inline-flex; }
  .navigation-panel { display: none; flex: 0 0 100%; width: 100%; min-width: 0; padding: 16px 0 20px; border-top: 1px solid #e5e7eb; max-height: calc(100vh - 76px); max-height: calc(100dvh - 76px); overflow-y: auto; overscroll-behavior: contain; }
  .navigation-panel.is-open { display: flex; flex-direction: column; align-items: stretch; gap: 16px; }
  .nav-menu { flex: none; flex-direction: column; align-items: stretch; gap: 4px; }
  .nav-menu a { width: 100%; padding: 12px 14px; border-radius: 8px; white-space: normal; }
  .nav-menu a:hover, .nav-menu a.router-link-exact-active { background: #eff6ff; }
  .nav-actions { width: 100%; padding-top: 16px; border-top: 1px solid #e5e7eb; }
  .nav-actions a { flex: 1; }
  .user-area { width: 100%; flex-direction: column; align-items: stretch; gap: 16px; }
  .role-links { flex-direction: column; align-items: stretch; gap: 6px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
  .role-links a { width: 100%; padding: 12px 14px; font-size: 14px; white-space: normal; }
  .account-actions { width: 100%; justify-content: space-between; padding-top: 16px; border-top: 1px solid #e5e7eb; }
  .user-info { flex: 1; padding-left: 0; border-left: 0; }
  .user-text strong { max-width: none; white-space: normal; overflow-wrap: anywhere; }
  .logout-button { font-size: 14px; }
}
@media (max-width: 480px) {
  .navbar-container { padding: 0 16px; }
  .brand-icon { width: 38px; height: 38px; font-size: 20px; }
  .brand strong { font-size: 16px; }
  .brand small { font-size: 10px; }
}
@media (max-width: 350px) {
  .brand small { display: none; }
  .brand { gap: 8px; }
  .menu-toggle { padding: 10px; }
}
</style>
