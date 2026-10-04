<script setup>
import { onMounted, ref } from 'vue'

import PublicLayout from '../layouts/PublicLayout.vue'
import api from '../services/api'
import { authState } from '../services/auth'

const loading = ref(true)
const error = ref('')

const dashboard = ref({
  customer: {
    name: '',
    email: '',
    phone: '',
  },

  statistics: {
    pets: 0,
    products: 0,
    services: 0,
  },
})

const loadDashboard = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get(
      '/customer/dashboard'
    )

    dashboard.value = response.data.data

  } catch (err) {
    console.error(err)

    error.value =
      'Dashboard gagal dimuat. Silakan coba kembali.'

  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboard()
})
</script>

<template>
  <PublicLayout>

    <main class="dashboard-page">

      <!-- LOADING -->

      <div
        v-if="loading"
        class="loading-state"
      >
        <div class="loader"></div>

        <p>
          Memuat dashboard...
        </p>
      </div>


      <!-- ERROR -->

      <div
        v-else-if="error"
        class="error-state"
      >
        <span>⚠️</span>

        <h2>
          Dashboard tidak dapat dimuat
        </h2>

        <p>
          {{ error }}
        </p>

        <button @click="loadDashboard">
          Coba Lagi
        </button>
      </div>


      <!-- DASHBOARD -->

      <template v-else>

        <!-- HERO -->

        <section class="welcome-section">

          <div>

            <span class="welcome-label">
              🐾 Dashboard Customer
            </span>

            <h1>
              Halo,
              {{ dashboard.customer.name }}!
            </h1>

            <p>
              Kelola informasi hewan peliharaan
              Anda dan gunakan AI Pet Assistant
              untuk mendapatkan rekomendasi
              berdasarkan katalog layanan kami.
            </p>

          </div>


          <div class="welcome-icon">
            🐶
          </div>

        </section>


        <!-- STATISTICS -->

        <section class="statistics">

          <RouterLink
            to="/my-pets"
            class="stat-card"
          >

            <div class="stat-icon blue">
              🐾
            </div>

            <div>
              <span>Hewan Saya</span>

              <strong>
                {{ dashboard.statistics.pets }}
              </strong>

              <small>
                Data hewan tersimpan
              </small>
            </div>

          </RouterLink>


          <RouterLink
            to="/products"
            class="stat-card"
          >

            <div class="stat-icon green">
              📦
            </div>

            <div>
              <span>Katalog Produk</span>

              <strong>
                {{ dashboard.statistics.products }}
              </strong>

              <small>
                Produk tersedia
              </small>
            </div>

          </RouterLink>


          <RouterLink
            to="/services"
            class="stat-card"
          >

            <div class="stat-icon purple">
              ✂️
            </div>

            <div>
              <span>Katalog Layanan</span>

              <strong>
                {{ dashboard.statistics.services }}
              </strong>

              <small>
                Layanan tersedia
              </small>
            </div>

          </RouterLink>

        </section>


        <!-- MAIN GRID -->

        <section class="dashboard-grid">

          <!-- AI -->

          <div class="ai-card">

            <div class="ai-content">

              <span class="ai-badge">
                ✨ FITUR UTAMA
              </span>

              <h2>
                AI Pet Assistant
              </h2>

              <p>
                Ceritakan kondisi atau kebutuhan
                hewan peliharaan Anda. AI akan
                membantu memberikan rekomendasi
                berdasarkan informasi yang Anda
                berikan dan katalog layanan
                PetShop AI.
              </p>


              <div class="ai-features">

                <span>
                  ✓ Analisis kebutuhan
                </span>

                <span>
                  ✓ Rekomendasi layanan
                </span>

                <span>
                  ✓ Terhubung melalui AI Gateway
                </span>

              </div>


              <RouterLink
                to="/ai-assistant"
                class="ai-button"
              >
                ✨ Gunakan AI Pet Assistant
              </RouterLink>

            </div>


            <div class="ai-decoration">
              🤖
            </div>

          </div>


          <!-- ACCOUNT -->

          <div class="account-card">

            <div class="card-heading">

              <div>
                <span>👤</span>

                <div>
                  <h3>Akun Saya</h3>

                  <p>
                    Informasi akun customer
                  </p>
                </div>
              </div>

            </div>


            <div class="account-info">

              <div>
                <span>Nama</span>

                <strong>
                  {{ dashboard.customer.name }}
                </strong>
              </div>


              <div>
                <span>Email</span>

                <strong>
                  {{ dashboard.customer.email }}
                </strong>
              </div>


              <div>
                <span>Telepon</span>

                <strong>
                  {{
                    dashboard.customer.phone
                      || 'Belum diisi'
                  }}
                </strong>
              </div>

            </div>


            <RouterLink
              to="/profile"
              class="outline-button"
            >
              Kelola Profil →
            </RouterLink>

          </div>

        </section>


        <!-- QUICK ACCESS -->

        <section class="quick-section">

          <div class="section-title">

            <div>
              <h2>Akses Cepat</h2>

              <p>
                Fitur yang dapat Anda gunakan.
              </p>
            </div>

          </div>


          <div class="quick-grid">

            <RouterLink
              to="/my-pets"
              class="quick-card"
            >
              <span class="quick-icon">
                🐱
              </span>

              <div>
                <h3>Hewan Saya</h3>

                <p>
                  Tambah dan kelola informasi
                  hewan peliharaan.
                </p>
              </div>

              <strong>→</strong>
            </RouterLink>


            <RouterLink
              to="/profile"
              class="quick-card"
            >
              <span class="quick-icon">
                👤
              </span>

              <div>
                <h3>Profil Saya</h3>

                <p>
                  Perbarui informasi akun dan
                  data pribadi.
                </p>
              </div>

              <strong>→</strong>
            </RouterLink>


            <RouterLink
              to="/products"
              class="quick-card"
            >
              <span class="quick-icon">
                📦
              </span>

              <div>
                <h3>Katalog Produk</h3>

                <p>
                  Lihat informasi produk yang
                  tersedia.
                </p>
              </div>

              <strong>→</strong>
            </RouterLink>


            <RouterLink
              to="/services"
              class="quick-card"
            >
              <span class="quick-icon">
                ✂️
              </span>

              <div>
                <h3>Katalog Layanan</h3>

                <p>
                  Lihat informasi layanan
                  perawatan hewan.
                </p>
              </div>

              <strong>→</strong>
            </RouterLink>

          </div>

        </section>


        <!-- AI HISTORY -->

