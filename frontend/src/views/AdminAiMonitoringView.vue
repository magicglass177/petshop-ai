<script setup>
import { onMounted, ref } from 'vue'
import api from '../services/api'

const statistics = ref({
  total_analyses: 0,
  total_tokens: 0,
  relevant_analyses: 0,
  with_pet: 0,
  without_pet: 0,
})

const recentAnalyses = ref([])
const loading = ref(true)
const errorMessage = ref('')

const loadMonitoring = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get(
      '/admin/ai/monitoring'
    )

    statistics.value =
      response.data.data.statistics

    recentAnalyses.value =
      response.data.data.recent_analyses || []

  } catch (error) {
    console.error(
      'Gagal mengambil monitoring AI:',
      error
    )

    errorMessage.value =
      error.response?.data?.message ||
      'Data monitoring AI tidak dapat dimuat.'

  } finally {
    loading.value = false
  }
}

const formatNumber = (value) => {
  return new Intl.NumberFormat(
    'id-ID'
  ).format(Number(value || 0))
}

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Intl.DateTimeFormat(
    'id-ID',
    {
      dateStyle: 'medium',
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
  loadMonitoring()
})
</script>

<template>
  <main class="monitoring-page">
    <section class="container">

      <!-- HEADER -->

      <div class="page-header">
        <div>
          <span class="eyebrow">
            ✦ ADMINISTRATOR
          </span>

          <h1>Monitoring AI</h1>

          <p>
            Pantau penggunaan AI Pet Assistant,
            konsumsi token, dan aktivitas analisis
            customer.
          </p>
        </div>

        <button
          type="button"
          class="refresh-button"
          :disabled="loading"
          @click="loadMonitoring"
        >
          ↻ Perbarui Data
        </button>
      </div>


      <!-- LOADING -->

      <div
        v-if="loading"
        class="state-card"
      >
        <div class="spinner"></div>

        <h3>Memuat monitoring AI...</h3>

        <p>
          Data penggunaan AI sedang diambil.
        </p>
      </div>


      <!-- ERROR -->

      <div
        v-else-if="errorMessage"
        class="state-card error-state"
      >
        <span class="state-icon">
          ⚠️
        </span>

        <h3>Data tidak dapat dimuat</h3>

        <p>
          {{ errorMessage }}
        </p>

        <button
          type="button"
          @click="loadMonitoring"
        >
          Coba Lagi
        </button>
      </div>


      <!-- CONTENT -->

      <template v-else>

        <!-- STATISTICS -->

        <section class="statistics-grid">

          <article class="stat-card">
            <div class="stat-icon">
              ✨
            </div>

            <div>
              <span>Total Analisis</span>

              <strong>
                {{
                  formatNumber(
                    statistics.total_analyses
                  )
                }}
              </strong>

              <small>
                Analisis tersimpan
              </small>
            </div>
          </article>


          <article class="stat-card">
            <div class="stat-icon">
              ⚡
            </div>

            <div>
              <span>Total Token</span>

              <strong>
                {{
                  formatNumber(
                    statistics.total_tokens
                  )
                }}
              </strong>

              <small>
                Token AI digunakan
              </small>
            </div>
          </article>


          <article class="stat-card">
            <div class="stat-icon">
              🐾
            </div>

            <div>
              <span>Dengan Hewan</span>

              <strong>
                {{
                  formatNumber(
                    statistics.with_pet
                  )
                }}
              </strong>

              <small>
                Menggunakan profil pet
              </small>
            </div>
          </article>


          <article class="stat-card">
            <div class="stat-icon">
              💬
            </div>

            <div>
              <span>Tanpa Hewan</span>

              <strong>
                {{
                  formatNumber(
                    statistics.without_pet
                  )
                }}
              </strong>

              <small>
                Berdasarkan pertanyaan
              </small>
            </div>
          </article>

        </section>


        <!-- RELEVANT INFO -->

        <section class="relevant-card">
          <div>
            <span>
              Analisis Relevan
            </span>

            <strong>
              {{
                formatNumber(
                  statistics.relevant_analyses
                )
              }}
            </strong>
          </div>

          <p>
            Jumlah analisis yang dikenali AI sebagai
            pertanyaan yang relevan dengan kebutuhan
            layanan pet shop.
          </p>
        </section>


        <!-- RECENT -->

        <section class="recent-section">

          <div class="section-heading">
            <div>
              <span class="section-label">
                AKTIVITAS
              </span>

              <h2>
                Analisis AI Terbaru
              </h2>
            </div>

            <span class="result-count">
              {{ recentAnalyses.length }}
              data terbaru
            </span>
          </div>


          <!-- EMPTY -->

          <div
            v-if="recentAnalyses.length === 0"
            class="empty-state"
          >
            <span>🤖</span>

            <h3>Belum ada aktivitas AI</h3>

            <p>
              Aktivitas customer akan muncul
              setelah mereka menggunakan
              AI Pet Assistant.
            </p>
          </div>


          <!-- LIST -->

          <div
            v-else
            class="analysis-list"
          >
            <article
              v-for="analysis in recentAnalyses"
              :key="analysis.id"
              class="analysis-card"
            >

              <!-- TOP -->

              <div class="analysis-top">

                <div class="customer">
                  <div class="avatar">
                    {{
                      analysis.customer?.user?.name
                        ?.charAt(0)
                        .toUpperCase() || '?'
                    }}
                  </div>

                  <div>
                    <strong>
                      {{
                        analysis.customer?.user?.name
                          || 'Customer'
                      }}
                    </strong>

                    <small>
                      {{
                        analysis.customer?.user?.email
                          || '-'
                      }}
                    </small>
                  </div>
                </div>


                <div class="analysis-meta">
                  <span
                    class="level-badge"
                    :class="
                      needLevelClass(
                        analysis.need_level
                      )
                    "
                  >
                    {{ analysis.need_level || '-' }}
                  </span>

                  <small>
                    {{ formatDate(analysis.created_at) }}
                  </small>
                </div>

              </div>


              <!-- PET -->

              <div class="pet-row">
                <template v-if="analysis.pet">
                  <span>🐾</span>

                  <div>
                    <strong>
                      {{ analysis.pet.name }}
                    </strong>

                    <small>
                      {{ analysis.pet.animal_type }}

                      <template
                        v-if="analysis.pet.breed"
                      >
                        • {{ analysis.pet.breed }}
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


              <!-- CONTENT -->

              <div class="analysis-content">
                <span class="category">
                  {{
                    analysis.category ||
                    'Analisis Pet'
                  }}
                </span>

                <p class="question">
                  {{ analysis.question }}
                </p>

                <p
                  v-if="analysis.summary"
                  class="summary"
                >
                  {{ analysis.summary }}
                </p>
              </div>


              <!-- FOOTER -->

              <div class="analysis-footer">

                <div class="footer-item">
                  <span>Rekomendasi</span>

                  <strong>
                    {{
                      analysis.recommended_services
                        ?.length || 0
                    }}
                    layanan
                  </strong>
                </div>


                <div class="footer-item">
                  <span>Token</span>

                  <strong>
                    {{
                      formatNumber(
                        analysis.total_tokens
                      )
                    }}
                  </strong>
                </div>


                <div class="footer-item">
                  <span>Model</span>

                  <strong>
                    {{ analysis.model || '-' }}
                  </strong>
                </div>


                <div class="footer-item">
                  <span>ID</span>

                  <strong>
                    #{{ analysis.id }}
                  </strong>
                </div>

              </div>

            </article>
          </div>

        </section>

      </template>

    </section>
  </main>
