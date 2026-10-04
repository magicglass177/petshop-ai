<script setup>
import {
  computed,
  onMounted,
  ref,
} from 'vue'

import { useRouter } from 'vue-router'

import api from '../services/api'
import { authState } from '../services/auth'


/*
|--------------------------------------------------------------------------
| State AI
|--------------------------------------------------------------------------
*/

const router = useRouter()

const goBack = () => {
  if (router.options.history.state.back) {
    router.back()
  } else {
    router.push('/')
  }
}

const question = ref('')
const loading = ref(false)
const errorMessage = ref('')
const result = ref(null)

const isHealthAnalysis = computed(() => {
  return result.value?.data?.is_relevant === true
    && result.value?.data?.analysis_type === 'health'
})

const urgencyLabel = (level) => ({
  monitor: 'Pantau kondisi dan tanda perburukan',
  vet_soon: 'Perlu pemeriksaan dokter hewan',
  urgent: 'Hubungi dokter hewan segera',
}[level] || 'Urgensi belum tersedia')


/*
|--------------------------------------------------------------------------
| State Pet
|--------------------------------------------------------------------------
*/

const pets = ref([])
const selectedPetId = ref('')
const loadingPets = ref(false)
const petError = ref('')


/*
|--------------------------------------------------------------------------
| Status customer
|--------------------------------------------------------------------------
*/

const isCustomer = computed(() => {
  return authState.user?.role === 'customer'
})


/*
|--------------------------------------------------------------------------
| Pet yang sedang dipilih
|--------------------------------------------------------------------------
*/

const selectedPet = computed(() => {
  if (!selectedPetId.value) {
    return null
  }

  return pets.value.find(
    (pet) =>
      Number(pet.id) ===
      Number(selectedPetId.value)
  ) || null
})


/*
|--------------------------------------------------------------------------
| Contoh pertanyaan
|--------------------------------------------------------------------------
*/

const exampleQuestions = [
  'Kucing saya beberapa hari ini sering muntah, sangat lemas, dan nafsu makannya menurun. Apa kemungkinan penyebab dan langkah berikutnya?',

  'Kucing Persia saya berumur 2 tahun. Bulunya mulai kusut dan banyak rontok. Layanan apa yang cocok?',

  'Anjing saya sudah lama tidak grooming dan kukunya mulai panjang. Perawatan apa yang cocok?',

  'Saya ingin membersihkan telinga dan memotong kuku kucing saya. Layanan apa yang tersedia?',
]


/*
|--------------------------------------------------------------------------
| Ambil data pets customer
|--------------------------------------------------------------------------
*/

const loadPets = async () => {
  if (!isCustomer.value) {
    return
  }

  loadingPets.value = true
  petError.value = ''

  try {
    const response = await api.get(
      '/customer/pets'
    )

    pets.value =
      response.data.data || []

  } catch (error) {
    console.error(
      'Gagal mengambil data hewan:',
      error
    )

    petError.value =
      'Data hewan tidak dapat dimuat.'

  } finally {
    loadingPets.value = false
  }
}


/*
|--------------------------------------------------------------------------
| Gunakan contoh pertanyaan
|--------------------------------------------------------------------------
*/

const useExample = (example) => {
  question.value = example
  errorMessage.value = ''
}


/*
|--------------------------------------------------------------------------
| Analisis AI
|--------------------------------------------------------------------------
*/

