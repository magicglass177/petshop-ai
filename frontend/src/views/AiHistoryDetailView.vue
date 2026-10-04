<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'

const route = useRoute()

const history = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const loadHistory = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get(
      `/customer/ai/history/${route.params.id}`
    )

    history.value = response.data.data

  } catch (error) {
    console.error(
      'Gagal mengambil detail riwayat AI:',
      error
    )

    if (error.response?.status === 404) {
      errorMessage.value =
        'Riwayat analisis AI tidak ditemukan.'
    } else {
      errorMessage.value =
        error.response?.data?.message ||
        'Detail riwayat AI tidak dapat dimuat.'
    }

  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Intl.DateTimeFormat(
    'id-ID',
    {
      dateStyle: 'long',
      timeStyle: 'short',
    }
  ).format(new Date(date))
}

const formatRupiah = (value) => {
  return new Intl.NumberFormat(
    'id-ID',
    {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }
  ).format(Number(value || 0))
}

const needLevelClass = (level) => {
  if (level === 'Ringan') {
    return 'level-light'
  }

  if (level === 'Tinggi') {
    return 'level-high'
  }

  return 'level-medium'
}

onMounted(() => {
  loadHistory()
})
</script>

<template>
  <main class="detail-page">
    <section class="detail-container">

      <!-- Back -->

      <RouterLink
        to="/customer/ai-history"
        class="back-link"
      >
        ← Kembali ke Riwayat AI
      </RouterLink>


      <!-- Loading -->

      <div
        v-if="loading"
        class="state-card"
      >
        <div class="spinner"></div>

        <h3>Memuat detail riwayat...</h3>

        <p>
          Data hasil analisis AI sedang diambil.
        </p>
      </div>


      <!-- Error -->

      <div
        v-else-if="errorMessage"
        class="state-card error-state"
      >
        <span class="state-icon">
          ⚠️
        </span>

        <h3>Detail tidak dapat ditampilkan</h3>

        <p>
          {{ errorMessage }}
        </p>

        <RouterLink
          to="/customer/ai-history"
          class="primary-link"
        >
          Kembali ke Riwayat
        </RouterLink>
      </div>


      <!-- Detail -->

      <template v-else-if="history">

        <!-- Header -->

        <section class="header-card">
          <div class="header-top">
            <div>
              <span class="eyebrow">
                ✦ DETAIL ANALISIS AI
              </span>

              <h1>
                {{ history.category || 'Analisis Pet' }}
              </h1>

              <p class="date">
                {{ formatDate(history.created_at) }}
              </p>
            </div>

            <span
              class="level-badge"
              :class="
                needLevelClass(
                  history.need_level
                )
              "
            >
              {{ history.need_level || '-' }}
            </span>
          </div>


          <!-- Pet -->

          <div class="pet-card">
            <template v-if="history.pet">
              <div class="pet-icon">
                {{
                  history.pet.animal_type
                    ?.toLowerCase()
                    .includes('kucing')
                    ? '🐱'
                    : history.pet.animal_type
                        ?.toLowerCase()
                        .includes('anjing')
                      ? '🐶'
                      : '🐾'
                }}
              </div>

              <div>
                <span>
                  Analisis berdasarkan profil
                </span>

                <h3>
                  {{ history.pet.name }}
                </h3>

                <p>
                  {{ history.pet.animal_type }}

                  <template v-if="history.pet.breed">
                    • {{ history.pet.breed }}
                  </template>

                  <template v-if="history.pet.gender">
                    • {{ history.pet.gender }}
                  </template>
                </p>
              </div>
            </template>

            <template v-else>
              <div class="pet-icon">
                💬
              </div>

              <div>
                <span>Mode Analisis</span>

                <h3>Tanpa data hewan</h3>

                <p>
                  Analisis dibuat berdasarkan informasi
                  yang dituliskan pada pertanyaan.
                </p>
              </div>
            </template>
          </div>
        </section>


        <!-- Pertanyaan -->

        <section class="content-card">
          <span class="section-label">
            PERTANYAAN
          </span>

          <h2>Kondisi atau Kebutuhan Pet</h2>

          <p class="question">
            {{ history.question }}
          </p>
        </section>


        <!-- Ringkasan -->

        <section class="content-card">
          <span class="section-label">
            HASIL ANALISIS
          </span>

          <h2>Ringkasan AI</h2>

          <p class="summary">
            {{ history.summary }}
          </p>

          <div class="analysis-info">
            <div>
              <span>Kategori</span>

              <strong>
                {{ history.category || '-' }}
              </strong>
            </div>

            <div>
              <span>Tingkat Kebutuhan</span>

              <strong>
                {{ history.need_level || '-' }}
              </strong>
            </div>
          </div>
        </section>


        <!-- Rekomendasi -->

        <section class="content-card">
          <div class="section-heading">
            <div>
              <span class="section-label">
                REKOMENDASI
              </span>

              <h2>
                Layanan yang Direkomendasikan
              </h2>
            </div>

            <span class="count-badge">
              {{
                history.recommended_services
                  ?.length || 0
              }}
              layanan
            </span>
          </div>


          <div
            v-if="
              history.recommended_services?.length
            "
            class="service-list"
          >
            <article
              v-for="(service, index) in
                history.recommended_services"
              :key="
                service.service_id || index
              "
              class="service-card"
            >
              <div class="service-number">
                {{ index + 1 }}
              </div>

              <div class="service-header">
                <div>
                  <span class="animal-badge">
                    {{ service.animal_type }}
                  </span>

                  <h3>
                    {{ service.name }}
                  </h3>
                </div>

                <strong class="price">
                  {{ formatRupiah(service.price) }}
                </strong>
              </div>

              <p class="description">
                {{ service.description }}
              </p>

              <span class="duration">
                🕐 Estimasi
                {{ service.duration }}
                menit
              </span>

              <div class="reason">
                <strong>
                  Mengapa direkomendasikan?
                </strong>

                <p>
                  {{ service.reason }}
                </p>
              </div>
            </article>
          </div>

          <div
            v-else
            class="empty-service"
          >
            Tidak ada rekomendasi layanan pada
            analisis ini.
          </div>
        </section>


        <!-- Safety -->

        <section class="safety-card">
          <span class="safety-icon">
            ⚕
          </span>

          <div>
            <strong>
              Catatan Keamanan
            </strong>

            <p>
              {{ history.safety_note }}
            </p>
          </div>
        </section>


        <!-- Metadata -->

        <section class="metadata">
          <span>
            Gateway:
            <strong>
              {{ history.gateway || '-' }}
            </strong>
          </span>

          <span>
            Model:
            <strong>
              {{ history.model || '-' }}
            </strong>
          </span>

          <span>
            Token:
            <strong>
              {{ history.total_tokens || 0 }}
            </strong>
          </span>

          <span>
            ID Analisis:
            <strong>
              #{{ history.id }}
            </strong>
          </span>
        </section>

      </template>

    </section>
  </main>
