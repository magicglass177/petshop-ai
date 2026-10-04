<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'

const histories = ref([])
const loading = ref(true)
const errorMessage = ref('')

const loadHistories = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get(
      '/customer/ai/history'
    )

    histories.value =
      response.data.data || []

  } catch (error) {
    console.error(
      'Gagal mengambil riwayat AI:',
      error
    )

    errorMessage.value =
      error.response?.data?.message ||
      'Riwayat analisis AI tidak dapat dimuat.'

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
  loadHistories()
})
</script>

<template>
  <main class="history-page">
    <section class="history-container">

      <div class="page-heading">
        <div>
          <span class="eyebrow">
            ✦ AI PET ASSISTANT
          </span>

          <h1>Riwayat Analisis AI</h1>

          <p>
            Lihat kembali hasil analisis dan rekomendasi
            perawatan hewan yang pernah Anda lakukan.
          </p>
        </div>
      </div>


      <!-- Loading -->

      <div
        v-if="loading"
        class="state-card"
      >
        <div class="spinner"></div>

        <h3>Memuat riwayat...</h3>

        <p>
          Riwayat analisis AI sedang diambil.
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

        <h3>Riwayat tidak dapat dimuat</h3>

        <p>
          {{ errorMessage }}
        </p>

        <button
          type="button"
          @click="loadHistories"
        >
          Coba Lagi
        </button>
      </div>


      <!-- Belum ada history -->

      <div
        v-else-if="histories.length === 0"
        class="state-card"
      >
        <span class="state-icon">
          🐾
        </span>

        <h3>Belum ada riwayat analisis</h3>

        <p>
          Gunakan AI Pet Assistant untuk mendapatkan
          rekomendasi layanan sesuai kebutuhan hewan Anda.
        </p>

        <RouterLink
          to="/ai-assistant"
          class="primary-link"
        >
          Gunakan AI Pet Assistant
        </RouterLink>
      </div>


      <!-- History -->

      <div
        v-else
        class="history-content"
      >
        <div class="history-summary">
          <span>
            Total Riwayat
          </span>

          <strong>
            {{ histories.length }}
          </strong>
        </div>


        <div class="history-list">
          <article
            v-for="history in histories"
            :key="history.id"
            class="history-card"
          >
            <div class="history-top">
              <div>
                <span class="history-date">
                  {{ formatDate(history.created_at) }}
                </span>

                <h2>
                  {{ history.category || 'Analisis Pet' }}
                </h2>
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


            <div class="pet-info">
              <template v-if="history.pet">
                <span>🐾</span>

                <div>
                  <strong>
                    {{ history.pet.name }}
                  </strong>

                  <small>
                    {{ history.pet.animal_type }}

                    <template
                      v-if="history.pet.breed"
                    >
                      • {{ history.pet.breed }}
                    </template>
                  </small>
                </div>
              </template>

              <template v-else>
                <span>💬</span>

                <div>
                  <strong>
                    Tanpa data hewan
                  </strong>

                  <small>
                    Analisis berdasarkan pertanyaan
                  </small>
                </div>
              </template>
            </div>


            <div class="question-box">
              <span>Pertanyaan</span>

              <p>
                {{ history.question }}
              </p>
            </div>


            <div class="history-footer">
              <div class="recommendation-count">
                <strong>
                  {{
                    history.recommended_services
                      ?.length || 0
                  }}
                </strong>

                <span>
                  rekomendasi layanan
                </span>
              </div>

              <RouterLink
                :to="`/customer/ai-history/${history.id}`"
                class="detail-button"
              >
                Lihat Detail →
              </RouterLink>
            </div>
          </article>
        </div>
      </div>

    </section>
  </main>
</template>

<style scoped>
.history-page {
  min-height: 100vh;
  padding: 55px 20px 80px;
  background:
    linear-gradient(
      180deg,
      #f4faf7 0%,
      #ffffff 45%,
      #f7faf9 100%
    );
  color: #17352c;
}

.history-container {
  width: min(950px, 100%);
  margin: 0 auto;
}

.page-heading {
  margin-bottom: 32px;
}

.eyebrow {
  display: inline-block;
  margin-bottom: 12px;
  color: #16824d;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1.4px;
}

.page-heading h1 {
  margin: 0;
  color: #153f32;
  font-size: clamp(30px, 4vw, 43px);
}

.page-heading p {
  max-width: 650px;
  margin: 12px 0 0;
  color: #71817c;
  line-height: 1.7;
}

.history-summary {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  padding: 9px 14px;
  border: 1px solid #dce9e3;
  border-radius: 999px;
  background: #ffffff;
  color: #61766e;
  font-size: 13px;
}

.history-summary strong {
  color: #16824d;
}

.history-list {
  display: grid;
  gap: 18px;
}

.history-card,
.state-card {
  border: 1px solid #e0ebe5;
  border-radius: 19px;
  background: #ffffff;
  box-shadow:
    0 12px 35px rgba(26, 74, 58, 0.06);
}

.history-card {
  padding: 24px;
}

.history-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
}

.history-date {
  color: #8a9993;
  font-size: 11px;
}

.history-top h2 {
  margin: 7px 0 0;
  color: #194b38;
  font-size: 20px;
}

.level-badge {
  display: inline-block;
  padding: 6px 11px;
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

.pet-info {
  display: flex;
  align-items: center;
  gap: 11px;
  margin-top: 19px;
  padding: 12px 14px;
  border-radius: 11px;
  background: #f5faf7;
}

.pet-info div {
  display: flex;
  flex-direction: column;
}

.pet-info strong {
  color: #315a4c;
  font-size: 13px;
}

.pet-info small {
  margin-top: 2px;
  color: #81918b;
  font-size: 11px;
}

.question-box {
  margin-top: 17px;
}

.question-box span {
  color: #899791;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.question-box p {
  margin: 7px 0 0;
  color: #506b62;
  line-height: 1.65;
}

.history-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-top: 20px;
  padding-top: 17px;
  border-top: 1px solid #edf2ef;
}

.recommendation-count {
  display: flex;
  gap: 5px;
  color: #819089;
  font-size: 12px;
}

.recommendation-count strong {
  color: #16824d;
}

.detail-button,
.primary-link {
  text-decoration: none;
  border-radius: 10px;
  background: #168b52;
  color: #ffffff;
  font-size: 12px;
  font-weight: 800;
}

.detail-button {
  padding: 9px 13px;
}

.primary-link {
  display: inline-block;
  margin-top: 10px;
  padding: 11px 16px;
}

.detail-button:hover,
.primary-link:hover {
  background: #107443;
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
  margin: 0 auto 12px;
  max-width: 500px;
  color: #778981;
  line-height: 1.6;
}

.error-state {
  border-color: #f0cccc;
}

.error-state button {
  margin-top: 7px;
  padding: 10px 15px;
  border: 0;
  border-radius: 9px;
  background: #168b52;
  color: white;
  font-weight: 700;
  cursor: pointer;
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
  .history-page {
    padding: 40px 14px 60px;
  }

  .history-card {
    padding: 19px;
  }

  .history-top,
  .history-footer {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>