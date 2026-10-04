<script setup>
import { onMounted, ref } from 'vue'
import PublicLayout from '../layouts/PublicLayout.vue'
import api from '../services/api'

const services = ref([])

const search = ref('')
const animalType = ref('')

const loading = ref(false)
const error = ref('')

const loadServices = async () => {
  loading.value = true
  error.value = ''

  try {
    const params = {}

    if (search.value.trim()) {
      params.search = search.value.trim()
    }

    if (animalType.value) {
      params.animal_type = animalType.value
    }

    const response = await api.get('/services', {
      params,
    })

    services.value = response.data.data
  } catch (err) {
    console.error(err)

    error.value =
      'Gagal mengambil data layanan. Pastikan backend Laravel sedang berjalan.'
  } finally {
    loading.value = false
  }
}

const searchServices = () => {
  loadServices()
}

const resetFilter = () => {
  search.value = ''
  animalType.value = ''
  loadServices()
}

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value))
}

const formatDuration = (minutes) => {
  if (!minutes) {
    return 'Durasi menyesuaikan'
  }

  if (minutes < 60) {
    return `${minutes} menit`
  }

  const hours = Math.floor(minutes / 60)
  const remainingMinutes = minutes % 60

  if (remainingMinutes === 0) {
    return `${hours} jam`
  }

  return `${hours} jam ${remainingMinutes} menit`
}

const getAnimalIcon = (type) => {
  const value = type?.toLowerCase()

  if (value === 'kucing') {
    return '🐱'
  }

  if (value === 'anjing') {
    return '🐶'
  }

  return '🐾'
}

onMounted(() => {
  loadServices()
})
</script>

<template>
  <PublicLayout>

    <section class="page-header">
      <div class="header-content">

        <span class="badge">
          ✂️ Perawatan Hewan
        </span>

        <h1>Layanan Pet Shop</h1>

        <p>
          Temukan informasi layanan perawatan
          yang sesuai untuk hewan kesayangan Anda.
        </p>

      </div>
    </section>


    <section class="service-section">

      <!-- FILTER -->

      <div class="filter-card">

        <div class="field">
          <label>Cari Layanan</label>

          <input
            v-model="search"
            type="text"
            placeholder="Contoh: Grooming..."
            @keyup.enter="searchServices"
          />
        </div>


        <div class="field">
          <label>Jenis Hewan</label>

          <select v-model="animalType">

            <option value="">
              Semua Hewan
            </option>

            <option value="Kucing">
              Kucing
            </option>

            <option value="Anjing">
              Anjing
            </option>

            <option value="Umum">
              Umum
            </option>

          </select>
        </div>


        <button
          class="primary-button"
          @click="searchServices"
        >
          Cari
        </button>


        <button
          class="secondary-button"
          @click="resetFilter"
        >
          Reset
        </button>

      </div>


      <!-- RESULT -->

      <div class="result-heading">

        <div>
          <h2>Daftar Layanan</h2>

          <p v-if="!loading">
            {{ services.length }} layanan ditemukan
          </p>
        </div>

      </div>


      <!-- LOADING -->

      <div
        v-if="loading"
        class="state"
      >
        <div class="loader"></div>
        <p>Memuat layanan...</p>
      </div>


      <!-- ERROR -->

      <div
        v-else-if="error"
        class="state"
      >
        <div class="state-icon">
          ⚠️
        </div>

        <h3>Terjadi Kesalahan</h3>

        <p>{{ error }}</p>

        <button @click="loadServices">
          Coba Lagi
        </button>
      </div>


      <!-- EMPTY -->

      <div
        v-else-if="services.length === 0"
        class="state"
      >
        <div class="state-icon">
          🐾
        </div>

        <h3>Layanan tidak ditemukan</h3>

        <p>
          Coba gunakan pencarian atau
          jenis hewan yang berbeda.
        </p>

        <button @click="resetFilter">
          Tampilkan Semua
        </button>
      </div>


      <!-- SERVICE CARDS -->

      <div
        v-else
        class="service-grid"
      >

        <article
          v-for="service in services"
          :key="service.id"
          class="service-card"
        >

          <div class="service-top">

            <div class="animal-icon">
              {{ getAnimalIcon(service.animal_type) }}
            </div>

            <span class="animal-badge">
              {{ service.animal_type }}
            </span>

          </div>


          <div class="service-content">

            <h3>
              {{ service.name }}
            </h3>

            <p class="description">
              {{
                service.description
                || 'Tidak ada deskripsi layanan.'
              }}
            </p>


            <div class="service-meta">

              <div>
                <span>⏱️ Durasi</span>

                <strong>
                  {{ formatDuration(service.duration) }}
                </strong>
              </div>


              <div>
                <span>💰 Harga</span>

                <strong>
                  {{ formatRupiah(service.price) }}
                </strong>
              </div>

            </div>

          </div>

        </article>

      </div>


      <!-- AI PROMOTION -->

      <div class="ai-banner">

        <div class="ai-icon">
          ✨
        </div>

        <div class="ai-text">
          <h3>
            Bingung memilih layanan?
          </h3>

          <p>
            Gunakan AI Pet Assistant untuk membantu
            menemukan layanan yang relevan berdasarkan
            kebutuhan hewan Anda.
          </p>
        </div>

        <RouterLink
          to="/ai-assistant"
          class="ai-button"
        >
          Coba AI Pet Assistant
        </RouterLink>

      </div>

    </section>

  </PublicLayout>
</template>