</template>

<style scoped>
.monitoring-page {
  min-height: 100vh;
  padding: 48px 20px 80px;
  background:
    linear-gradient(
      180deg,
      #f5f8fc 0%,
      #ffffff 45%,
      #f7f9fb 100%
    );
  color: #1f2937;
}

.container {
  width: min(1100px, 100%);
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 25px;
  margin-bottom: 30px;
}

.eyebrow,
.section-label {
  display: inline-block;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 1.3px;
}

.page-header h1 {
  margin: 7px 0 0;
  color: #111827;
  font-size: clamp(34px, 4vw, 46px);
}

.page-header p {
  max-width: 680px;
  margin: 12px 0 0;
  color: #6b7280;
  font-size: 15px;
  line-height: 1.7;
}

.refresh-button {
  padding: 12px 18px;
  border: 1px solid #dbe3ef;
  border-radius: 11px;
  background: #ffffff;
  color: #2563eb;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
}

.refresh-button:hover {
  background: #eff6ff;
}

.refresh-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* STATISTICS */

.statistics-grid {
  display: grid;
  grid-template-columns:
    repeat(4, minmax(0, 1fr));
  gap: 15px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px;
  border: 1px solid #e5eaf1;
  border-radius: 16px;
  background: #ffffff;
  box-shadow:
    0 8px 25px rgba(31, 41, 55, 0.04);
}

.stat-icon {
  display: grid;
  place-items: center;
  width: 50px;
  height: 50px;
  flex: 0 0 50px;
  border-radius: 13px;
  background: #eff6ff;
  font-size: 23px;
}

.stat-card span,
.stat-card small {
  display: block;
}

.stat-card span {
  color: #6b7280;
  font-size: 13px;
}

.stat-card strong {
  display: block;
  margin: 4px 0;
  color: #111827;
  font-size: 27px;
}