const analyzePet = async () => {
  if (loading.value) return
  errorMessage.value = ''
  result.value = null

  const cleanedQuestion =
    question.value.trim()


  if (cleanedQuestion.length < 10) {
    errorMessage.value =
      'Silakan jelaskan kondisi atau kebutuhan hewan minimal 10 karakter.'

    return
  }


  loading.value = true


  try {

    let response


    /*
    |--------------------------------------------------------------------------
    | Tentukan endpoint berdasarkan status login
    |--------------------------------------------------------------------------
    */

    if (isCustomer.value) {

      /*
      |--------------------------------------------------------------------------
      | CUSTOMER
      |--------------------------------------------------------------------------
      |
      | Customer selalu menggunakan endpoint customer agar
      | hasil analisis dapat disimpan ke riwayat.
      | Jika pet tidak dipilih, pet_id dikirim sebagai null.
      |
      */

      response = await api.post(
        '/customer/ai/analyze',
        {
          pet_id: selectedPetId.value
            ? Number(selectedPetId.value)
            : null,

          question:
            cleanedQuestion,
        }
      )

    } else {

      /*
      |--------------------------------------------------------------------------
      | GUEST
      |--------------------------------------------------------------------------
      |
      | Guest tetap menggunakan endpoint public dan
      | hasil analisis tidak disimpan ke riwayat.
      |
      */

      response = await api.post(
        '/ai/analyze',
        {
          question:
            cleanedQuestion,
        }
      )
    }


    if (response.data.success) {
      result.value =
        response.data

    } else {
      errorMessage.value =
        response.data.message ||
        'AI tidak dapat melakukan analisis.'
    }

  } catch (error) {

    console.error(
      'AI Pet Assistant Error:',
      error
    )


    /*
    |--------------------------------------------------------------------------
    | Validation error
    |--------------------------------------------------------------------------
    */

    if (
      error.response?.status === 422
    ) {

      const validationMessage =
        error.response?.data
          ?.errors
          ?.question?.[0]

      errorMessage.value =
        validationMessage ||
        error.response?.data
          ?.message ||
        'Data yang dikirim tidak valid.'


    /*
    |--------------------------------------------------------------------------
    | Pet bukan milik customer / tidak ditemukan
    |--------------------------------------------------------------------------
    */

    } else if (
      error.response?.status === 404
    ) {

      errorMessage.value =
        error.response?.data
          ?.message ||
        'Data hewan tidak ditemukan.'


    /*
    |--------------------------------------------------------------------------
    | Rate limit
    |--------------------------------------------------------------------------
    */

    } else if (
      error.response?.status === 429
    ) {

      errorMessage.value =
        'AI sedang mencapai batas penggunaan. Silakan coba kembali beberapa saat lagi.'


    /*
    |--------------------------------------------------------------------------
    | AI Gateway error
    |--------------------------------------------------------------------------
    */

    } else if (
      error.response?.status === 503
    ) {

      errorMessage.value =
        error.response?.data
          ?.message ||
        'AI Pet Assistant sementara tidak tersedia.'


    /*
    |--------------------------------------------------------------------------
    | Backend tidak terhubung
    |--------------------------------------------------------------------------
    */

    } else if (!error.response) {

      errorMessage.value =
        'Tidak dapat terhubung ke server. Pastikan backend Laravel sedang berjalan.'


    /*
    |--------------------------------------------------------------------------
    | Error lainnya
    |--------------------------------------------------------------------------
    */

    } else {

      errorMessage.value =
        'Terjadi kesalahan saat melakukan analisis.'
    }

  } finally {

    loading.value = false
  }
}


/*
|--------------------------------------------------------------------------
| Reset analisis
|--------------------------------------------------------------------------
*/

const resetAnalysis = () => {
  question.value = ''
  result.value = null
  errorMessage.value = ''

  /*
  | Pet tetap dipilih agar customer
  | bisa melakukan analisis berikutnya
  | untuk hewan yang sama.
  */
}


/*
|--------------------------------------------------------------------------
| Format Rupiah
|--------------------------------------------------------------------------
*/

const formatRupiah = (value) => {
  return new Intl.NumberFormat(
    'id-ID',
    {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }
  ).format(
    Number(value || 0)
  )
}


/*
|--------------------------------------------------------------------------
| Badge kebutuhan
|--------------------------------------------------------------------------
*/

const needLevelClass = (level) => {
  if (level === 'Ringan') {
    return 'level-light'
  }

  if (level === 'Tinggi') {
    return 'level-high'
  }

  return 'level-medium'
}


/*
|--------------------------------------------------------------------------
| Load data setelah halaman dibuka
|--------------------------------------------------------------------------
*/

onMounted(() => {
  loadPets()
})
</script>

<template>
  <main class="ai-page">
    <section class="hero">
      <div class="back-navigation">
        <button type="button" class="back-button" @click="goBack">
          <span aria-hidden="true">←</span>
          Kembali
        </button>
      </div>
      <div class="hero-content">
        <span class="eyebrow">
          ✦ AI PET ASSISTANT
        </span>

        <h1>
          Pahami Kebutuhan dan Kondisi
          Hewan Kesayangan
        </h1>

        <p>
          Ceritakan kondisi atau kebutuhan hewan Anda.
          AI Pet Assistant membantu informasi kesehatan awal
          dan rekomendasi perawatan dari katalog layanan.
          Hasil analisis membantu memahami langkah berikutnya.
        </p>

        <div class="architecture">
          <span>Vue</span>
          <b>→</b>
          <span>Laravel API</span>
          <b>→</b>
          <span>AtmoRouter</span>
          <b>→</b>
          <span>AI</span>
        </div>
      </div>
    </section>

    <section class="content-container">
      <div class="ai-grid">
        <!-- FORM -->
        <div class="question-card">
          <div class="card-heading">
            <div class="icon-box">
              🐾
            </div>

            <div>
              <h2>Konsultasikan Kebutuhan Pet</h2>
              <p>
                Jelaskan kondisi hewan secara lengkap
                agar rekomendasi lebih relevan.
              </p>
            </div>
          </div>

          <form @submit.prevent="analyzePet">
            <label for="question">
              Kondisi atau kebutuhan hewan
            </label>

            <!--
|--------------------------------------------------------------------------
| Pilihan Pet untuk Customer
|--------------------------------------------------------------------------
-->

<div
  v-if="isCustomer"
  class="pet-selector"
