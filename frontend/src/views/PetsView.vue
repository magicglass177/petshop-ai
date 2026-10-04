<script setup>
import { onMounted, ref } from 'vue'

import PublicLayout from '../layouts/PublicLayout.vue'
import api from '../services/api'

const pets = ref([])

const loading = ref(true)
const saving = ref(false)

const error = ref('')
const success = ref('')

const showForm = ref(false)
const editingId = ref(null)

const form = ref({
  name: '',
  animal_type: 'Kucing',
  breed: '',
  gender: '',
  birth_date: '',
  weight: '',
  notes: '',
})


/*
|--------------------------------------------------------------------------
| LOAD PETS
|--------------------------------------------------------------------------
*/

const loadPets = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get(
      '/customer/pets'
    )

    pets.value = response.data.data

  } catch (err) {
    console.error(err)

    error.value =
      'Gagal mengambil data hewan.'

  } finally {
    loading.value = false
  }
}


/*
|--------------------------------------------------------------------------
| RESET FORM
|--------------------------------------------------------------------------
*/

const resetForm = () => {
  form.value = {
    name: '',
    animal_type: 'Kucing',
    breed: '',
    gender: '',
    birth_date: '',
    weight: '',
    notes: '',
  }

  editingId.value = null
}


/*
|--------------------------------------------------------------------------
| OPEN ADD
|--------------------------------------------------------------------------
*/

const openAddForm = () => {
  resetForm()

  success.value = ''
  error.value = ''

  showForm.value = true
}


/*
|--------------------------------------------------------------------------
| OPEN EDIT
|--------------------------------------------------------------------------
*/

const editPet = (pet) => {
  editingId.value = pet.id

  form.value = {
    name: pet.name || '',
    animal_type: pet.animal_type || 'Kucing',
    breed: pet.breed || '',
    gender: pet.gender || '',
    birth_date: pet.birth_date || '',
    weight: pet.weight || '',
    notes: pet.notes || '',
  }

  success.value = ''
  error.value = ''

  showForm.value = true

  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}


/*
|--------------------------------------------------------------------------
| CANCEL
|--------------------------------------------------------------------------
*/

const cancelForm = () => {
  showForm.value = false

  resetForm()
}


/*
|--------------------------------------------------------------------------
| SAVE
|--------------------------------------------------------------------------
*/

const savePet = async () => {
  saving.value = true

  error.value = ''
  success.value = ''

  const payload = {
    name: form.value.name,
    animal_type: form.value.animal_type,

    breed:
      form.value.breed || null,

    gender:
      form.value.gender || null,

    birth_date:
      form.value.birth_date || null,

    weight:
      form.value.weight === ''
        ? null
        : Number(form.value.weight),

    notes:
      form.value.notes || null,
  }

  try {

    if (editingId.value) {

      await api.put(
        `/customer/pets/${editingId.value}`,
        payload
      )

      success.value =
        'Data hewan berhasil diperbarui.'

    } else {

      await api.post(
        '/customer/pets',
        payload
      )

      success.value =
        'Hewan berhasil ditambahkan.'
    }

    showForm.value = false

    resetForm()

    await loadPets()

  } catch (err) {
    console.error(err)

    if (err.response?.status === 422) {

      const errors =
        err.response.data.errors

      if (errors) {
        const firstError =
          Object.values(errors)[0]?.[0]

        error.value =
          firstError ||
          'Data hewan tidak valid.'
      } else {
        error.value =
          'Data hewan tidak valid.'
      }

    } else {
      error.value =
        'Gagal menyimpan data hewan.'
    }

  } finally {
    saving.value = false
  }
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

const deletePet = async (pet) => {
  const confirmed = window.confirm(
    `Hapus data ${pet.name}?`
  )

  if (!confirmed) {
    return
  }

  error.value = ''
  success.value = ''

  try {

    await api.delete(
      `/customer/pets/${pet.id}`
    )

    success.value =
      `${pet.name} berhasil dihapus.`

    await loadPets()

  } catch (err) {
    console.error(err)

    error.value =
      'Gagal menghapus data hewan.'
  }
}


/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/

const animalIcon = (type) => {
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
  loadPets()
})
</script>