<section class="history-card">

  <div class="history-icon">
    🧠
  </div>

  <div class="history-content">

    <span class="coming-soon">
      ✨ FITUR AI CUSTOMER
    </span>

    <h3>
      Riwayat Analisis AI
    </h3>

    <p>
      Lihat kembali hasil analisis
      AI Pet Assistant yang pernah
      Anda lakukan, termasuk rekomendasi
      layanan dan informasi hewan yang digunakan.
    </p>

    <RouterLink
      to="/customer/ai-history"
      class="history-button"
    >
      Lihat Riwayat AI →
    </RouterLink>

  </div>

</section>

      </template>

    </main>

  </PublicLayout>
</template>

<style scoped>
.dashboard-page {
  min-height: 700px;

  padding: 38px 24px 90px;

  background: #f8fafc;
}


/* WELCOME */

.welcome-section {
  max-width: 1200px;

  margin: auto;

  padding: 35px;

  display: flex;

  align-items: center;
  justify-content: space-between;

  gap: 30px;

  border-radius: 20px;

  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #ffffff,
      #f5f3ff
    );

  border: 1px solid #dbeafe;
}

.welcome-label {
  display: inline-block;

  margin-bottom: 12px;

  padding: 6px 12px;

  border-radius: 20px;

  background: white;

  color: #2563eb;

  font-size: 11px;
  font-weight: 700;
}

.welcome-section h1 {
  margin: 0 0 10px;

  color: #111827;

  font-size: 32px;
}

.welcome-section p {
  max-width: 650px;

  margin: 0;

  color: #6b7280;

  font-size: 13px;

  line-height: 1.8;
}

.welcome-icon {
  font-size: 75px;
}


/* STATISTICS */

.statistics {
  max-width: 1200px;

  margin: 25px auto;

  display: grid;

  grid-template-columns:
    repeat(3, minmax(0, 1fr));

  gap: 18px;
}