>
  <div class="pet-selector-header">
    <div>
      <span class="pet-selector-label">
        🐾 Data Hewan Saya
      </span>

      <p>
        Pilih hewan agar AI dapat memberikan
        rekomendasi berdasarkan profil hewan
        yang sudah tersimpan.
      </p>
    </div>
  </div>


  <!-- Loading pets -->

  <div
    v-if="loadingPets"
    class="pet-loading"
  >
    Memuat data hewan...
  </div>


  <!-- Error pets -->

  <div
    v-else-if="petError"
    class="pet-error"
  >
    {{ petError }}
  </div>


  <!-- Select pet -->

  <template v-else>

    <select
      v-model="selectedPetId"
      class="pet-select"
      :disabled="loading"
    >
      <option value="">
        Tanpa data hewan
      </option>

      <option
        v-for="pet in pets"
        :key="pet.id"
        :value="pet.id"
      >
        {{ pet.name }}
        — {{ pet.animal_type }}
        <template v-if="pet.breed">
          ({{ pet.breed }})
        </template>
      </option>
    </select>


    <!-- Pet terpilih -->

    <div
      v-if="selectedPet"
      class="selected-pet-card"
    >
      <div class="selected-pet-icon">
        {{
          selectedPet.animal_type
            ?.toLowerCase()
            .includes('kucing')
            ? '🐱'
            : selectedPet.animal_type
                ?.toLowerCase()
                .includes('anjing')
              ? '🐶'
              : '🐾'
        }}
      </div>

      <div class="selected-pet-info">
        <strong>
          {{ selectedPet.name }}
        </strong>

        <span>
          {{ selectedPet.animal_type }}

          <template
            v-if="selectedPet.breed"
          >
            • {{ selectedPet.breed }}
          </template>
        </span>

        <small>
          AI akan menggunakan profil
          {{ selectedPet.name }}
          sebagai konteks tambahan.
        </small>
      </div>
    </div>


    <!-- Tidak punya pet -->

    <div
      v-if="pets.length === 0"
      class="no-pets-info"
    >
      <span>ℹ️</span>

      <p>
        Anda belum memiliki data hewan.
        Tambahkan hewan melalui menu
        <strong>Hewan Saya</strong>
        untuk mendapatkan analisis yang
        lebih personal.
      </p>
    </div>

  </template>
</div>

            <div
  v-if="isCustomer && selectedPet"
  class="analysis-mode pet-mode"
>
  <span>✓</span>

  <p>
    Analisis akan menggunakan data
    <strong>{{ selectedPet.name }}</strong>
    sebagai konteks.
  </p>
</div>

<div
  v-else
  class="analysis-mode general-mode"
>
  <span>💬</span>

  <p>
    Analisis menggunakan informasi yang
    Anda tuliskan pada pertanyaan.
  </p>
</div>

            <textarea
              id="question"
              v-model="question"
              rows="8"
              maxlength="2000"
              placeholder="Contoh: Kucing Persia saya berumur 2 tahun. Bulunya mulai kusut dan banyak rontok..."
              :disabled="loading"
            ></textarea>

            <div class="textarea-info">
              <span>
                Minimal 10 karakter
              </span>

              <span>
                {{ question.length }}/2000
              </span>
            </div>

            <div
              v-if="errorMessage"
              class="error-box"
            >
              <strong>Analisis gagal</strong>
              <span>{{ errorMessage }}</span>
            </div>

            <button
              type="submit"
              class="analyze-button"
              :disabled="loading || question.trim().length < 10"
            >
              <template v-if="loading">
                <span class="spinner"></span>
                AI sedang menganalisis...
              </template>

              <template v-else>
                ✦ Analisis dengan AI
              </template>
            </button>
          </form>

          <div class="examples">
            <p class="examples-title">
              Coba contoh pertanyaan
            </p>

            <button
              v-for="(example, index) in exampleQuestions"
              :key="index"
              type="button"
              class="example-button"
              :disabled="loading"
              @click="useExample(example)"
            >
              <span>💬</span>
              {{ example }}
            </button>
          </div>
        </div>

        <!-- INFO -->
        <aside class="info-column">
          <div class="info-card">
            <div class="info-icon">
              🤖
            </div>

            <h3>Bagaimana AI bekerja?</h3>

            <div class="step">
              <span>1</span>
              <p>
                Anda menjelaskan kebutuhan hewan.
              </p>
            </div>

            <div class="step">
              <span>2</span>
              <p>
                Laravel mengambil layanan aktif
                dari database.
              </p>
            </div>

            <div class="step">
              <span>3</span>
              <p>
                AI menilai kebutuhan perawatan atau
                memberikan informasi kesehatan awal.
              </p>
            </div>

            <div class="step">
              <span>4</span>
              <p>
                Laravel memvalidasi struktur hasil
                dan rekomendasi layanan AI.
              </p>
            </div>
          </div>

          <div class="warning-card">
            <strong>⚕ Catatan Penting</strong>

            <p>
              AI dapat menjelaskan kemungkinan penyebab awal,
              tetapi tidak memastikan penyakit. Untuk kondisi
              kesehatan yang mengkhawatirkan, pemeriksaan
              dokter hewan tetap diperlukan.
            </p>
          </div>
        </aside>
      </div>

      <!-- LOADING -->
      <section
        v-if="loading"
        class="loading-card"
      >
        <div class="large-spinner"></div>

        <h3>AI sedang menganalisis...</h3>

        <p>
          Kami sedang menganalisis kondisi atau kebutuhan
          hewan berdasarkan informasi yang Anda berikan.
        </p>
      </section>

      <!-- RESULT -->
      <section
        v-if="result && !loading"
        class="result-section"
      >
        <div class="result-header">
          <div>
            <span class="result-label">
              ✦ HASIL ANALISIS AI
            </span>

            <h2>{{ isHealthAnalysis ? 'Informasi Kesehatan Awal' : 'Hasil Analisis untuk Pet Anda' }}</h2>

            <p>
              {{ isHealthAnalysis
                ? 'Kemungkinan penyebab dan langkah pemeriksaan berdasarkan informasi yang Anda berikan.'
                : 'Informasi kebutuhan dan rekomendasi berdasarkan katalog layanan yang tersedia.' }}
            </p>
          </div>

          <button
            type="button"
            class="reset-button"
            @click="resetAnalysis"
          >
            Analisis Baru
          </button>
        </div>

        <!--
