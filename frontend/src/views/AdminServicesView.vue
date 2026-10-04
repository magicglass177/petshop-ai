<script setup>
import {
  computed,
  onMounted,
  ref
} from 'vue'

import PublicLayout from '../layouts/PublicLayout.vue'
import api from '../services/api'


/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const services = ref([])

const loading = ref(true)
const saving = ref(false)

const error = ref('')
const success = ref('')

const search = ref('')
const animalFilter = ref('')
const statusFilter = ref('')

const showForm = ref(false)
const editingId = ref(null)


const emptyForm = () => ({
  name: '',
  animal_type: '',
  description: '',
  price: '',
  duration: '',
  is_active: true,
})


const form = ref(
  emptyForm()
)


/*
|--------------------------------------------------------------------------
| COMPUTED
|--------------------------------------------------------------------------
*/

const filteredServices = computed(() => {

  const keyword =
    search.value
      .trim()
      .toLowerCase()


  return services.value.filter(
    (service) => {

      const matchesSearch =
        !keyword ||

        service.name
          ?.toLowerCase()
          .includes(keyword) ||

        service.description
          ?.toLowerCase()
          .includes(keyword) ||

        service.animal_type
          ?.toLowerCase()
          .includes(keyword)


      const matchesAnimal =
        !animalFilter.value ||

        service.animal_type ===
          animalFilter.value


      const matchesStatus =
        !statusFilter.value ||

        (
          statusFilter.value ===
            'active' &&
          service.is_active
        ) ||

        (
          statusFilter.value ===
            'inactive' &&
          !service.is_active
        )


      return (
        matchesSearch &&
        matchesAnimal &&
        matchesStatus
      )
    }
  )
})


const activeServices = computed(() => {

  return services.value.filter(
    service => service.is_active
  ).length

})


const inactiveServices = computed(() => {

  return services.value.filter(
    service => !service.is_active
  ).length

})


/*
|--------------------------------------------------------------------------
| FORMAT
|--------------------------------------------------------------------------
*/

const formatRupiah = (value) => {

  return new Intl.NumberFormat(
    'id-ID',
    {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0,
    }
  ).format(
    Number(value || 0)
  )
}


const formatDuration = (duration) => {

  if (!duration) {
    return '-'
  }

  return `${duration} menit`
}


const animalIcon = (type) => {

  if (type === 'Kucing') {
    return '🐱'
  }

  if (type === 'Anjing') {
    return '🐶'
  }

  return '🐾'
}


/*
|--------------------------------------------------------------------------
| LOAD SERVICES
|--------------------------------------------------------------------------
*/

const loadServices = async () => {

  loading.value = true
  error.value = ''

  try {

    const response =
      await api.get(
        '/admin/services'
      )

    services.value =
      response.data.data

  } catch (err) {

    console.error(err)

    error.value =
      'Data layanan gagal dimuat.'

  } finally {

    loading.value = false
  }
}


/*
|--------------------------------------------------------------------------
| ADD
|--------------------------------------------------------------------------
*/

const openAddForm = () => {

  error.value = ''
  success.value = ''

  editingId.value = null

  form.value =
    emptyForm()

  showForm.value = true
}


/*
|--------------------------------------------------------------------------
| EDIT
|--------------------------------------------------------------------------
*/

const openEditForm = (service) => {

  error.value = ''
  success.value = ''

  editingId.value =
    service.id

  form.value = {

    name:
      service.name || '',

    animal_type:
      service.animal_type || '',

    description:
      service.description || '',

    price:
      service.price || '',

    duration:
      service.duration ?? '',

    is_active:
      Boolean(
        service.is_active
      ),
  }

  showForm.value = true

  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}


/*
|--------------------------------------------------------------------------
| CLOSE FORM
|--------------------------------------------------------------------------
*/

const closeForm = () => {

  editingId.value = null

  form.value =
    emptyForm()

  showForm.value = false
}


/*
|--------------------------------------------------------------------------
| SAVE
|--------------------------------------------------------------------------
*/

