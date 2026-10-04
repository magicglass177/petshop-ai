<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

import PublicLayout from '../layouts/PublicLayout.vue'

import api from '../services/api'

import {
  setAuth
} from '../services/auth'

const router = useRouter()

const email = ref('')
const password = ref('')

const loading = ref(false)
const error = ref('')

const login = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.post(
      '/login',
      {
        email: email.value,
        password: password.value,
      }
    )

    const data = response.data.data

    setAuth(
      data.token,
      data.user
    )

    if (data.user.role === 'customer') {
  router.push('/customer/dashboard')
  return
}

    if (data.user.role === 'admin') {
  router.push('/admin/dashboard')
  return
}

  } catch (err) {
    console.error(err)

    if (err.response?.status === 401) {
      error.value =
        'Email atau password yang Anda masukkan salah.'
    } else if (err.response?.status === 422) {
      error.value =
        'Silakan periksa kembali email dan password.'
    } else {
      error.value =
        'Terjadi kesalahan saat login. Silakan coba kembali.'
    }

  } finally {
    loading.value = false
  }
}
</script>

<template>
  <PublicLayout>

    <section class="auth-page">

      <div class="auth-card">

        <div class="auth-icon">
          🐾
        </div>

        <h1>Selamat Datang</h1>

        <p class="subtitle">
          Masuk ke akun PetShop AI Anda.
        </p>


        <div
          v-if="error"
          class="alert-error"
        >
          ⚠️ {{ error }}
        </div>


        <form @submit.prevent="login">

          <div class="field">
            <label>Email</label>

            <input
              v-model="email"
              type="email"
              placeholder="nama@email.com"
              required
            />
          </div>


          <div class="field">
            <label>Password</label>

            <input
              v-model="password"
              type="password"
              placeholder="Masukkan password"
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
                ? 'Memproses...'
                : 'Masuk'
            }}
          </button>

        </form>


        <div class="auth-footer">

          Belum mempunyai akun?

          <RouterLink to="/register">
            Daftar sekarang
          </RouterLink>

        </div>


        <div class="guest-info">
          <span>ℹ️</span>

          <p>
            Anda tetap dapat melihat produk,
            layanan, dan menggunakan AI Pet
            Assistant tanpa login.
          </p>
        </div>

      </div>

    </section>

  </PublicLayout>
</template>

<style scoped>
.auth-page {
  min-height: 650px;

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
  max-width: 440px;

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
  align-items: center;
  justify-content: center;

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
  margin-bottom: 18px;

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

  box-shadow:
    0 0 0 3px rgba(37, 99, 235, 0.08);
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

.submit-button:hover {
  background: #1d4ed8;
}

.submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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
  margin-top: 24px;

  text-align: center;

  color: #6b7280;

  font-size: 13px;
}

.auth-footer a {
  color: #2563eb;

  font-weight: 600;

  text-decoration: none;
}

.guest-info {
  margin-top: 25px;

  padding: 13px;

  display: flex;

  gap: 9px;

  border-radius: 10px;

  background: #f8fafc;

  color: #6b7280;

  font-size: 11px;

  line-height: 1.6;
}

.guest-info p {
  margin: 0;
}
</style>