|--------------------------------------------------------------------------
| Pet yang digunakan pada analisis
|--------------------------------------------------------------------------
-->

<div
  v-if="result.pet"
  class="result-pet-card"
>
  <div class="result-pet-icon">
    {{
      result.pet.animal_type
        ?.toLowerCase()
        .includes('kucing')
        ? '🐱'
        : result.pet.animal_type
            ?.toLowerCase()
            .includes('anjing')
          ? '🐶'
          : '🐾'
    }}
  </div>

  <div class="result-pet-main">
    <span>
      Analisis berdasarkan profil
    </span>

    <h3>
      {{ result.pet.name }}
    </h3>

    <div class="result-pet-details">
      <span>
        {{ result.pet.animal_type }}
      </span>

      <span
        v-if="result.pet.breed"
      >
        {{ result.pet.breed }}
      </span>

      <span
        v-if="result.pet.gender"
      >
        {{ result.pet.gender }}
      </span>

      <span
        v-if="result.pet.weight"
      >
        {{ result.pet.weight }} kg
      </span>
    </div>
  </div>

  <div class="database-badge">
    ✓ Data tersimpan
  </div>
</div>

        <div class="analysis-summary">
          <div class="summary-item">
            <span class="summary-label">
              Kategori Kebutuhan
            </span>

            <strong>
              {{ result.data.category }}
            </strong>
          </div>

          <div class="summary-item">
            <span class="summary-label">
              Tingkat Kebutuhan
            </span>

            <span
              class="level-badge"
              :class="needLevelClass(
                result.data.need_level
              )"
            >
              {{ result.data.need_level }}
            </span>
          </div>
        </div>

        <div class="summary-box">
          <span class="summary-icon">
            ✨
          </span>

          <div>
            <h3>Ringkasan AI</h3>
            <p>{{ result.data.summary }}</p>
          </div>
        </div>

        <section v-if="isHealthAnalysis" class="health-details">
          <div
            v-if="result.data.urgency"
            class="health-urgency"
            :class="'urgency-' + result.data.urgency.level"
            role="status"
          >
            <h3>{{ urgencyLabel(result.data.urgency.level) }}</h3>
            <p>{{ result.data.urgency.reason }}</p>
            <strong>{{ result.data.urgency.action }}</strong>
          </div>

          <div class="health-section">
            <h3>Kemungkinan Penyebab Awal</h3>
            <p class="health-caption">
              Beberapa kondisi dapat memiliki gejala serupa.
              Daftar ini belum memastikan penyakit yang dialami hewan.
            </p>
            <ul v-if="result.data.possible_causes?.length" class="health-cause-list">
              <li v-for="(cause, index) in result.data.possible_causes" :key="index">
                <strong>{{ cause.name }}</strong>
                <p>{{ cause.explanation }}</p>
              </li>
            </ul>
            <p v-else>
              Informasi yang tersedia belum cukup untuk menjelaskan kemungkinan penyebab.
            </p>
          </div>

          <div v-if="result.data.follow_up_questions?.length" class="health-section">
            <h3>Informasi Tambahan yang Diperlukan</h3>
            <p class="health-caption">
              Anda dapat melengkapi pertanyaan baru dengan jawaban berikut.
              Jika perlu bantuan segera, jangan menunda pemeriksaan untuk menjawabnya.
            </p>
            <ol class="health-list">
              <li v-for="(item, index) in result.data.follow_up_questions" :key="index">
                {{ item }}
              </li>
            </ol>
          </div>
        </section>

        <section v-if="result.data.next_steps?.length" class="health-section next-steps">
          <h3>Langkah Selanjutnya</h3>
          <ol class="health-list">
            <li v-for="(item, index) in result.data.next_steps" :key="index">
              {{ item }}
            </li>
          </ol>
        </section>

        <div
  v-if="result.data.is_relevant === false"
  class="irrelevant-box"
