<script setup>
import { onMounted, ref } from 'vue'

import PublicLayout from '../layouts/PublicLayout.vue'
import api from '../services/api'

import {
  authState,
  setAuth
} from '../services/auth'

const profile = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
})

const loading = ref(true)
const saving = ref(false)

const error = ref('')
const success = ref('')

const loadProfile = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get(
      '/customer/profile'
    )

    const data = response.data.data

    profile.value = {
      name: data.name || '',
      email: data.email || '',
      phone: data.phone || '',
      address: data.address || '',
    }

  } catch (err) {
    console.error(err)

    error.value =
      'Gagal mengambil data profil.'
  } finally {
    loading.value = false
  }
}

const updateProfile = async () => {
  saving.value = true

  error.value = ''
  success.value = ''

  try {
    const response = await api.put(
      '/customer/profile',
      {
        name: profile.value.name,
        phone: profile.value.phone,
        address: profile.value.address,
      }
    )

    const data = response.data.data

    /*
     * Update user pada authState agar
     * nama di Navbar ikut berubah.
     */
    const updatedUser = {
      ...authState.user,
      name: data.name,
    }

    setAuth(
      authState.token,
      updatedUser
    )

    success.value =
      'Profil berhasil diperbarui.'

  } catch (err) {
    console.error(err)

    if (err.response?.status === 422) {
      error.value =
        'Data profil tidak valid. Silakan periksa kembali.'
    } else {
      error.value =
        'Gagal memperbarui profil.'
    }

  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadProfile()
})
</script>

<template>
  <PublicLayout>

    <section class="page-header">

      <div class="header-container">

        <span class="badge">
          👤 Akun Customer
        </span>

        <h1>Profil Saya</h1>

        <p>
          Kelola informasi akun dan data pribadi Anda.
        </p>

      </div>

    </section>


    <section class="profile-section">

      <!-- LOADING -->

      <div
        v-if="loading"
        class="loading-state"
      >
        <div class="loader"></div>

        <p>
          Memuat profil...
        </p>
      </div>


      <!-- PROFILE -->

      <div
        v-else
        class="profile-grid"
      >

        <!-- SIDEBAR -->

        <aside class="profile-card">

          <div class="avatar">
            {{
              profile.name
                ?.charAt(0)
                .toUpperCase()
            }}
          </div>

          <h2>
            {{ profile.name }}
          </h2>

          <p>
            {{ profile.email }}
          </p>

          <span class="role">
            Customer
          </span>


          <div class="account-menu">

            <RouterLink
              to="/profile"
              class="menu active"
            >
              👤 Profil Saya
            </RouterLink>

            <RouterLink
              to="/my-pets"
              class="menu"
            >
              🐾 Hewan Saya
            </RouterLink>

          </div>

        </aside>


        <!-- FORM -->

        <div class="form-card">

          <div class="form-header">

            <div>
              <h2>Informasi Pribadi</h2>

              <p>
                Perbarui informasi profil Anda.
              </p>
            </div>

          </div>


          <div
            v-if="success"
            class="alert success"
          >
            ✓ {{ success }}
          </div>


          <div
            v-if="error"
            class="alert error"
          >
            ⚠️ {{ error }}
          </div>


          <form @submit.prevent="updateProfile">

            <div class="field">

              <label>Nama Lengkap</label>

              <input
                v-model="profile.name"
                type="text"
                required
              />

            </div>


            <div class="field">

              <label>Email</label>

              <input
                v-model="profile.email"
                type="email"
                disabled
              />

              <small>
                Email tidak dapat diubah dari halaman ini.
              </small>

            </div>


            <div class="field">

              <label>Nomor Telepon</label>

              <input
                v-model="profile.phone"
                type="text"
                placeholder="Contoh: 081234567890"
              />

            </div>


            <div class="field">

              <label>Alamat</label>

              <textarea
                v-model="profile.address"
                rows="5"
                placeholder="Masukkan alamat..."
              ></textarea>

            </div>


            <button
              type="submit"
              class="save-button"
              :disabled="saving"
            >
              {{
                saving
                  ? 'Menyimpan...'
                  : 'Simpan Perubahan'
              }}
            </button>

          </form>

        </div>

      </div>

    </section>

  </PublicLayout>