<template>
  <PublicLayout>

    <section class="page-header">

      <div class="header-container">

        <div>

          <span class="badge">
            🐾 Akun Customer
          </span>

          <h1>Hewan Saya</h1>

          <p>
            Kelola data hewan peliharaan Anda.
          </p>

        </div>


        <button
          class="add-button"
          @click="openAddForm"
        >
          + Tambah Hewan
        </button>

      </div>

    </section>


    <section class="pets-section">

      <!-- ALERT -->

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


      <!-- FORM -->

      <div
        v-if="showForm"
        class="form-card"
      >

        <div class="form-heading">

          <div>

            <h2>
              {{
                editingId
                  ? 'Edit Hewan'
                  : 'Tambah Hewan'
              }}
            </h2>

            <p>
              Masukkan informasi hewan peliharaan.
            </p>

          </div>


          <button
            class="close-button"
            @click="cancelForm"
          >
            ✕
          </button>

        </div>


        <form @submit.prevent="savePet">

          <div class="form-grid">

            <div class="field">

              <label>Nama Hewan *</label>

              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Mochi"
                required
              />

            </div>


            <div class="field">

              <label>Jenis Hewan *</label>

              <select
                v-model="form.animal_type"
                required
              >
                <option value="Kucing">
                  Kucing
                </option>

                <option value="Anjing">
                  Anjing
                </option>

                <option value="Burung">
                  Burung
                </option>

                <option value="Kelinci">
                  Kelinci
                </option>

                <option value="Lainnya">
                  Lainnya
                </option>
              </select>

            </div>


            <div class="field">

              <label>Ras</label>

              <input
                v-model="form.breed"
                type="text"
                placeholder="Contoh: Persia"
              />

            </div>


            <div class="field">

              <label>Jenis Kelamin</label>

              <select v-model="form.gender">

                <option value="">
                  Pilih
                </option>

                <option value="Jantan">
                  Jantan
                </option>

                <option value="Betina">
                  Betina
                </option>

              </select>

            </div>


            <div class="field">

              <label>Tanggal Lahir</label>

              <input
                v-model="form.birth_date"
                type="date"
              />

            </div>


            <div class="field">

              <label>Berat Badan (kg)</label>

              <input
                v-model="form.weight"
                type="number"
                min="0"
                step="0.01"
                placeholder="Contoh: 4.5"
              />

            </div>

          </div>


          <div class="field">

            <label>Catatan</label>

            <textarea
              v-model="form.notes"
              rows="4"
              placeholder="Catatan tambahan mengenai hewan..."
            ></textarea>

          </div>


          <div class="form-actions">

            <button
              type="button"
              class="cancel-button"
              @click="cancelForm"
            >
              Batal
            </button>

            <button
              type="submit"
              class="save-button"
              :disabled="saving"
            >
              {{
                saving
                  ? 'Menyimpan...'
                  : (
                    editingId
                      ? 'Simpan Perubahan'
                      : 'Tambah Hewan'
                  )
              }}
            </button>

          </div>

        </form>

      </div>


      <!-- ACCOUNT MENU -->

      <div class="account-navigation">

        <RouterLink to="/profile">
          👤 Profil Saya
        </RouterLink>

        <RouterLink
          to="/my-pets"
          class="active"
        >
          🐾 Hewan Saya
        </RouterLink>

      </div>


      <!-- LOADING -->

      <div
        v-if="loading"
        class="state"
      >

        <div class="loader"></div>

        <p>
          Memuat data hewan...
        </p>

      </div>


      <!-- EMPTY -->

      <div
        v-else-if="pets.length === 0"
        class="state"
      >

        <div class="state-icon">
          🐾
        </div>

        <h3>
          Belum ada hewan
        </h3>

        <p>
          Tambahkan data hewan peliharaan
          agar nantinya dapat digunakan
          bersama AI Pet Assistant.
        </p>

        <button @click="openAddForm">
          + Tambah Hewan Pertama
        </button>

      </div>


      <!-- PET GRID -->

      <div
        v-else
        class="pet-grid"
      >

        <article
          v-for="pet in pets"
          :key="pet.id"
          class="pet-card"
        >

          <div class="pet-top">

            <div class="pet-icon">
              {{ animalIcon(pet.animal_type) }}
            </div>

            <span class="animal-type">
              {{ pet.animal_type }}
            </span>

          </div>


          <div class="pet-content">

            <h2>
              {{ pet.name }}
            </h2>

            <p class="breed">
              {{ pet.breed || 'Ras tidak dicantumkan' }}
            </p>


            <div class="pet-information">

              <div>
                <span>Jenis Kelamin</span>

                <strong>
                  {{ pet.gender || '-' }}
                </strong>
              </div>


              <div>
                <span>Berat</span>

                <strong>
                  {{
                    pet.weight
                      ? `${pet.weight} kg`
                      : '-'
                  }}
                </strong>
              </div>


              <div>
                <span>Tanggal Lahir</span>

                <strong>
                  {{ pet.birth_date || '-' }}
                </strong>
              </div>

            </div>


            <div
              v-if="pet.notes"
              class="notes"
            >
              <span>Catatan</span>

              <p>
                {{ pet.notes }}
              </p>
            </div>


            <div class="pet-actions">

              <button
                class="edit-button"
                @click="editPet(pet)"
              >
                ✏️ Edit
              </button>

              <button
                class="delete-button"
                @click="deletePet(pet)"
              >
                🗑️ Hapus
              </button>

            </div>

          </div>

        </article>

      </div>

    </section>

  </PublicLayout>