const saveService = async () => {

  error.value = ''
  success.value = ''


  if (!form.value.name.trim()) {

    error.value =
      'Nama layanan wajib diisi.'

    return
  }


  if (!form.value.animal_type) {

    error.value =
      'Jenis hewan wajib dipilih.'

    return
  }


  if (
    form.value.price === '' ||
    Number(form.value.price) < 0
  ) {

    error.value =
      'Harga layanan tidak valid.'

    return
  }


  if (
    form.value.duration !== '' &&
    Number(form.value.duration) < 1
  ) {

    error.value =
      'Durasi layanan tidak valid.'

    return
  }


  saving.value = true


  const payload = {

    name:
      form.value.name.trim(),

    animal_type:
      form.value.animal_type,

    description:
      form.value.description.trim()
        || null,

    price:
      Number(
        form.value.price
      ),

    duration:
      form.value.duration === ''
        ? null
        : Number(
            form.value.duration
          ),

    is_active:
      Boolean(
        form.value.is_active
      ),
  }


  try {

    if (editingId.value) {

      await api.put(
        `/admin/services/${editingId.value}`,
        payload
      )

      success.value =
        'Layanan berhasil diperbarui.'

    } else {

      await api.post(
        '/admin/services',
        payload
      )

      success.value =
        'Layanan berhasil ditambahkan.'
    }


    closeForm()

    await loadServices()


  } catch (err) {

    console.error(err)


    if (
      err.response?.status === 422
    ) {

      const validationErrors =
        err.response?.data?.errors


      if (validationErrors) {

        error.value =
          Object.values(
            validationErrors
          )
            .flat()[0] ||
          'Data layanan tidak valid.'

      } else {

        error.value =
          err.response?.data?.message ||
          'Data layanan tidak valid.'
      }

    } else if (
      err.response?.status === 403
    ) {

      error.value =
        'Anda tidak memiliki akses untuk mengelola layanan.'

    } else {

      error.value =
        'Terjadi kesalahan saat menyimpan layanan.'
    }

  } finally {

    saving.value = false
  }
}


/*
|--------------------------------------------------------------------------
| TOGGLE STATUS
|--------------------------------------------------------------------------
*/

