<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

import PublicLayout from '../layouts/PublicLayout.vue'

import api from '../services/api'

import {
  setAuth
} from '../services/auth'

const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const error = ref('')
const validationErrors = ref({})

const register = async () => {
  loading.value = true
  error.value = ''
  validationErrors.value = {}

  if (
    form.value.password !==
    form.value.password_confirmation
  ) {
    error.value =
      'Konfirmasi password tidak sama.'

    loading.value = false
    return
  }

  try {
    const response = await api.post(
      '/register',
      form.value
    )

    const data = response.data.data

    setAuth(
      data.token,
      data.user
    )

    router.push('/customer/dashboard')

  } catch (err) {
    console.error(err)

    if (err.response?.status === 422) {
      validationErrors.value =
        err.response.data.errors || {}

      error.value =
        'Silakan periksa kembali data yang Anda masukkan.'
    } else {
      error.value =
        'Registrasi gagal. Silakan coba kembali.'
    }

  } finally {
    loading.value = false
  }
}

const getError = (field) => {
  return validationErrors.value[field]?.[0]
}
</script>

<template>
  <PublicLayout>

    <section class="auth-page">

      <div class="auth-card">

        <div class="auth-icon">
          🐾
        </div>

        <h1>Buat Akun</h1>

        <p class="subtitle">
          Daftar untuk mendapatkan fitur
          tambahan PetShop AI.
        </p>


        <div
          v-if="error"
          class="alert-error"
        >
          ⚠️ {{ error }}
        </div>


        <form @submit.prevent="register">

          <div class="field">

            <label>Nama Lengkap</label>

            <input
              v-model="form.name"
              type="text"
              placeholder="Masukkan nama"
              required
            />

            <small v-if="getError('name')">
              {{ getError('name') }}
            </small>

          </div>


          <div class="field">

            <label>Email</label>

            <input
              v-model="form.email"
              type="email"
              placeholder="nama@email.com"
              required
            />

            <small v-if="getError('email')">
              {{ getError('email') }}
            </small>

          </div>


          <div class="field">

            <label>Password</label>

            <input
              v-model="form.password"
              type="password"
              placeholder="Minimal 8 karakter"
              minlength="8"
              required
            />

            <small v-if="getError('password')">
              {{ getError('password') }}
            </small>

          </div>


          <div class="field">

            <label>Konfirmasi Password</label>

            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Ulangi password"
              minlength="8"
              required
            />

          </div>


          <button
            type="submit"
            class="submit-button"
            :disabled="loading"
          >
            {{
              loading
                ? 'Mendaftarkan...'
                : 'Daftar'
            }}
          </button>

        </form>


        <div class="auth-footer">

          Sudah mempunyai akun?

          <RouterLink to="/login">
            Masuk
          </RouterLink>

        </div>


        <div class="benefit-box">

          <strong>
            Dengan membuat akun Anda dapat:
          </strong>

          <ul>
            <li>🐾 Menyimpan data hewan peliharaan</li>
            <li>✨ Menyimpan riwayat AI Pet Assistant</li>
            <li>🧾 Melihat riwayat transaksi</li>
            <li>👤 Mengelola profil pribadi</li>
          </ul>

        </div>

      </div>

    </section>

  </PublicLayout>
</template>

<style scoped>
.auth-page {
  min-height: 700px;

  padding: 70px 20px;

  display: flex;
  justify-content: center;
  align-items: flex-start;

  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #ffffff,
      #f5f3ff
    );
}

.auth-card {
  width: 100%;
  max-width: 460px;

  padding: 38px;

  background: white;

  border: 1px solid #e5e7eb;

  border-radius: 20px;

  box-shadow:
    0 20px 50px rgba(0, 0, 0, 0.07);
}

.auth-icon {
  width: 55px;
  height: 55px;

  margin: auto;

  display: flex;
  justify-content: center;
  align-items: center;

  border-radius: 15px;

  background: #eff6ff;

  font-size: 27px;
}

.auth-card h1 {
  margin: 20px 0 7px;

  text-align: center;

  color: #111827;

  font-size: 27px;
}

.subtitle {
  margin: 0 0 30px;

  text-align: center;

  color: #6b7280;

  font-size: 14px;
}

.field {
  margin-bottom: 17px;

  display: flex;
  flex-direction: column;

  gap: 7px;
}

.field label {
  color: #374151;

  font-size: 13px;
  font-weight: 600;
}

.field input {
  height: 46px;

  padding: 0 14px;

  border: 1px solid #d1d5db;

  border-radius: 9px;

  outline: none;
}

.field input:focus {
  border-color: #2563eb;
}

.field small {
  color: #dc2626;

  font-size: 11px;
}

.submit-button {
  width: 100%;
  height: 46px;

  margin-top: 5px;

  border: none;

  border-radius: 9px;

  background: #2563eb;

  color: white;

  font-weight: 600;

  cursor: pointer;
}

.submit-button:disabled {
  opacity: 0.6;
}

.alert-error {
  margin-bottom: 20px;

  padding: 12px 14px;

  border-radius: 9px;

  background: #fef2f2;
  color: #b91c1c;

  font-size: 13px;
}

.auth-footer {
  margin-top: 23px;

  text-align: center;

  color: #6b7280;

  font-size: 13px;
}

.auth-footer a {
  color: #2563eb;

  font-weight: 600;

  text-decoration: none;
}

.benefit-box {
  margin-top: 25px;

  padding: 16px;

  border-radius: 11px;

  background: #f8fafc;

  color: #4b5563;

  font-size: 12px;
}

.benefit-box ul {
  margin: 10px 0 0;

  padding-left: 5px;

  list-style: none;

  line-height: 2;
}
</style>