</template>

<style scoped>
.page-header {
  padding: 50px 24px;

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

  display: flex;

  align-items: center;
  justify-content: space-between;

  gap: 20px;
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
  margin: 15px 0 7px;

  color: #111827;

  font-size: 34px;
}

.header-container p {
  margin: 0;

  color: #6b7280;
}

.add-button {
  padding: 12px 18px;

  border: none;

  border-radius: 9px;

  background: #2563eb;

  color: white;

  font-weight: 600;

  cursor: pointer;
}


/* SECTION */

.pets-section {
  max-width: 1200px;

  margin: auto;

  padding: 40px 24px 90px;
}


/* NAV */

.account-navigation {
  margin-bottom: 25px;

  display: flex;

  gap: 8px;
}

.account-navigation a {
  padding: 10px 15px;

  border-radius: 8px;

  color: #4b5563;

  text-decoration: none;

  font-size: 12px;
  font-weight: 600;
}

.account-navigation a:hover,
.account-navigation a.active {
  background: #eff6ff;
  color: #2563eb;
}


/* FORM */

.form-card {
  margin-bottom: 30px;

  padding: 27px;

  border: 1px solid #bfdbfe;

  border-radius: 16px;

  background: white;

  box-shadow:
    0 10px 30px rgba(0, 0, 0, 0.04);
}

.form-heading {
  margin-bottom: 25px;

  padding-bottom: 18px;

  border-bottom: 1px solid #f3f4f6;

  display: flex;

  justify-content: space-between;
}

.form-heading h2 {
  margin: 0 0 5px;

  color: #111827;
}

.form-heading p {
  margin: 0;

  color: #6b7280;

  font-size: 12px;
}

.close-button {
  border: none;

  background: transparent;

  color: #9ca3af;

  cursor: pointer;
}

.form-grid {
  display: grid;

  grid-template-columns: 1fr 1fr;

  gap: 17px;
}