const toggleStatus = async (service) => {

  error.value = ''
  success.value = ''


  const newStatus =
    !service.is_active


  const payload = {

    name:
      service.name,

    animal_type:
      service.animal_type,

    description:
      service.description,

    price:
      Number(service.price),

    duration:
      service.duration
        ? Number(service.duration)
        : null,

    is_active:
      newStatus,
  }


  try {

    await api.put(
      `/admin/services/${service.id}`,
      payload
    )


    success.value =
      newStatus
        ? 'Layanan berhasil diaktifkan.'
        : 'Layanan berhasil dinonaktifkan.'


    await loadServices()


  } catch (err) {

    console.error(err)

    error.value =
      err.response?.data?.message ||
      'Status layanan gagal diubah.'
  }
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

const deleteService = async (service) => {

  error.value = ''
  success.value = ''


  const confirmed =
    window.confirm(
      `Hapus layanan "${service.name}"?`
    )


  if (!confirmed) {
    return
  }


  try {

    await api.delete(
      `/admin/services/${service.id}`
    )


    success.value =
      'Layanan berhasil dihapus.'


    await loadServices()


  } catch (err) {

    console.error(err)

    error.value =
      err.response?.data?.message ||
      'Layanan gagal dihapus.'
  }
}


/*
|--------------------------------------------------------------------------
| MOUNT
|--------------------------------------------------------------------------
*/

onMounted(() => {
  loadServices()
})
</script>


<template>

  <PublicLayout>

    <main class="admin-page">

      <!-- ===================================================
           HEADER
      ==================================================== -->

      <section class="page-header">

        <div>

          <RouterLink
            to="/admin/dashboard"
            class="back-link"
          >
            ← Dashboard Admin
          </RouterLink>

          <span class="page-badge">
            ✂️ KELOLA LAYANAN
          </span>

          <h1>
            Layanan Pet Shop
          </h1>

          <p>
            Kelola katalog informasi layanan
            perawatan hewan yang tersedia
            pada PetShop AI.
          </p>

        </div>


        <button
          class="primary-button"
          @click="openAddForm"
        >
          + Tambah Layanan
        </button>

      </section>



      <!-- ===================================================
           ALERT
      ==================================================== -->

      <div
        v-if="success"
        class="alert success-alert"
      >
        ✓ {{ success }}
      </div>


      <div
        v-if="error"
        class="alert error-alert"
      >
        ⚠ {{ error }}
      </div>



      <!-- ===================================================
           FORM
      ==================================================== -->

      <section
        v-if="showForm"
        class="form-card"
      >

        <div class="form-heading">

          <div>

            <h2>
              {{
                editingId
                  ? '✏️ Edit Layanan'
                  : '➕ Tambah Layanan'
              }}
            </h2>

            <p>
              Isi informasi layanan
              perawatan hewan.
            </p>

          </div>


          <button
            type="button"
            class="close-button"
            @click="closeForm"
          >
            ✕
          </button>

        </div>


        <form
          @submit.prevent="saveService"
        >

          <div class="form-grid">

            <!-- NAME -->

            <div class="form-group">

              <label>
                Nama Layanan *
              </label>

              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Full Grooming Kucing"
                required
              />

            </div>


            <!-- ANIMAL -->

            <div class="form-group">

              <label>
                Jenis Hewan *
              </label>

              <select
                v-model="form.animal_type"
                required
              >

                <option value="">
                  Pilih jenis hewan
                </option>

                <option value="Kucing">
                  🐱 Kucing
                </option>

                <option value="Anjing">
                  🐶 Anjing
                </option>

                <option value="Umum">
                  🐾 Umum
                </option>

              </select>

            </div>


            <!-- PRICE -->

            <div class="form-group">

              <label>
                Harga *
              </label>

              <input
                v-model="form.price"
                type="number"
                min="0"
                step="1"
                placeholder="100000"
                required
              />

            </div>


            <!-- DURATION -->

            <div class="form-group">

              <label>
                Durasi
              </label>

              <input
                v-model="form.duration"
                type="number"
                min="1"
                step="1"
                placeholder="90"
              />

              <small>
                Durasi dalam menit.
              </small>

            </div>


            <!-- DESCRIPTION -->

            <div class="form-group full">

              <label>
                Deskripsi
              </label>

              <textarea
                v-model="form.description"
                rows="5"
                placeholder="Jelaskan layanan yang diberikan..."
              ></textarea>

            </div>


            <!-- ACTIVE -->

            <div class="form-group full">

              <label class="checkbox-row">

                <input
                  v-model="form.is_active"
                  type="checkbox"
                />

                <span>

                  <strong>
                    Layanan Aktif
                  </strong>

                  <small>
                    Layanan aktif dapat dilihat
                    pengguna dan nantinya dapat
                    digunakan sebagai konteks
                    rekomendasi AI.
                  </small>

                </span>

              </label>

            </div>

          </div>


          <div class="form-actions">

            <button
              type="button"
              class="secondary-button"
              @click="closeForm"
            >
              Batal
            </button>


            <button
              type="submit"
              class="primary-button"
              :disabled="saving"
            >
              {{
                saving
                  ? 'Menyimpan...'
                  : editingId
                    ? 'Simpan Perubahan'
                    : 'Tambah Layanan'
              }}
            </button>

          </div>

        </form>

      </section>



      <!-- ===================================================
           STATISTICS
      ==================================================== -->

      <section class="statistics">

        <div class="stat-card">

          <span>
            ✂️ Total Layanan
          </span>

          <strong>
            {{ services.length }}
          </strong>

        </div>


        <div class="stat-card">

          <span>
            🟢 Aktif
          </span>

          <strong>
            {{ activeServices }}
          </strong>

        </div>


        <div class="stat-card">

          <span>
            ⚪ Nonaktif
          </span>

          <strong>
            {{ inactiveServices }}
          </strong>

        </div>

      </section>



      <!-- ===================================================
           CONTENT
      ==================================================== -->

      <section class="content-card">

        <div class="toolbar">

          <div>

            <h2>
              Daftar Layanan
            </h2>

            <p>
              Kelola layanan aktif dan
              nonaktif.
            </p>

          </div>


          <div class="filters">

            <input
              v-model="search"
              type="text"
              placeholder="🔍 Cari layanan..."
            />


            <select
              v-model="animalFilter"
            >

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


            <select
              v-model="statusFilter"
            >

              <option value="">
                Semua Status
              </option>

              <option value="active">
                Aktif
              </option>

              <option value="inactive">
                Nonaktif
              </option>

            </select>

          </div>

        </div>



        <!-- LOADING -->

        <div
          v-if="loading"
          class="state"
        >

          <div class="loader"></div>

          <p>
            Memuat layanan...
          </p>

        </div>



        <!-- EMPTY -->

        <div
          v-else-if="
            filteredServices.length === 0
          "
          class="state"
        >

          <div class="empty-icon">
            ✂️
          </div>

          <h3>
            Layanan tidak ditemukan
          </h3>

          <p>
            Belum ada layanan atau data
            tidak sesuai dengan filter.
          </p>

        </div>



        <!-- TABLE -->

        <div
          v-else
          class="table-wrapper"
        >

          <table>

            <thead>

              <tr>
                <th>Layanan</th>
                <th>Hewan</th>
                <th>Harga</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>

            </thead>


            <tbody>

              <tr
                v-for="
                  service in filteredServices
                "
                :key="service.id"
              >

                <!-- SERVICE -->

                <td>

                  <div class="service-info">

                    <div class="service-icon">
                      ✂️
                    </div>

                    <div>

                      <strong>
                        {{ service.name }}
                      </strong>

                      <small>
                        ID: {{ service.id }}
                      </small>

                    </div>

                  </div>

                </td>


                <!-- ANIMAL -->

                <td>

                  <span class="animal-badge">

                    {{
                      animalIcon(
                        service.animal_type
                      )
                    }}

                    {{
                      service.animal_type
                    }}

                  </span>

                </td>


                <!-- PRICE -->

                <td class="price">

                  {{
                    formatRupiah(
                      service.price
                    )
                  }}

                </td>


                <!-- DURATION -->

                <td>

                  {{
                    formatDuration(
                      service.duration
                    )
                  }}

                </td>


                <!-- STATUS -->

                <td>

                  <button
                    class="status-button"
                    :class="
                      service.is_active
                        ? 'active'
                        : 'inactive'
                    "
                    @click="
                      toggleStatus(service)
                    "
                  >

                    {{
                      service.is_active
                        ? '● Aktif'
                        : '○ Nonaktif'
                    }}

                  </button>

                </td>


                <!-- ACTION -->

                <td>

                  <div class="table-actions">

                    <button
                      class="edit-button"
                      @click="
                        openEditForm(service)
                      "
                    >
                      ✏️ Edit
                    </button>


                    <button
                      class="delete-button"
                      @click="
                        deleteService(service)
                      "
                    >
                      🗑️ Hapus
                    </button>

                  </div>

                </td>

              </tr>

            </tbody>

          </table>

        </div>

      </section>



      <!-- ===================================================
           AI INFORMATION
      ==================================================== -->

      <section class="ai-info">

        <div class="ai-info-icon">
          🤖
        </div>

        <div>

          <span>
            PERSIAPAN AI PET ASSISTANT
          </span>

          <h3>
            Data Layanan akan Digunakan AI
          </h3>

          <p>
            Nantinya Laravel hanya akan
            mengirim layanan berstatus aktif
            kepada AI Gateway. Dengan demikian,
            AI Pet Assistant memberikan
            rekomendasi berdasarkan layanan
            yang benar-benar tersedia pada
            sistem PetShop AI.
          </p>

        </div>

      </section>

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

.page-header {
  max-width: 1200px;
  margin: auto;
  padding: 30px;

  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 25px;

  border-radius: 18px;

  background:
    linear-gradient(
      135deg,
      #111827,
      #1e293b
    );

  color: white;
}

.back-link {
  display: block;
  width: fit-content;

  margin-bottom: 15px;

  color: #93c5fd;

  text-decoration: none;
  font-size: 10px;
  font-weight: 600;
}

.page-badge {
  display: inline-block;

  padding: 5px 10px;

  border-radius: 20px;

  background:
    rgba(255,255,255,0.1);

  color: #bfdbfe;

  font-size: 8px;
  font-weight: 800;

  letter-spacing: 1px;
}

.page-header h1 {
  margin: 10px 0 6px;

  font-size: 28px;
}

.page-header p {
  max-width: 600px;

  margin: 0;

  color: #cbd5e1;

  font-size: 11px;

  line-height: 1.7;
}


/* BUTTONS */

.primary-button,
.secondary-button {
  padding: 11px 16px;

  border-radius: 8px;

  font-size: 10px;
  font-weight: 700;

  cursor: pointer;
}

.primary-button {
  border: none;

  background: #2563eb;

  color: white;
}

.primary-button:hover {
  background: #1d4ed8;
}

.primary-button:disabled {
  opacity: 0.6;

  cursor: not-allowed;
}

.secondary-button {
  border: 1px solid #d1d5db;

  background: white;

  color: #4b5563;
}


/* ALERT */

.alert {
  max-width: 1200px;

  margin: 18px auto 0;

  padding: 13px 15px;

  box-sizing: border-box;

  border-radius: 9px;

  font-size: 10px;
}

.success-alert {
  border: 1px solid #bbf7d0;

  background: #f0fdf4;

  color: #166534;
}

.error-alert {
  border: 1px solid #fecaca;

  background: #fef2f2;

  color: #b91c1c;
}


/* FORM */

.form-card {
  max-width: 1200px;

  margin: 20px auto;

  padding: 25px;

  box-sizing: border-box;

  border: 1px solid #dbeafe;

  border-radius: 15px;

  background: white;
}

.form-heading {
  margin-bottom: 22px;

  padding-bottom: 15px;

  display: flex;

  align-items: center;

  justify-content: space-between;

  border-bottom:
    1px solid #f3f4f6;
}

.form-heading h2 {
  margin: 0;

  color: #111827;

  font-size: 16px;
}

.form-heading p {
  margin: 4px 0 0;

  color: #9ca3af;

  font-size: 9px;
}

.close-button {
  border: none;

  background: transparent;

  color: #9ca3af;

  font-size: 16px;

  cursor: pointer;
}

.form-grid {
  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 17px;
}

.form-group label {
  display: block;

  margin-bottom: 7px;

  color: #374151;

  font-size: 10px;

  font-weight: 700;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;

  box-sizing: border-box;

  padding: 11px 12px;

  border:
    1px solid #d1d5db;

  border-radius: 8px;

  outline: none;

  background: white;

  font-family: inherit;

  font-size: 10px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #2563eb;

  box-shadow:
    0 0 0 3px
    rgba(37,99,235,0.08);
}

.form-group textarea {
  resize: vertical;
}

.form-group > small {
  display: block;

  margin-top: 5px;

  color: #9ca3af;

  font-size: 8px;
}

.full {
  grid-column: 1 / -1;
}

.checkbox-row {
  padding: 13px;

  display: flex !important;

  align-items: center;

  gap: 10px;

  border:
    1px solid #e5e7eb;

  border-radius: 9px;

  cursor: pointer;
}

.checkbox-row input {
  width: auto;
}

.checkbox-row strong,
.checkbox-row small {
  display: block;
}

.checkbox-row small {
  margin-top: 3px;

  color: #9ca3af;

  font-size: 8px;
}

.form-actions {
  margin-top: 22px;

  display: flex;

  justify-content: flex-end;

  gap: 8px;
}


/* STATISTICS */

.statistics {
  max-width: 1200px;

  margin: 22px auto;

  display: grid;

  grid-template-columns:
    repeat(3, minmax(0, 1fr));

  gap: 13px;
}

.stat-card {
  padding: 18px;

  border:
    1px solid #e5e7eb;

  border-radius: 12px;

  background: white;
}

.stat-card span {
  display: block;

  color: #6b7280;

  font-size: 9px;
}

.stat-card strong {
  display: block;

  margin-top: 4px;

  color: #111827;

  font-size: 23px;
}


/* CONTENT */

.content-card {
  max-width: 1200px;

  margin: auto;

  overflow: hidden;

  border:
    1px solid #e5e7eb;

  border-radius: 15px;

  background: white;
}

.toolbar {
  padding: 20px;

  display: flex;

  align-items: center;

  justify-content: space-between;

  gap: 20px;

  border-bottom:
    1px solid #f3f4f6;
}

.toolbar h2 {
  margin: 0 0 4px;

  color: #111827;

  font-size: 15px;
}

.toolbar p {
  margin: 0;

  color: #9ca3af;

  font-size: 9px;
}

.filters {
  display: flex;

  gap: 7px;
}

.filters input,
.filters select {
  padding: 9px 10px;

  border:
    1px solid #d1d5db;

  border-radius: 7px;

  outline: none;

  background: white;

  font-size: 9px;
}


/* TABLE */

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;

  border-collapse: collapse;
}