.stat-card {
  padding: 22px;

  display: flex;

  align-items: center;

  gap: 16px;

  border: 1px solid #e5e7eb;

  border-radius: 14px;

  background: white;

  text-decoration: none;

  transition:
    transform 0.2s,
    box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);

  box-shadow:
    0 12px 30px rgba(0, 0, 0, 0.06);
}

.stat-icon {
  width: 52px;
  height: 52px;

  display: flex;

  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border-radius: 13px;

  font-size: 23px;
}

.blue {
  background: #eff6ff;
}

.green {
  background: #ecfdf5;
}

.purple {
  background: #f5f3ff;
}

.stat-card span {
  display: block;

  color: #6b7280;

  font-size: 11px;
}

.stat-card strong {
  display: block;

  margin: 3px 0;

  color: #111827;

  font-size: 25px;
}

.stat-card small {
  color: #9ca3af;

  font-size: 9px;
}


/* MAIN */

.dashboard-grid {
  max-width: 1200px;

  margin: 0 auto 30px;

  display: grid;

  grid-template-columns: 2fr 1fr;

  gap: 20px;
}


/* AI */

.ai-card {
  min-height: 300px;

  padding: 32px;

  display: flex;

  align-items: center;
  justify-content: space-between;

  overflow: hidden;

  border-radius: 18px;

  background:
    linear-gradient(
      135deg,
      #172554,
      #312e81
    );

  color: white;
}

.ai-content {
  position: relative;

  z-index: 2;

  max-width: 550px;
}

.ai-badge {
  display: inline-block;

  padding: 6px 10px;

  border-radius: 20px;

  background: rgba(255, 255, 255, 0.12);

  color: #bfdbfe;

  font-size: 9px;
  font-weight: 700;
}

.ai-card h2 {
  margin: 15px 0 10px;

  font-size: 27px;
}

.ai-card p {
  color: #cbd5e1;

  font-size: 12px;

  line-height: 1.8;
}

.ai-features {
  margin: 18px 0;

  display: flex;

  flex-wrap: wrap;

  gap: 10px;
}

.ai-features span {
  color: #bfdbfe;

  font-size: 10px;
}

.ai-button {
  display: inline-block;

  margin-top: 5px;

  padding: 11px 16px;

  border-radius: 8px;

  background: white;

  color: #1e3a8a;

  text-decoration: none;

  font-size: 11px;
  font-weight: 700;
}

.ai-decoration {
  padding-left: 20px;

  font-size: 80px;

  opacity: 0.8;
}


/* ACCOUNT */

.account-card {
  padding: 27px;

  border: 1px solid #e5e7eb;

  border-radius: 18px;

  background: white;
}

.card-heading > div {
  display: flex;

  align-items: center;

  gap: 10px;
}

.card-heading span {
  font-size: 25px;
}

.card-heading h3 {
  margin: 0;

  color: #111827;

  font-size: 16px;
}

.card-heading p {
  margin: 3px 0 0;

  color: #9ca3af;

  font-size: 10px;
}

.account-info {
  margin: 22px 0;
}

.account-info div {
  padding: 12px 0;

  border-bottom: 1px solid #f3f4f6;
}

.account-info span,
.account-info strong {
  display: block;
}

.account-info span {
  margin-bottom: 4px;

  color: #9ca3af;

  font-size: 9px;

  text-transform: uppercase;
}

.account-info strong {
  overflow-wrap: anywhere;

  color: #374151;

  font-size: 11px;
}

.outline-button {
  display: block;

  padding: 10px;

  border: 1px solid #bfdbfe;

  border-radius: 8px;

  color: #2563eb;

  text-align: center;

  text-decoration: none;

  font-size: 11px;
  font-weight: 600;
}


/* QUICK */

.quick-section {
  max-width: 1200px;

  margin: 35px auto;
}

.section-title {
  margin-bottom: 18px;
}

.section-title h2 {
  margin: 0 0 4px;

  color: #111827;

  font-size: 19px;
}

.section-title p {
  margin: 0;

  color: #9ca3af;

  font-size: 11px;
}

.quick-grid {
  display: grid;

  grid-template-columns:
    repeat(4, minmax(0, 1fr));

  gap: 15px;
}