<style scoped>
.page-header {
  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #f8fafc
    );

  padding: 65px 24px;
}

.header-content {
  max-width: 1200px;
  margin: auto;
}

.badge {
  display: inline-block;

  padding: 7px 13px;

  border-radius: 30px;

  background: #dbeafe;
  color: #1d4ed8;

  font-size: 13px;
  font-weight: 600;
}

.header-content h1 {
  margin: 16px 0 10px;

  font-size: 38px;

  color: #111827;
}

.header-content p {
  max-width: 600px;

  margin: 0;

  color: #6b7280;

  line-height: 1.7;
}


.service-section {
  max-width: 1200px;

  margin: auto;

  padding: 50px 24px 90px;
}


/* FILTER */

.filter-card {
  display: grid;

  grid-template-columns:
    minmax(250px, 1fr)
    220px
    auto
    auto;

  align-items: end;

  gap: 15px;

  padding: 20px;

  border: 1px solid #e5e7eb;

  border-radius: 15px;

  background: white;

  box-shadow:
    0 5px 20px rgba(0, 0, 0, 0.04);
}

.field {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.field label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.field input,
.field select {
  height: 43px;

  padding: 0 13px;

  border: 1px solid #d1d5db;

  border-radius: 8px;

  outline: none;

  background: white;
}

.field input:focus,
.field select:focus {
  border-color: #2563eb;
}

.primary-button,
.secondary-button {
  height: 43px;

  padding: 0 20px;

  border: none;

  border-radius: 8px;

  font-weight: 600;

  cursor: pointer;
}

.primary-button {
  background: #2563eb;
  color: white;
}

.secondary-button {
  background: #f3f4f6;
  color: #374151;
}


/* RESULT */

.result-heading {
  margin: 40px 0 22px;
}

.result-heading h2 {
  margin: 0 0 5px;

  color: #111827;
}

.result-heading p {
  margin: 0;

  color: #6b7280;

  font-size: 13px;
}


/* SERVICE GRID */

.service-grid {
  display: grid;

  grid-template-columns:
    repeat(3, minmax(0, 1fr));

  gap: 24px;
}

.service-card {
  overflow: hidden;

  border: 1px solid #e5e7eb;

  border-radius: 16px;

  background: white;

  transition:
    transform 0.2s,
    box-shadow 0.2s;
}

.service-card:hover {
  transform: translateY(-4px);

  box-shadow:
    0 15px 35px rgba(0, 0, 0, 0.08);
}

.service-top {
  min-height: 150px;

  padding: 25px;

  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #f8fafc
    );

  display: flex;

  align-items: flex-start;
  justify-content: space-between;
}

.animal-icon {
  font-size: 65px;
}

.animal-badge {
  padding: 6px 10px;

  border-radius: 20px;

  background: white;

  color: #2563eb;

  font-size: 11px;
  font-weight: 700;
}

.service-content {
  padding: 22px;
}

.service-content h3 {
  margin: 0 0 10px;

  color: #111827;

  font-size: 18px;
}

.description {
  min-height: 65px;

  color: #6b7280;

  font-size: 13px;

  line-height: 1.7;
}

.service-meta {
  display: grid;

  grid-template-columns: 1fr 1fr;

  gap: 10px;

  margin-top: 20px;

  padding-top: 17px;

  border-top: 1px solid #f3f4f6;
}

.service-meta div {
  display: flex;

  flex-direction: column;

  gap: 5px;
}

.service-meta span {
  color: #9ca3af;

  font-size: 11px;
}

.service-meta strong {
  color: #111827;

  font-size: 13px;
}


/* AI BANNER */

.ai-banner {
  margin-top: 55px;

  padding: 30px;

  border: 1px solid #bfdbfe;

  border-radius: 18px;

  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #f5f3ff
    );

  display: flex;

  align-items: center;

  gap: 20px;
}

.ai-icon {
  font-size: 42px;
}

.ai-text {
  flex: 1;
}

.ai-text h3 {
  margin: 0 0 6px;

  color: #111827;
}

.ai-text p {
  margin: 0;

  color: #6b7280;

  line-height: 1.6;

  font-size: 13px;
}

.ai-button {
  padding: 12px 18px;

  border-radius: 9px;

  background: #2563eb;

  color: white;

  text-decoration: none;

  font-size: 13px;
  font-weight: 600;
}


/* STATE */

.state {
  min-height: 300px;

  border: 1px dashed #d1d5db;

  border-radius: 16px;

  display: flex;

  flex-direction: column;

  align-items: center;
  justify-content: center;

  padding: 30px;

  text-align: center;

  color: #6b7280;
}

.state-icon {
  font-size: 42px;
}

.state h3 {
  margin-bottom: 5px;

  color: #111827;
}

.state button {
  margin-top: 10px;

  padding: 10px 16px;

  border: none;

  border-radius: 8px;

  background: #2563eb;

  color: white;

  cursor: pointer;
}

.loader {
  width: 35px;
  height: 35px;

  border: 4px solid #dbeafe;

  border-top-color: #2563eb;

  border-radius: 50%;

  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}


/* RESPONSIVE */

@media (max-width: 900px) {
  .service-grid {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .filter-card {
    grid-template-columns: 1fr 1fr;
  }

  .ai-banner {
    align-items: flex-start;
    flex-direction: column;
  }
}

@media (max-width: 600px) {
  .service-grid {
    grid-template-columns: 1fr;
  }

  .filter-card {
    grid-template-columns: 1fr;
  }

  .header-content h1 {
    font-size: 30px;
  }
}
</style>