.field {
  margin-bottom: 17px;

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
.field select,
.field textarea {
  padding: 11px 12px;

  border: 1px solid #d1d5db;

  border-radius: 8px;

  outline: none;

  font-family: inherit;

  background: white;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
  border-color: #2563eb;
}

.form-actions {
  display: flex;

  justify-content: flex-end;

  gap: 9px;
}

.cancel-button,
.save-button {
  padding: 11px 17px;

  border-radius: 8px;

  font-weight: 600;

  cursor: pointer;
}

.cancel-button {
  border: 1px solid #d1d5db;

  background: white;

  color: #4b5563;
}

.save-button {
  border: none;

  background: #2563eb;

  color: white;
}


/* PET GRID */

.pet-grid {
  display: grid;

  grid-template-columns:
    repeat(3, minmax(0, 1fr));

  gap: 22px;
}

.pet-card {
  overflow: hidden;

  border: 1px solid #e5e7eb;

  border-radius: 16px;

  background: white;

  transition:
    transform 0.2s,
    box-shadow 0.2s;
}

.pet-card:hover {
  transform: translateY(-3px);

  box-shadow:
    0 15px 30px rgba(0, 0, 0, 0.07);
}

.pet-top {
  min-height: 130px;

  padding: 22px;

  display: flex;

  justify-content: space-between;
  align-items: flex-start;

  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #f5f3ff
    );
}

.pet-icon {
  font-size: 55px;
}

.animal-type {
  padding: 6px 10px;

  border-radius: 20px;

  background: white;

  color: #2563eb;

  font-size: 10px;
  font-weight: 700;
}

.pet-content {
  padding: 21px;
}

.pet-content h2 {
  margin: 0;

  color: #111827;

  font-size: 20px;
}

.breed {
  margin: 5px 0 20px;

  color: #6b7280;

  font-size: 12px;
}

.pet-information {
  display: grid;

  grid-template-columns: 1fr 1fr;

  gap: 13px;
}

.pet-information div {
  display: flex;

  flex-direction: column;

  gap: 4px;
}

.pet-information span,
.notes span {
  color: #9ca3af;

  font-size: 9px;
  text-transform: uppercase;
}

.pet-information strong {
  color: #374151;

  font-size: 12px;
}

.notes {
  margin-top: 17px;

  padding: 12px;

  border-radius: 8px;

  background: #f9fafb;
}

.notes p {
  margin: 5px 0 0;

  color: #6b7280;

  font-size: 11px;

  line-height: 1.5;
}

.pet-actions {
  margin-top: 20px;

  padding-top: 15px;

  border-top: 1px solid #f3f4f6;

  display: grid;

  grid-template-columns: 1fr 1fr;

  gap: 8px;
}

.pet-actions button {
  padding: 9px;

  border-radius: 7px;

  font-size: 11px;
  font-weight: 600;

  cursor: pointer;
}

.edit-button {
  border: 1px solid #bfdbfe;

  background: #eff6ff;

  color: #2563eb;
}

.delete-button {
  border: 1px solid #fecaca;

  background: #fef2f2;

  color: #dc2626;
}


/* ALERT */

.alert {
  margin-bottom: 20px;

  padding: 12px 15px;

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


/* STATES */

.state {
  min-height: 350px;

  display: flex;
  flex-direction: column;

  align-items: center;
  justify-content: center;

  border: 1px dashed #d1d5db;

  border-radius: 15px;

  text-align: center;

  color: #6b7280;
}

.state-icon {
  font-size: 50px;
}

.state h3 {
  margin-bottom: 5px;

  color: #111827;
}

.state p {
  max-width: 400px;

  font-size: 12px;

  line-height: 1.7;
}

.state button {
  margin-top: 10px;

  padding: 11px 17px;

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


@media (max-width: 900px) {
  .pet-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 650px) {
  .pet-grid,
  .form-grid {
    grid-template-columns: 1fr;
  }

  .header-container {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>