</template>

<style scoped>
.detail-page {
  min-height: 100vh;
  padding: 45px 20px 80px;
  background:
    linear-gradient(
      180deg,
      #f4faf7 0%,
      #ffffff 45%,
      #f7faf9 100%
    );
  color: #17352c;
}

.detail-container {
  width: min(950px, 100%);
  margin: 0 auto;
}

.back-link {
  display: inline-block;
  margin-bottom: 22px;
  color: #267451;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
}

.back-link:hover {
  text-decoration: underline;
}

.header-card,
.content-card,
.state-card {
  border: 1px solid #e0ebe5;
  border-radius: 20px;
  background: #ffffff;
  box-shadow:
    0 12px 35px rgba(26, 74, 58, 0.06);
}

.header-card,
.content-card {
  padding: 28px;
  margin-bottom: 20px;
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 25px;
}

.eyebrow,
.section-label {
  display: inline-block;
  color: #16824d;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.2px;
}

.header-card h1 {
  margin: 8px 0 0;
  color: #173f33;
  font-size: 30px;
}

.date {
  margin: 8px 0 0;
  color: #85958f;
  font-size: 12px;
}

.level-badge {
  padding: 7px 12px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
}

.level-light {
  background: #eaf8f1;
  color: #167849;
}

.level-medium {
  background: #fff4d8;
  color: #8a6816;
}

.level-high {
  background: #ffe8e8;
  color: #a23f3f;
}

.pet-card {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-top: 24px;
  padding: 16px;
  border: 1px solid #dce9e3;
  border-radius: 14px;
  background: #f5faf7;
}

.pet-icon {
  display: grid;
  place-items: center;
  width: 48px;
  height: 48px;
  flex: 0 0 48px;
  border-radius: 13px;
  background: #e5f5ec;
  font-size: 23px;
}

