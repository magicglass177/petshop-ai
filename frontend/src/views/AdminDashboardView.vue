<script setup>
import { onMounted, ref } from 'vue'

import PublicLayout from '../layouts/PublicLayout.vue'
import api from '../services/api'

const loading = ref(true)
const error = ref('')

const statistics = ref({
  products: 0,
  active_products: 0,
  categories: 0,
  services: 0,
  active_services: 0,
  customers: 0,
})

const loadDashboard = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get(
      '/admin/dashboard'
    )

    statistics.value =
      response.data.data.statistics

  } catch (err) {
    console.error(err)

    error.value =
      'Dashboard admin gagal dimuat.'

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

    <main class="admin-page">

      <!-- LOADING -->

      <div
        v-if="loading"
        class="state"
      >
        <div class="loader"></div>

        <p>
          Memuat dashboard admin...
        </p>
      </div>


      <!-- ERROR -->

      <div
        v-else-if="error"
        class="state"
      >
        <div class="state-icon">
          ⚠️
        </div>

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

        <!-- HEADER -->

        <section class="admin-header">

          <div>

            <span class="admin-badge">
              ⚙️ ADMINISTRATOR
            </span>

            <h1>
              Dashboard Admin
            </h1>

            <p>
              Kelola informasi katalog dan
              data Sistem Informasi PetShop AI.
            </p>

          </div>


          <div class="header-icon">
            🐾
          </div>

        </section>


        <!-- STATISTICS -->

        <section class="statistics-grid">

          <!-- PRODUCTS -->

          <div class="stat-card">

            <div class="stat-icon blue">
              📦
            </div>

            <div class="stat-content">

              <span>
                Total Produk
              </span>

              <strong>
                {{ statistics.products }}
              </strong>

              <small>
                {{ statistics.active_products }}
                produk aktif
              </small>

            </div>

          </div>


          <!-- CATEGORIES -->

          <div class="stat-card">

            <div class="stat-icon orange">
              🗂️
            </div>

            <div class="stat-content">

              <span>
                Kategori Produk
              </span>

              <strong>
                {{ statistics.categories }}
              </strong>

              <small>
                Kategori tersedia
              </small>

            </div>

          </div>


          <!-- SERVICES -->

          <div class="stat-card">

            <div class="stat-icon purple">
              ✂️
            </div>

            <div class="stat-content">

              <span>
                Total Layanan
              </span>

              <strong>
                {{ statistics.services }}
              </strong>

              <small>
                {{ statistics.active_services }}
                layanan aktif
              </small>

            </div>

          </div>


          <!-- CUSTOMER -->

          <div class="stat-card">

            <div class="stat-icon green">
              👥
            </div>

            <div class="stat-content">

              <span>
                Customer
              </span>

              <strong>
                {{ statistics.customers }}
              </strong>

              <small>
                Akun customer
              </small>

            </div>

          </div>

        </section>


        <!-- MANAGEMENT -->

        <section class="management-section">

          <div class="section-heading">

            <div>
              <h2>
                Pengelolaan Sistem
              </h2>

              <p>
                Kelola data katalog dan
                informasi PetShop AI.
              </p>
            </div>

          </div>


          <div class="management-grid">

  <!-- =====================================================
       PRODUCT
  ====================================================== -->

  <div class="management-card">

    <div class="management-icon">
      📦
    </div>

    <h3>
      Kelola Produk
    </h3>

    <p>
      Tambah, edit, nonaktifkan,
      dan hapus informasi produk
      pada katalog.
    </p>

    <div class="management-count">

      <strong>
        {{ statistics.products }}
      </strong>

      <span>
        produk
      </span>

    </div>

    <RouterLink
      to="/admin/products"
      class="manage-link"
    >
      Kelola Produk →
    </RouterLink>

  </div>


  <!-- =====================================================
       CATEGORY
  ====================================================== -->

  <div class="management-card">

    <div class="management-icon">
      🗂️
    </div>

    <h3>
      Kelola Kategori
    </h3>

    <p>
      Kelola kategori untuk
      mengelompokkan produk
      dalam katalog.
    </p>

    <div class="management-count">

      <strong>
        {{ statistics.categories }}
      </strong>

      <span>
        kategori
      </span>

    </div>

    <RouterLink
      to="/admin/categories"
      class="manage-link"
    >
      Kelola Kategori →
    </RouterLink>

  </div>


  <!-- =====================================================
       SERVICES
  ====================================================== -->

  <div class="management-card">

    <div class="management-icon">
      ✂️
    </div>

    <h3>
      Kelola Layanan
    </h3>

    <p>
      Kelola katalog informasi
      layanan perawatan hewan.
    </p>

    <div class="management-count">

      <strong>
        {{ statistics.services }}
      </strong>

      <span>
        layanan
      </span>

    </div>

    <RouterLink
  to="/admin/services"
  class="manage-link"
>
  Kelola Layanan →
</RouterLink>

  </div>


  <!-- =====================================================
       CUSTOMER
  ====================================================== -->

  <div class="management-card">

    <div class="management-icon">
      👥
    </div>

    <h3>
      Data Customer
    </h3>

    <p>
      Lihat informasi customer
      yang telah membuat akun
      pada sistem.
    </p>

    <div class="management-count">

      <strong>
        {{ statistics.customers }}
      </strong>

      <span>
        customer
      </span>

    </div>

    <RouterLink
  to="/admin/customers"
  class="manage-link"
>
  Lihat Customer →
</RouterLink>

  </div>

</div>


        </section>


        <!-- AI GATEWAY -->

        <section class="ai-section">

          <div class="ai-icon">
            🤖
          </div>


          <div class="ai-information">

            <span>
              FITUR UTAMA SISTEM
            </span>

            <h2>
              AI Gateway & AI Pet Assistant
            </h2>

            <p>
              Sistem akan menghubungkan Laravel
              dengan layanan AI melalui AI Gateway.
              AI akan menerima konteks dari katalog
              layanan dan memberikan rekomendasi
              berdasarkan kebutuhan hewan pengguna.
            </p>


            <div class="ai-flow">

              <div>
                Vue
              </div>

              <b>→</b>

              <div>
                Laravel API
              </div>

              <b>→</b>

              <div class="gateway">
                AI Gateway
              </div>

              <b>→</b>

              <div>
                AI Provider
              </div>

            </div>

          </div>


          <div class="development-status">

  <span>
    STATUS AI
  </span>

  <strong>
    ✓ Terintegrasi
  </strong>

  <RouterLink
    to="/admin/ai-monitoring"
    class="monitoring-link"
  >
    Monitoring AI →
  </RouterLink>

</div>

        </section>


        <!-- INFORMATION -->

        <section class="scope-card">

          <div class="scope-icon">
            ℹ️
          </div>

          <div>

            <h3>
              Fokus Sistem Informasi
            </h3>

            <p>
              PetShop AI berfokus pada penyediaan
              katalog informasi produk dan layanan
              serta pemanfaatan AI Gateway untuk
              membantu pengguna mendapatkan
              rekomendasi yang relevan.
            </p>

          </div>

        </section>

      </template>

    </main>

  </PublicLayout>
</template>

<style scoped>
.admin-page {
  min-height: 750px;

  padding: 38px 24px 90px;

  background: #f8fafc;
}


/* HEADER */

.admin-header {
  max-width: 1200px;

  margin: auto;

  padding: 35px;

  display: flex;

  align-items: center;
  justify-content: space-between;

  border-radius: 18px;

  background:
    linear-gradient(
      135deg,
      #111827,
      #1e293b
    );

  color: white;
}

.admin-badge {
  display: inline-block;

  padding: 6px 11px;

  border-radius: 20px;

  background:
    rgba(255, 255, 255, 0.1);

  color: #bfdbfe;

  font-size: 9px;
  font-weight: 800;

  letter-spacing: 1px;
}

.admin-header h1 {
  margin: 13px 0 7px;

  font-size: 30px;
}

.admin-header p {
  margin: 0;

  color: #cbd5e1;

  font-size: 12px;
}

.header-icon {
  font-size: 65px;
}


/* STATISTICS */

.statistics-grid {
  max-width: 1200px;

  margin: 22px auto;

  display: grid;

  grid-template-columns:
    repeat(4, minmax(0, 1fr));

  gap: 15px;
}

.stat-card {
  padding: 20px;

  display: flex;

  align-items: center;

  gap: 14px;

  border: 1px solid #e5e7eb;

  border-radius: 14px;

  background: white;
}

.stat-icon {
  width: 50px;
  height: 50px;

  display: flex;

  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border-radius: 12px;

  font-size: 22px;
}

.blue {
  background: #eff6ff;
}

.orange {
  background: #fff7ed;
}

.purple {
  background: #f5f3ff;
}

.green {
  background: #ecfdf5;
}

.stat-content span {
  display: block;

  color: #6b7280;

  font-size: 10px;
}

.stat-content strong {
  display: block;

  margin: 3px 0;

  color: #111827;

  font-size: 24px;
}

.stat-content small {
  color: #9ca3af;

  font-size: 9px;
}


/* MANAGEMENT */

.management-section {
  max-width: 1200px;

  margin: 35px auto;
}

.section-heading {
  margin-bottom: 18px;
}

.section-heading h2 {
  margin: 0 0 5px;

  color: #111827;

  font-size: 20px;
}

.section-heading p {
  margin: 0;

  color: #6b7280;

  font-size: 11px;
}

.management-grid {
  display: grid;

  grid-template-columns:
    repeat(4, minmax(0, 1fr));

  gap: 16px;
}

.management-card {
  padding: 23px;

  border: 1px solid #e5e7eb;

  border-radius: 15px;

  background: white;
}

.management-icon {
  width: 45px;
  height: 45px;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 11px;

  background: #f8fafc;

  font-size: 21px;
}

.management-card h3 {
  margin: 16px 0 7px;

  color: #111827;

  font-size: 14px;
}

.management-card p {
  min-height: 55px;

  margin: 0;

  color: #6b7280;

  font-size: 10px;

  line-height: 1.7;
}

.management-count {
  margin: 18px 0;

  padding: 12px;

  border-radius: 9px;

  background: #f8fafc;
}

.management-count strong {
  color: #111827;

  font-size: 20px;
}

.management-count span {
  margin-left: 5px;

  color: #9ca3af;

  font-size: 9px;
}

.manage-button {
  width: 100%;

  padding: 10px;

  border: none;

  border-radius: 8px;

  background: #e5e7eb;

  color: #9ca3af;

  font-size: 10px;
  font-weight: 600;

  cursor: not-allowed;
}

.next-stage {
  display: block;

  margin-top: 8px;

  color: #9ca3af;

  text-align: center;

  font-size: 8px;
}


/* AI */

.ai-section {
  max-width: 1200px;

  margin: 35px auto 20px;

  padding: 28px;

  display: grid;

  grid-template-columns:
    auto 1fr auto;

  align-items: center;

  gap: 22px;

  border-radius: 17px;

  background:
    linear-gradient(
      135deg,
      #312e81,
      #1e3a8a
    );

  color: white;
}

.ai-icon {
  width: 70px;
  height: 70px;

  display: flex;

  align-items: center;
  justify-content: center;

  border-radius: 16px;

  background:
    rgba(255, 255, 255, 0.1);

  font-size: 34px;
}

.ai-information > span {
  color: #bfdbfe;

  font-size: 8px;
  font-weight: 800;

  letter-spacing: 1px;
}

.ai-information h2 {
  margin: 6px 0 8px;

  font-size: 19px;
}

.ai-information p {
  max-width: 700px;

  margin: 0;

  color: #cbd5e1;

  font-size: 10px;

  line-height: 1.7;
}

.ai-flow {
  margin-top: 15px;

  display: flex;

  align-items: center;

  flex-wrap: wrap;

  gap: 7px;
}

.ai-flow div {
  padding: 6px 9px;

  border-radius: 6px;

  background:
    rgba(255, 255, 255, 0.1);

  font-size: 8px;
}

.ai-flow .gateway {
  background: white;

  color: #312e81;

  font-weight: 700;
}

.ai-flow b {
  color: #93c5fd;
}

.development-status {
  padding: 12px 15px;

  border-radius: 10px;

  background:
    rgba(255, 255, 255, 0.1);

  text-align: center;
}

.development-status span,
.development-status strong {
  display: block;
}

.development-status span {
  color: #bfdbfe;

  font-size: 8px;
}

.development-status strong {
  margin-top: 4px;

  font-size: 11px;
}


/* SCOPE */

.scope-card {
  max-width: 1200px;

  margin: auto;

  padding: 20px;

  display: flex;

  align-items: center;

  gap: 15px;

  border: 1px solid #dbeafe;

  border-radius: 13px;

  background: #eff6ff;
}

.scope-icon {
  font-size: 25px;
}

.scope-card h3 {
  margin: 0 0 4px;

  color: #1e3a8a;

  font-size: 12px;
}

.scope-card p {
  margin: 0;

  color: #64748b;

  font-size: 9px;

  line-height: 1.6;
}


/* STATE */

.state {
  min-height: 500px;

  display: flex;

  flex-direction: column;

  align-items: center;
  justify-content: center;

  color: #6b7280;

  text-align: center;
}

.loader {
  width: 38px;
  height: 38px;

  border: 4px solid #dbeafe;
  border-top-color: #2563eb;

  border-radius: 50%;

  animation: spin 0.8s linear infinite;
}

.state-icon {
  font-size: 40px;
}

.state button {
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

@media (max-width: 950px) {
  .statistics-grid,
  .management-grid {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .ai-section {
    grid-template-columns: auto 1fr;
  }

  .development-status {
    display: none;
  }
}

@media (max-width: 600px) {
  .statistics-grid,
  .management-grid {
    grid-template-columns: 1fr;
  }

  .header-icon {
    display: none;
  }

  .ai-section {
    grid-template-columns: 1fr;
  }

  .ai-icon {
    width: 55px;
    height: 55px;
  }
}

.manage-link {
  display: block;

  width: 100%;

  box-sizing: border-box;

  padding: 10px;

  border-radius: 8px;

  background: #2563eb;

  color: white;

  text-align: center;

  text-decoration: none;

  font-size: 10px;

  font-weight: 700;

  transition: 0.2s;
}

.manage-link:hover {
  background: #1d4ed8;
}

.monitoring-link {
  display: block;
  margin-top: 10px;
  padding: 8px 12px;

  border-radius: 7px;

  background: white;
  color: #312e81;

  text-align: center;
  text-decoration: none;

  font-size: 10px;
  font-weight: 700;

  transition: 0.2s;
}

.monitoring-link:hover {
  background: #eff6ff;
}

/* =========================================================
   FINAL UI - READABILITY
========================================================= */

.admin-badge {
  font-size: 11px;
}

.admin-header p {
  font-size: 14px;
  line-height: 1.6;
}

.stat-content span {
  font-size: 13px;
}

.stat-content strong {
  font-size: 28px;
}

.stat-content small {
  font-size: 12px;
}

.section-heading h2 {
  font-size: 22px;
}

.section-heading p {
  font-size: 13px;
}

.management-card h3 {
  font-size: 16px;
}

.management-card p {
  font-size: 13px;
  line-height: 1.6;
}

.management-count span {
  font-size: 12px;
}

.manage-link {
  font-size: 12px;
}

.ai-information > span {
  font-size: 10px;
}

.ai-information h2 {
  font-size: 21px;
}

.ai-information p {
  font-size: 13px;
  line-height: 1.7;
}

.ai-flow div {
  font-size: 11px;
}

.development-status span {
  font-size: 10px;
}

.development-status strong {
  font-size: 13px;
}

.monitoring-link {
  font-size: 11px;
}

.scope-card h3 {
  font-size: 14px;
}

.scope-card p {
  font-size: 12px;
  line-height: 1.7;
}
</style>