>
  <strong>💬 Pertanyaan di luar cakupan</strong>

  <p>
    AI Pet Assistant difokuskan untuk membantu
    informasi mengenai hewan peliharaan,
    perawatan, grooming, produk, dan layanan
    pet shop.
  </p>
</div>

        <div v-if="!isHealthAnalysis && result.data.is_relevant" class="recommendation-heading">
          <div>
            <h3>Layanan yang Direkomendasikan</h3>

            <p>
              {{
                result.data.recommended_services.length
              }}
              layanan sesuai dengan kebutuhan pet Anda.
            </p>
          </div>
        </div>

        <div
          v-if="
            result.data.recommended_services.length
          "
          class="service-grid"
        >
          <article
            v-for="(service, index) in
              result.data.recommended_services"
            :key="service.service_id"
            class="service-card"
          >
            <div class="service-number">
              {{ index + 1 }}
            </div>

            <div class="service-top">
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

            <div class="duration">
              🕐 Estimasi {{ service.duration }} menit
            </div>

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
          v-else-if="!isHealthAnalysis && result.data.is_relevant"
          class="empty-recommendation"
        >
          <span>🐾</span>

          <h3>
            Belum ada layanan yang sesuai
          </h3>

          <p>
            Katalog saat ini belum menyediakan layanan yang cukup sesuai.
            AI tidak menambahkan atau memaksakan layanan lain.
          </p>
        </div>

        <div class="safety-note">
          <span>⚕</span>

          <div>
            <strong>Catatan Keamanan</strong>

            <p>
              {{ result.data.safety_note }}
            </p>
          </div>
        </div>

        <div
          v-if="result.ai"
          class="ai-meta"
        >
          <span>
            Gateway:
            <strong>{{ result.ai.gateway }}</strong>
          </span>

          <span>
            Model:
            <strong>{{ result.ai.model }}</strong>
          </span>

          <span
            v-if="result.ai.usage?.total_tokens"
          >
            Token:
            <strong>
              {{ result.ai.usage.total_tokens }}
            </strong>
          </span>
        </div>
      </section>
    </section>
  </main>
</template>

<style scoped>
.back-navigation {
  max-width: 1200px;
  margin: 0 auto 32px;
  display: flex;
  justify-content: flex-start;
}

.back-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-height: 44px;
  padding: 10px 16px;
  border: 1px solid #d7e9e0;
  border-radius: 10px;
  background: #ffffff;
  color: #17352c;
  font: inherit;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s, border-color 0.2s;
}

.back-button:hover {
  background: #eef8f2;
  border-color: #16824d;
}

.back-button:focus-visible {
  outline: 3px solid #16824d;
  outline-offset: 3px;
}

.ai-page {
  min-height: 100vh;
  background:
    linear-gradient(
      180deg,
      #f5fbf8 0%,
      #ffffff 45%,
      #f7faf9 100%
    );
  color: #17352c;
}

.hero {
  padding: 28px 24px 60px;
  text-align: center;
  background:
    radial-gradient(
      circle at top,
      rgba(39, 174, 96, 0.14),
      transparent 55%
    );
}

.hero-content {
  max-width: 850px;
  margin: 0 auto;
}

.eyebrow,
.result-label {
  display: inline-block;
  color: #16824d;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 1.5px;
  margin-bottom: 16px;
}

.hero h1 {
  margin: 0;
  font-size: clamp(36px, 5vw, 58px);
  line-height: 1.08;
  color: #123d30;
}

.hero p {
  max-width: 720px;
  margin: 22px auto 0;
  color: #63756f;
  font-size: 17px;
  line-height: 1.8;
}

.architecture {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 30px;
}

.architecture span {
  padding: 8px 14px;
  border: 1px solid #d7e9e0;
  border-radius: 999px;
  background: #ffffff;
  color: #345c50;
  font-size: 13px;
  font-weight: 700;
}

.architecture b {
  color: #7b9d92;
}

.content-container {
  width: min(1180px, calc(100% - 40px));
  margin: 0 auto;
  padding-bottom: 80px;
}

.ai-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.75fr);
  gap: 24px;
  align-items: start;
}

.question-card,
.info-card,
.loading-card,
.result-section {
  border: 1px solid #e1ebe6;
  background: #ffffff;
  border-radius: 22px;
  box-shadow: 0 15px 45px rgba(26, 74, 58, 0.07);
}

.question-card {
  padding: 30px;
}