.pet-card span {
  color: #82928c;
  font-size: 10px;
  text-transform: uppercase;
}

.pet-card h3 {
  margin: 3px 0;
  color: #285443;
}

.pet-card p {
  margin: 0;
  color: #71837c;
  font-size: 12px;
}

.content-card h2 {
  margin: 7px 0 17px;
  color: #244b3e;
  font-size: 20px;
}

.question,
.summary {
  margin: 0;
  color: #536e65;
  line-height: 1.75;
}

.question {
  padding: 17px;
  border-radius: 13px;
  background: #f7faf9;
}

.analysis-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 13px;
  margin-top: 20px;
}

.analysis-info div {
  padding: 14px;
  border: 1px solid #e3ece8;
  border-radius: 12px;
}

.analysis-info span {
  display: block;
  margin-bottom: 5px;
  color: #899791;
  font-size: 11px;
}

.analysis-info strong {
  color: #31594c;
  font-size: 13px;
}

.section-heading {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
}

.count-badge {
  padding: 6px 10px;
  border-radius: 999px;
  background: #eaf8f1;
  color: #167849;
  font-size: 11px;
  font-weight: 800;
}

.service-list {
  display: grid;
  gap: 15px;
}

.service-card {
  position: relative;
  padding: 20px;
  border: 1px solid #e0ebe5;
  border-radius: 15px;
}

.service-number {
  position: absolute;
  top: -8px;
  left: -8px;
  display: grid;
  place-items: center;
  width: 27px;
  height: 27px;
  border-radius: 50%;
  background: #168b52;
  color: white;
  font-size: 11px;
  font-weight: 800;
}

.service-header {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}

.animal-badge {
  padding: 4px 8px;
  border-radius: 999px;
  background: #edf8f2;
  color: #297354;
  font-size: 10px;
  font-weight: 800;
}

.service-header h3 {
  margin: 8px 0 0;
}

.price {
  color: #168b52;
  white-space: nowrap;
}

.description {
  color: #687b74;
  line-height: 1.6;
  font-size: 13px;
}

.duration {
  display: inline-block;
  padding: 6px 9px;
  border-radius: 8px;
  background: #f3f6f5;
  color: #63746e;
  font-size: 11px;
}

.reason {
  margin-top: 15px;
  padding: 14px;
  border-left: 3px solid #39a86e;
  border-radius: 0 9px 9px 0;
  background: #f5faf7;
}

.reason strong {
  font-size: 12px;
}

.reason p {
  margin: 5px 0 0;
  color: #597168;
  line-height: 1.6;
  font-size: 12px;
}

.empty-service {
  padding: 25px;
  text-align: center;
  border: 1px dashed #ceddd6;
  border-radius: 13px;
  color: #788a83;
}

.safety-card {
  display: flex;
  gap: 14px;
  margin-bottom: 20px;
  padding: 20px;
  border: 1px solid #f0d9aa;
  border-radius: 16px;
  background: #fffaf0;
  color: #725b24;
}

.safety-icon {
  font-size: 20px;
}

.safety-card p {
  margin: 6px 0 0;
  line-height: 1.6;
  font-size: 13px;
}

.metadata {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 25px;
  padding: 17px 5px;
  color: #87958f;
  font-size: 11px;
}

.metadata strong {
  color: #587067;
}

.state-card {
  padding: 55px 25px;
  text-align: center;
}

.state-icon {
  font-size: 35px;
}

.state-card h3 {
  margin: 14px 0 7px;
}

.state-card p {
  color: #778981;
}

.error-state {
  border-color: #efcccc;
}

.primary-link {
  display: inline-block;
  margin-top: 10px;
  padding: 10px 15px;
  border-radius: 9px;
  background: #168b52;
  color: white;
  font-size: 12px;
  font-weight: 800;
  text-decoration: none;
}

.spinner {
  width: 36px;
  height: 36px;
  margin: 0 auto;
  border: 4px solid #e1eee7;
  border-top-color: #168b52;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 600px) {
  .detail-page {
    padding: 35px 14px 60px;
  }

  .header-card,
  .content-card {
    padding: 20px;
  }

  .header-top,
  .service-header,
  .section-heading {
    flex-direction: column;
  }

  .analysis-info {
    grid-template-columns: 1fr;
  }

  .price {
    white-space: normal;
  }
}
</style>