th {
  padding: 12px 14px;

  background: #f9fafb;

  color: #6b7280;

  text-align: left;

  font-size: 8px;

  text-transform: uppercase;
}

td {
  padding: 14px;

  border-top:
    1px solid #f3f4f6;

  color: #4b5563;

  font-size: 9px;

  vertical-align: middle;
}

tbody tr:hover {
  background: #fafafa;
}

.service-info {
  min-width: 180px;

  display: flex;

  align-items: center;

  gap: 9px;
}

.service-icon {
  width: 42px;

  height: 42px;

  display: flex;

  align-items: center;

  justify-content: center;

  flex-shrink: 0;

  border-radius: 9px;

  background: #f5f3ff;

  font-size: 18px;
}

.service-info strong,
.service-info small {
  display: block;
}

.service-info strong {
  color: #111827;

  font-size: 10px;
}

.service-info small {
  margin-top: 3px;

  color: #9ca3af;

  font-size: 8px;
}

.animal-badge {
  display: inline-block;

  padding: 6px 9px;

  border-radius: 20px;

  background: #f8fafc;

  color: #475569;

  font-size: 8px;

  font-weight: 600;
}

.price {
  color: #111827;

  font-weight: 700;
}


/* STATUS */

.status-button {
  padding: 6px 9px;

  border: none;

  border-radius: 20px;

  font-size: 8px;

  font-weight: 700;

  cursor: pointer;
}