.card-heading {
  display: flex;
  gap: 16px;
  margin-bottom: 26px;
}

.icon-box,
.info-icon {
  display: grid;
  place-items: center;
  width: 48px;
  height: 48px;
  flex: 0 0 48px;
  border-radius: 14px;
  background: #eaf8f1;
  font-size: 22px;
}

.card-heading h2,
.result-header h2 {
  margin: 0;
  color: #173d32;
}

.card-heading p,
.result-header p {
  margin: 7px 0 0;
  color: #71817c;
  line-height: 1.6;
}

form label {
  display: block;
  margin-bottom: 9px;
  color: #274e42;
  font-size: 14px;
  font-weight: 700;
}

textarea {
  width: 100%;
  box-sizing: border-box;
  resize: vertical;
  padding: 16px;
  border: 1px solid #d9e6e0;
  border-radius: 14px;
  background: #fbfdfc;
  color: #17352c;
  font: inherit;
  line-height: 1.6;
  outline: none;
  transition: 0.2s;
}

textarea:focus {
  border-color: #28a365;
  box-shadow: 0 0 0 4px rgba(40, 163, 101, 0.1);
}

textarea:disabled {
  opacity: 0.7;
}

.textarea-info {
  display: flex;
  justify-content: space-between;
  margin-top: 8px;
  color: #899791;
  font-size: 12px;
}

.analyze-button {
  width: 100%;
  min-height: 50px;
  margin-top: 18px;
  border: 0;
  border-radius: 14px;
  background: #168b52;
  color: white;
  font-weight: 800;
  font-size: 15px;
  cursor: pointer;
  transition: 0.2s;
}

.analyze-button:hover:not(:disabled) {
  background: #107443;
  transform: translateY(-1px);
}

.analyze-button:disabled {
  cursor: not-allowed;
  opacity: 0.55;
}

.spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  margin-right: 8px;
  border: 2px solid rgba(255,255,255,.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.error-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 14px;
  padding: 13px 15px;
  border: 1px solid #f2c9c9;
  border-radius: 12px;
  background: #fff5f5;
  color: #a63c3c;
  font-size: 13px;
}

.examples {
  margin-top: 30px;
  padding-top: 24px;
  border-top: 1px solid #edf2ef;
}

.examples-title {
  margin: 0 0 12px;
  font-size: 13px;
  font-weight: 800;
  color: #577068;
}

.example-button {
  display: flex;
  gap: 9px;
  width: 100%;
  margin-top: 9px;
  padding: 11px 13px;
  text-align: left;
  border: 1px solid #e1ebe6;
  border-radius: 11px;
  background: #fbfdfc;
  color: #526b63;
  cursor: pointer;
  line-height: 1.5;
}

.example-button:hover {
  border-color: #9ed2b7;
  background: #f2faf6;
}

.info-column {
  display: grid;
  gap: 18px;
}

.info-card {
  padding: 25px;
}

.info-card h3 {
  margin: 14px 0 22px;
}

.step {
  display: flex;
  gap: 12px;
  margin-top: 17px;
}

.step span {
  display: grid;
  place-items: center;
  width: 27px;
  height: 27px;
  flex: 0 0 27px;
  border-radius: 50%;
  background: #168b52;
  color: #fff;
  font-size: 12px;
  font-weight: 800;
}

.step p {
  margin: 3px 0 0;
  color: #63766f;
  line-height: 1.55;
  font-size: 14px;
}

.warning-card {
  padding: 20px;
  border: 1px solid #f0dfad;
  border-radius: 18px;
  background: #fffaf0;
  color: #755c1c;
}

.warning-card p {
  margin: 9px 0 0;
  font-size: 13px;
  line-height: 1.65;
}

.loading-card {
  margin-top: 26px;
  padding: 45px 25px;
  text-align: center;
}

.large-spinner {
  width: 42px;
  height: 42px;
  margin: 0 auto 18px;
  border: 4px solid #e0eee7;
  border-top-color: #168b52;
  border-radius: 50%;
  animation: spin 0.9s linear infinite;
}

.loading-card h3 {
  margin: 0;
}

.loading-card p {
  color: #74847e;
}

.result-section {
  margin-top: 28px;
  padding: 32px;
}

.result-header {
  display: flex;
  justify-content: space-between;
  gap: 25px;
  align-items: flex-start;
  padding-bottom: 25px;
  border-bottom: 1px solid #edf2ef;
}

.reset-button {
  border: 1px solid #cfe2d8;
  border-radius: 11px;
  padding: 10px 15px;
  background: #fff;
  color: #267451;
  font-weight: 700;
  cursor: pointer;
}

.analysis-summary {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-top: 25px;
}

.summary-item {
  padding: 17px;
  border: 1px solid #e4ede9;
  border-radius: 14px;
  background: #fbfdfc;
}

.summary-label {
  display: block;
  margin-bottom: 7px;
  color: #819089;
  font-size: 12px;
}