</template>

<style scoped>
.page-header {
  padding: 55px 24px;

  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #f8fafc
    );
}

.header-container {
  max-width: 1200px;
  margin: auto;
}

.badge {
  display: inline-block;

  padding: 7px 13px;

  border-radius: 30px;

  background: #dbeafe;
  color: #1d4ed8;

  font-size: 12px;
  font-weight: 600;
}

.header-container h1 {
  margin: 15px 0 8px;

  color: #111827;

  font-size: 34px;
}

.header-container p {
  margin: 0;

  color: #6b7280;
}


/* CONTENT */

.profile-section {
  max-width: 1200px;

  margin: auto;

  padding: 45px 24px 90px;
}

.profile-grid {
  display: grid;

  grid-template-columns: 280px 1fr;

  gap: 25px;

  align-items: start;
}


/* PROFILE CARD */

.profile-card {
  padding: 30px 22px;

  border: 1px solid #e5e7eb;

  border-radius: 16px;

  background: white;

  text-align: center;
}

.avatar {
  width: 75px;
  height: 75px;

  margin: auto;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: #2563eb;

  color: white;

  font-size: 28px;
  font-weight: 700;
}

.profile-card h2 {
  margin: 16px 0 5px;

  color: #111827;

  font-size: 18px;
}

.profile-card p {
  margin: 0;

  color: #6b7280;

  font-size: 12px;
}

.role {
  display: inline-block;

  margin-top: 12px;

  padding: 5px 11px;

  border-radius: 20px;

  background: #eff6ff;
  color: #2563eb;

  font-size: 10px;
  font-weight: 700;
}


/* MENU */

.account-menu {
  margin-top: 27px;

  padding-top: 20px;

  border-top: 1px solid #f3f4f6;

  display: flex;
  flex-direction: column;

  gap: 7px;

  text-align: left;
}

.menu {
  padding: 11px 13px;

  border-radius: 8px;

  color: #4b5563;

  text-decoration: none;

  font-size: 12px;
}

.menu:hover,
.menu.active {
  background: #eff6ff;
  color: #2563eb;
}


/* FORM */

.form-card {
  padding: 30px;

  border: 1px solid #e5e7eb;

  border-radius: 16px;

  background: white;
}

.form-header {
  margin-bottom: 27px;

  padding-bottom: 20px;

  border-bottom: 1px solid #f3f4f6;
}

.form-header h2 {
  margin: 0 0 5px;

  color: #111827;

  font-size: 20px;
}

.form-header p {
  margin: 0;

  color: #6b7280;

  font-size: 12px;
}

.field {
  margin-bottom: 20px;

  display: flex;
  flex-direction: column;

  gap: 7px;
}

.field label {
  color: #374151;

  font-size: 12px;
  font-weight: 600;
}

.field input,
.field textarea {
  padding: 12px 13px;

  border: 1px solid #d1d5db;

  border-radius: 9px;

  outline: none;

  font-family: inherit;
}

.field input:focus,
.field textarea:focus {
  border-color: #2563eb;
}

.field input:disabled {
  background: #f9fafb;

  color: #9ca3af;

  cursor: not-allowed;
}

.field small {
  color: #9ca3af;

  font-size: 10px;
}

.save-button {
  padding: 12px 20px;

  border: none;

  border-radius: 9px;

  background: #2563eb;

  color: white;

  font-weight: 600;

  cursor: pointer;
}

.save-button:disabled {
  opacity: 0.6;
}


/* ALERT */

.alert {
  margin-bottom: 20px;

  padding: 12px 14px;

  border-radius: 9px;

  font-size: 12px;
}

.success {
  background: #ecfdf5;
  color: #047857;
}

.error {
  background: #fef2f2;
  color: #b91c1c;
}


/* LOADING */

.loading-state {
  min-height: 350px;

  display: flex;
  flex-direction: column;

  align-items: center;
  justify-content: center;

  color: #6b7280;
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


@media (max-width: 800px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}
</style>