.status-button.active {
  background: #ecfdf5;

  color: #047857;
}

.status-button.inactive {
  background: #f3f4f6;

  color: #6b7280;
}


/* ACTION */

.table-actions {
  display: flex;

  gap: 5px;
}

.edit-button,
.delete-button {
  padding: 7px 8px;

  border-radius: 6px;

  background: white;

  font-size: 8px;

  cursor: pointer;
}

.edit-button {
  border:
    1px solid #bfdbfe;

  color: #2563eb;
}

.edit-button:hover {
  background: #eff6ff;
}

.delete-button {
  border:
    1px solid #fecaca;

  color: #dc2626;
}

.delete-button:hover {
  background: #fef2f2;
}


/* STATE */

.state {
  min-height: 280px;

  padding: 30px;

  display: flex;

  flex-direction: column;

  align-items: center;

  justify-content: center;

  color: #6b7280;

  text-align: center;
}

.state h3 {
  margin: 10px 0 4px;

  color: #111827;
}

.state p {
  margin: 0;

  font-size: 9px;
}

.empty-icon {
  font-size: 38px;
}

.loader {
  width: 34px;

  height: 34px;

  border:
    4px solid #dbeafe;

  border-top-color:
    #2563eb;

  border-radius: 50%;

  animation:
    spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform:
      rotate(360deg);
  }
}