.level-badge,
.animal-badge {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 999px;
  font-size: 12px;
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

.summary-box {
  display: flex;
  gap: 15px;
  margin-top: 20px;
  padding: 20px;
  border-radius: 16px;
  background: #eff9f4;
}

.summary-icon {
  font-size: 22px;
}

.summary-box h3 {
  margin: 0 0 7px;
}

.summary-box p {
  margin: 0;
  color: #557067;
  line-height: 1.7;
}

.recommendation-heading {
  margin: 30px 0 16px;
}

.recommendation-heading h3 {
  margin: 0;
}

.recommendation-heading p {
  margin: 5px 0 0;
  color: #82908b;
}

.service-grid {
  display: grid;
  gap: 16px;
}

.service-card {
  position: relative;
  padding: 22px;
  border: 1px solid #e0ebe5;
  border-radius: 17px;
  background: #fff;
}

.service-number {
  position: absolute;
  top: -9px;
  left: -9px;
  display: grid;
  place-items: center;
  width: 29px;
  height: 29px;
  border-radius: 50%;
  background: #168b52;
  color: #fff;
  font-size: 12px;
  font-weight: 800;
}

.service-top {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}

.service-top h3 {
  margin: 9px 0 0;
}

.animal-badge {
  background: #eef8f3;
  color: #297354;
}

.price {
  white-space: nowrap;
  color: #168b52;
}

.description {
  color: #667a72;
  line-height: 1.65;
}

.duration {
  display: inline-block;
  margin-top: 2px;
  padding: 7px 10px;
  border-radius: 8px;
  background: #f3f6f5;
  color: #62736d;
  font-size: 12px;
}

.reason {
  margin-top: 17px;
  padding: 15px;
  border-left: 3px solid #39a86e;
  border-radius: 0 10px 10px 0;
  background: #f5faf7;
}

.reason strong {
  font-size: 13px;
}

.reason p {
  margin: 6px 0 0;
  color: #587067;
  line-height: 1.6;
  font-size: 13px;
}

.empty-recommendation {
  padding: 35px;
  text-align: center;
  border: 1px dashed #cadbd3;
  border-radius: 16px;
}

.empty-recommendation span {
  font-size: 32px;
}

.empty-recommendation h3 {
  margin-bottom: 5px;
}

.empty-recommendation p {
  color: #778a82;
}

.safety-note {
  display: flex;
  gap: 14px;
  margin-top: 22px;
  padding: 18px;
  border: 1px solid #f0d9aa;
  border-radius: 14px;
  background: #fffaf0;
  color: #725b24;
}

.safety-note p {
  margin: 6px 0 0;
  line-height: 1.6;
  font-size: 13px;
}

.ai-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 25px;
  margin-top: 22px;
  padding-top: 18px;
  border-top: 1px solid #edf2ef;
  color: #87958f;
  font-size: 11px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 850px) {
  .ai-grid {
    grid-template-columns: 1fr;
  }

  .result-header,
  .service-top {
    flex-direction: column;
  }

  .analysis-summary {
    grid-template-columns: 1fr;
  }

  .price {
    white-space: normal;
  }
}

@media (max-width: 520px) {
  .hero {
    padding: 55px 18px 40px;
  }

  .content-container {
    width: min(100% - 24px, 1180px);
  }

  .question-card,
  .result-section {
    padding: 20px;
  }
}

.irrelevant-box {
  margin-top: 22px;
  padding: 18px;
  border: 1px solid #d9e2e7;
  border-radius: 14px;
  background: #f7f9fa;
  color: #53646c;
}

.irrelevant-box strong {
  display: block;
  margin-bottom: 7px;
  color: #344a54;
}

.irrelevant-box p {
  margin: 0;
  line-height: 1.6;
  font-size: 14px;
}

/*
|--------------------------------------------------------------------------
| Pet Selector
|--------------------------------------------------------------------------
*/

.pet-selector {
  margin-bottom: 25px;
  padding: 20px;
  border: 1px solid #dcebe4;
  border-radius: 16px;
  background: #f7fcf9;
}

.pet-selector-header {
  margin-bottom: 14px;
}

.pet-selector-label {
  color: #1a6f49;
  font-size: 14px;
  font-weight: 800;
}

.pet-selector-header p {
  margin: 6px 0 0;
  color: #71827b;
  font-size: 13px;
  line-height: 1.6;
}


/*
|--------------------------------------------------------------------------
| Select Pet
|--------------------------------------------------------------------------
*/

.pet-select {
  width: 100%;
  min-height: 46px;
  padding: 0 14px;
  border: 1px solid #cfdfd7;
  border-radius: 11px;
  background: #ffffff;
  color: #294b40;
  font: inherit;
  outline: none;
  cursor: pointer;
}

.pet-select:focus {
  border-color: #28a365;
  box-shadow:
    0 0 0 4px
    rgba(40, 163, 101, 0.1);
}