.quick-card {
  padding: 20px;

  display: flex;

  align-items: center;

  gap: 12px;

  border: 1px solid #e5e7eb;

  border-radius: 13px;

  background: white;

  color: inherit;

  text-decoration: none;
}

.quick-card:hover {
  border-color: #bfdbfe;

  box-shadow:
    0 10px 25px rgba(0, 0, 0, 0.05);
}

.quick-icon {
  font-size: 28px;
}

.quick-card div {
  flex: 1;
}

.quick-card h3 {
  margin: 0 0 4px;

  color: #111827;

  font-size: 12px;
}

.quick-card p {
  margin: 0;

  color: #9ca3af;

  font-size: 9px;

  line-height: 1.5;
}

.quick-card > strong {
  color: #2563eb;
}


/* HISTORY */

.history-card {
  max-width: 1200px;

  margin: auto;

  padding: 25px;

  display: flex;

  align-items: center;

  gap: 18px;

  border: 1px dashed #c7d2fe;

  border-radius: 14px;

  background: #fafaff;
}

.history-icon {
  width: 55px;
  height: 55px;

  display: flex;

  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border-radius: 13px;

  background: #eef2ff;

  font-size: 25px;
}

.coming-soon {
  color: #6366f1;

  font-size: 8px;
  font-weight: 800;

  letter-spacing: 1px;
}

.history-card h3 {
  margin: 5px 0;

  color: #111827;

  font-size: 14px;
}

.history-card p {
  max-width: 700px;

  margin: 0;

  color: #6b7280;

  font-size: 10px;

  line-height: 1.6;
}


/* STATES */

.loading-state,
.error-state {
  min-height: 500px;

  display: flex;
  flex-direction: column;

  align-items: center;
  justify-content: center;

  text-align: center;

  color: #6b7280;
}

.loader {
  width: 38px;
  height: 38px;

  border: 4px solid #dbeafe;
  border-top-color: #2563eb;

  border-radius: 50%;

  animation: spin 0.8s linear infinite;
}

.error-state span {
  font-size: 40px;
}

.error-state h2 {
  color: #111827;
}

.error-state button {
  padding: 10px 16px;

  border: none;

  border-radius: 8px;

  background: #2563eb;

  color: white;

  cursor: pointer;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}


/* RESPONSIVE */

@media (max-width: 900px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .quick-grid {
    grid-template-columns: 1fr 1fr;
  }

  .ai-decoration {
    display: none;
  }
}

@media (max-width: 650px) {
  .statistics,
  .quick-grid {
    grid-template-columns: 1fr;
  }

  .welcome-icon {
    display: none;
  }

  .welcome-section {
    padding: 25px;
  }
}

/* =========================================================
   FINAL UI - AI HISTORY
========================================================= */

.history-content {
  flex: 1;
}

.history-button {
  display: inline-block;

  margin-top: 14px;
  padding: 10px 15px;

  border-radius: 8px;

  background: #4f46e5;
  color: white;

  text-decoration: none;

  font-size: 12px;
  font-weight: 700;

  transition: 0.2s;
}

.history-button:hover {
  background: #4338ca;
}

/* =========================================================
   FINAL UI - READABILITY
========================================================= */

.stat-card span {
  font-size: 13px;
}

.stat-card strong {
  font-size: 28px;
}

.stat-card small {
  font-size: 12px;
}

.ai-badge {
  font-size: 11px;
}

.ai-card p {
  font-size: 13px;
}

.ai-features span {
  font-size: 12px;
}

.ai-button {
  font-size: 12px;
}

.card-heading p {
  font-size: 12px;
}

.account-info span {
  font-size: 11px;
}

.account-info strong {
  font-size: 13px;
}

.outline-button {
  font-size: 12px;
}

.section-title h2 {
  font-size: 21px;
}

.section-title p {
  font-size: 13px;
}

.quick-card h3 {
  font-size: 14px;
}

.quick-card p {
  font-size: 12px;
  line-height: 1.6;
}

.coming-soon {
  font-size: 10px;
}

.history-card h3 {
  font-size: 16px;
}

.history-card p {
  font-size: 13px;
  line-height: 1.7;
}
</style>