.stat-card small {
  color: #9ca3af;
  font-size: 11px;
}

/* RELEVANT */

.relevant-card {
  display: flex;
  align-items: center;
  gap: 22px;
  margin-top: 15px;
  padding: 17px 20px;
  border: 1px solid #dbeafe;
  border-radius: 14px;
  background: #eff6ff;
}

.relevant-card div {
  min-width: 125px;
}

.relevant-card span {
  display: block;
  color: #5b76a5;
  font-size: 12px;
}

.relevant-card strong {
  display: block;
  margin-top: 3px;
  color: #2563eb;
  font-size: 27px;
}

.relevant-card p {
  margin: 0;
  color: #60728f;
  font-size: 14px;
  line-height: 1.6;
}

/* RECENT */

.recent-section {
  margin-top: 35px;
}

.section-heading {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 20px;
  margin-bottom: 17px;
}

.section-heading h2 {
  margin: 5px 0 0;
  color: #111827;
  font-size: 25px;
}

.result-count {
  padding: 7px 12px;
  border-radius: 999px;
  background: #f3f4f6;
  color: #6b7280;
  font-size: 12px;
  font-weight: 700;
}

.analysis-list {
  display: grid;
  gap: 16px;
}

.analysis-card {
  padding: 22px;
  border: 1px solid #e5eaf1;
  border-radius: 17px;
  background: #ffffff;
  box-shadow:
    0 8px 25px rgba(31, 41, 55, 0.04);
}

.analysis-top {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}

.customer {
  display: flex;
  align-items: center;
  gap: 11px;
}

.avatar {
  display: grid;
  place-items: center;
  width: 39px;
  height: 39px;
  border-radius: 50%;
  background: #2563eb;
  color: white;
  font-size: 14px;
  font-weight: 800;
}

.customer strong,
.customer small {
  display: block;
}

.customer strong {
  color: #1f2937;
  font-size: 14px;
}

.customer small {
  margin-top: 3px;
  color: #9ca3af;
  font-size: 11px;
}

.analysis-meta {
  text-align: right;
}

.analysis-meta small {
  display: block;
  margin-top: 7px;
  color: #9ca3af;
  font-size: 11px;
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

/* PET */

.pet-row {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-top: 17px;
  padding: 10px 12px;
  border-radius: 10px;
  background: #f8fafc;
}

.pet-row div {
  display: flex;
  flex-direction: column;
}

.pet-row strong {
  color: #475569;
  font-size: 12px;
}

.pet-row small {
  margin-top: 3px;
  color: #94a3b8;
  font-size: 11px;
}

/* CONTENT */

.analysis-content {
  margin-top: 17px;
}

.category {
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
}

.question {
  margin: 8px 0 0;
  color: #374151;
  font-size: 15px;
  font-weight: 600;
  line-height: 1.6;
}

.summary {
  margin: 9px 0 0;
  color: #6b7280;
  font-size: 13px;
  line-height: 1.7;
}

/* FOOTER */

.analysis-footer {
  display: grid;
  grid-template-columns:
    repeat(4, minmax(0, 1fr));
  gap: 10px;
  margin-top: 18px;
  padding-top: 15px;
  border-top: 1px solid #eef0f3;
}

.footer-item span,
.footer-item strong {
  display: block;
}

.footer-item span {
  color: #9ca3af;
  font-size: 11px;
}

.footer-item strong {
  margin-top: 4px;
  overflow: hidden;
  color: #4b5563;
  font-size: 12px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* STATES */

.state-card,
.empty-state {
  padding: 55px 25px;
  border: 1px solid #e5eaf1;
  border-radius: 17px;
  background: white;
  text-align: center;
}

.state-icon,
.empty-state > span {
  font-size: 34px;
}

.state-card h3,
.empty-state h3 {
  margin: 13px 0 6px;
}

.state-card p,
.empty-state p {
  margin: 0;
  color: #6b7280;
}

.state-card button {
  margin-top: 15px;
  padding: 9px 14px;
  border: 0;
  border-radius: 8px;
  background: #2563eb;
  color: white;
  cursor: pointer;
}

.error-state {
  border-color: #fecaca;
}

.spinner {
  width: 36px;
  height: 36px;
  margin: 0 auto;
  border: 4px solid #e5e7eb;
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
  .statistics-grid {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 600px) {
  .monitoring-page {
    padding: 35px 14px 60px;
  }

  .page-header,
  .section-heading,
  .analysis-top {
    flex-direction: column;
    align-items: flex-start;
  }

  .statistics-grid {
    grid-template-columns: 1fr;
  }

  .analysis-meta {
    text-align: left;
  }

  .analysis-footer {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .relevant-card {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>