/* AI INFO */

.ai-info {
  max-width: 1200px;

  margin: 20px auto 0;

  padding: 20px;

  box-sizing: border-box;

  display: flex;

  align-items: center;

  gap: 15px;

  border:
    1px solid #c7d2fe;

  border-radius: 13px;

  background:
    linear-gradient(
      135deg,
      #eef2ff,
      #eff6ff
    );
}

.ai-info-icon {
  width: 52px;

  height: 52px;

  display: flex;

  align-items: center;

  justify-content: center;

  flex-shrink: 0;

  border-radius: 12px;

  background: white;

  font-size: 25px;
}

.ai-info span {
  color: #6366f1;

  font-size: 8px;

  font-weight: 800;

  letter-spacing: 0.7px;
}

.ai-info h3 {
  margin: 4px 0;

  color: #1e3a8a;

  font-size: 12px;
}

.ai-info p {
  max-width: 800px;

  margin: 0;

  color: #64748b;

  font-size: 9px;

  line-height: 1.7;
}


/* RESPONSIVE */

@media (max-width: 850px) {

  .toolbar {
    align-items: stretch;

    flex-direction: column;
  }

  .filters {
    flex-direction: column;
  }

}


@media (max-width: 650px) {

  .page-header {
    align-items: flex-start;

    flex-direction: column;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .full {
    grid-column: auto;
  }

  .statistics {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .primary-button,
  .secondary-button {
    width: 100%;
  }

}
</style>