.pet-select:disabled {
  cursor: not-allowed;
  opacity: 0.65;
}


/*
|--------------------------------------------------------------------------
| Selected Pet
|--------------------------------------------------------------------------
*/

.selected-pet-card {
  display: flex;
  align-items: center;
  gap: 13px;
  margin-top: 14px;
  padding: 14px;
  border: 1px solid #d5eadf;
  border-radius: 12px;
  background: #ffffff;
}

.selected-pet-icon {
  display: grid;
  place-items: center;
  width: 45px;
  height: 45px;
  flex: 0 0 45px;
  border-radius: 12px;
  background: #e9f7f0;
  font-size: 22px;
}

.selected-pet-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.selected-pet-info strong {
  color: #194b38;
}

.selected-pet-info span {
  margin-top: 2px;
  color: #60766d;
  font-size: 13px;
}

.selected-pet-info small {
  margin-top: 5px;
  color: #8a9993;
  line-height: 1.4;
}


/*
|--------------------------------------------------------------------------
| Pet Status
|--------------------------------------------------------------------------
*/

.pet-loading,
.pet-error,
.no-pets-info {
  padding: 12px;
  border-radius: 10px;
  font-size: 13px;
}

.pet-loading {
  background: #f3f7f5;
  color: #64766f;
}

.pet-error {
  border: 1px solid #f1cccc;
  background: #fff5f5;
  color: #a34242;
}

.no-pets-info {
  display: flex;
  gap: 10px;
  margin-top: 12px;
  border: 1px solid #dae5e0;
  background: #ffffff;
  color: #667871;
}

.no-pets-info p {
  margin: 0;
  line-height: 1.6;
}


/*
|--------------------------------------------------------------------------
| Analysis Mode
|--------------------------------------------------------------------------
*/

.analysis-mode {
  display: flex;
  gap: 9px;
  align-items: center;
  margin-bottom: 10px;
  padding: 9px 12px;
  border-radius: 9px;
  font-size: 12px;
}

.analysis-mode p {
  margin: 0;
}

.pet-mode {
  background: #eaf8f1;
  color: #28704f;
}

.general-mode {
  background: #f4f7f6;
  color: #697b74;
}


/*
|--------------------------------------------------------------------------
| Result Pet
|--------------------------------------------------------------------------
*/

.result-pet-card {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-top: 25px;
  padding: 18px;
  border: 1px solid #d7e9e0;
  border-radius: 15px;
  background:
    linear-gradient(
      135deg,
      #f2faf6,
      #ffffff
    );
}

.result-pet-icon {
  display: grid;
  place-items: center;
  width: 52px;
  height: 52px;
  flex: 0 0 52px;
  border-radius: 14px;
  background: #e4f5ec;
  font-size: 26px;
}

.result-pet-main {
  flex: 1;
}

.result-pet-main > span {
  color: #809089;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

.result-pet-main h3 {
  margin: 3px 0 7px;
  color: #194b38;
}

.result-pet-details {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.result-pet-details span {
  padding: 4px 8px;
  border-radius: 999px;
  background: #ffffff;
  border: 1px solid #dce9e3;
  color: #5c7269;
  font-size: 11px;
}

.database-badge {
  padding: 6px 10px;
  border-radius: 999px;
  background: #dff4e8;
  color: #197447;
  font-size: 11px;
  font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| Responsive Pet
|--------------------------------------------------------------------------
*/

@media (max-width: 600px) {
  .result-pet-card {
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .database-badge {
    margin-left: 67px;
  }
}

.health-details { margin-top: 24px; }
.health-urgency { padding: 22px; border-radius: 14px; border: 1px solid #f3d49d; background: #fffbeb; color: #713f12; }
.health-urgency h3 { margin: 0 0 10px; font-size: 20px; }
.health-urgency p { margin: 0 0 12px; line-height: 1.8; }
.health-urgency strong { display: block; line-height: 1.7; }
.urgency-urgent { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }
.urgency-monitor { background: #f0f9ff; border-color: #bae6fd; color: #075985; }
.health-section { margin: 24px 0; padding: 22px; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow-wrap: anywhere; }
.health-section h3 { margin: 0 0 12px; font-size: 19px; color: #17352c; }
.health-caption { margin: 0 0 18px; color: #63756f; line-height: 1.8; }
.health-cause-list { list-style: none; margin: 0; padding: 0; }
.health-cause-list li + li { margin-top: 18px; padding-top: 18px; border-top: 1px solid #edf2f7; }
.health-cause-list strong { color: #17352c; }
.health-cause-list p { margin: 7px 0 0; line-height: 1.8; color: #63756f; }
.health-list { margin: 0; padding-left: 24px; line-height: 1.8; color: #345c50; }
.health-list li + li { margin-top: 10px; }
@media (max-width: 600px) {
  .health-section, .health-urgency { padding: 18px; }
}

</style>
