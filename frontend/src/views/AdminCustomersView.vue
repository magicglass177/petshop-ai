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

const customers = ref([])

const loading = ref(true)
const detailLoading = ref(false)

const error = ref('')

const search = ref('')

const selectedCustomer = ref(null)
const showDetail = ref(false)


/*
|--------------------------------------------------------------------------
| COMPUTED
|--------------------------------------------------------------------------
*/

const filteredCustomers = computed(() => {

  const keyword =
    search.value
      .trim()
      .toLowerCase()


  if (!keyword) {
    return customers.value
  }


  return customers.value.filter(
    customer => {

      const name =
        customer.user?.name
          ?.toLowerCase() || ''

      const email =
        customer.user?.email
          ?.toLowerCase() || ''

      const phone =
        customer.phone
          ?.toLowerCase() || ''

      const address =
        customer.address
          ?.toLowerCase() || ''


      return (
        name.includes(keyword) ||
        email.includes(keyword) ||
        phone.includes(keyword) ||
        address.includes(keyword)
      )
    }
  )
})


const totalPets = computed(() => {

  return customers.value.reduce(
    (total, customer) => {

      return total +
        Number(
          customer.pets_count || 0
        )
    },
    0
  )
})


/*
|--------------------------------------------------------------------------
| FORMAT
|--------------------------------------------------------------------------
*/

const formatDate = (date) => {

  if (!date) {
    return '-'
  }


  return new Intl.DateTimeFormat(
    'id-ID',
    {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    }
  ).format(
    new Date(date)
  )
}


const formatWeight = (weight) => {

  if (
    weight === null ||
    weight === undefined ||
    weight === ''
  ) {
    return '-'
  }

  return `${Number(weight)} kg`
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
| LOAD CUSTOMERS
|--------------------------------------------------------------------------
*/

const loadCustomers = async () => {

  loading.value = true
  error.value = ''

  try {

    const response =
      await api.get(
        '/admin/customers'
      )

    customers.value =
      response.data.data

  } catch (err) {

    console.error(err)

    if (
      err.response?.status === 403
    ) {

      error.value =
        'Anda tidak memiliki akses ke data customer.'

    } else {

      error.value =
        'Data customer gagal dimuat.'
    }

  } finally {

    loading.value = false
  }
}


/*
|--------------------------------------------------------------------------
| CUSTOMER DETAIL
|--------------------------------------------------------------------------
*/

const openDetail = async (customer) => {

  detailLoading.value = true

  error.value = ''

  showDetail.value = true

  selectedCustomer.value = null


  try {

    const response =
      await api.get(
        `/admin/customers/${customer.id}`
      )

    selectedCustomer.value =
      response.data.data

  } catch (err) {

    console.error(err)

    error.value =
      'Detail customer gagal dimuat.'

    showDetail.value = false

  } finally {

    detailLoading.value = false
  }
}


const closeDetail = () => {

  showDetail.value = false

  selectedCustomer.value = null
}


/*
|--------------------------------------------------------------------------
| MOUNT
|--------------------------------------------------------------------------
*/

onMounted(() => {
  loadCustomers()
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
            👥 DATA CUSTOMER
          </span>

          <h1>
            Data Customer
          </h1>

          <p>
            Lihat customer yang telah
            melakukan registrasi serta
            informasi hewan peliharaan
            yang tersimpan pada sistem.
          </p>

        </div>


        <div class="header-icon">
          👥
        </div>

      </section>



      <!-- ===================================================
           ERROR
      ==================================================== -->

      <div
        v-if="error"
        class="alert error-alert"
      >
        ⚠ {{ error }}
      </div>



      <!-- ===================================================
           STATISTICS
      ==================================================== -->

      <section class="statistics">

        <div class="stat-card">

          <div class="stat-icon">
            👥
          </div>

          <div>

            <span>
              Total Customer
            </span>

            <strong>
              {{ customers.length }}
            </strong>

          </div>

        </div>


        <div class="stat-card">

          <div class="stat-icon">
            🐾
          </div>

          <div>

            <span>
              Hewan Terdaftar
            </span>

            <strong>
              {{ totalPets }}
            </strong>

          </div>

        </div>

      </section>



      <!-- ===================================================
           CUSTOMER TABLE
      ==================================================== -->

      <section class="content-card">

        <div class="toolbar">

          <div>

            <h2>
              Daftar Customer
            </h2>

            <p>
              Akun customer yang telah
              terdaftar pada PetShop AI.
            </p>

          </div>


          <div class="search-box">

            <span>
              🔍
            </span>

            <input
              v-model="search"
              type="text"
              placeholder="Cari nama, email, telepon..."
            />

          </div>

        </div>



        <!-- LOADING -->

        <div
          v-if="loading"
          class="state"
        >

          <div class="loader"></div>

          <p>
            Memuat data customer...
          </p>

        </div>



        <!-- EMPTY -->

        <div
          v-else-if="
            filteredCustomers.length === 0
          "
          class="state"
        >

          <div class="empty-icon">
            👥
          </div>

          <h3>
            Customer tidak ditemukan
          </h3>

          <p>
            {{
              search
                ? 'Tidak ada customer yang sesuai dengan pencarian.'
                : 'Belum ada customer yang melakukan registrasi.'
            }}
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

                <th>
                  Customer
                </th>

                <th>
                  Telepon
                </th>

                <th>
                  Alamat
                </th>

                <th>
                  Hewan
                </th>

                <th>
                  Terdaftar
                </th>

                <th>
                  Aksi
                </th>

              </tr>

            </thead>


            <tbody>

              <tr
                v-for="
                  customer
                  in filteredCustomers
                "
                :key="customer.id"
              >

                <!-- CUSTOMER -->

                <td>

                  <div class="customer-info">

                    <div class="avatar">

                      {{
                        customer.user?.name
                          ?.charAt(0)
                          ?.toUpperCase()
                          || '?'
                      }}

                    </div>


                    <div>

                      <strong>
                        {{
                          customer.user?.name
                          || '-'
                        }}
                      </strong>

                      <small>
                        {{
                          customer.user?.email
                          || '-'
                        }}
                      </small>

                    </div>

                  </div>

                </td>


                <!-- PHONE -->

                <td>

                  {{
                    customer.phone
                    || 'Belum diisi'
                  }}

                </td>


                <!-- ADDRESS -->

                <td class="address-cell">

                  {{
                    customer.address
                    || 'Belum diisi'
                  }}

                </td>


                <!-- PETS -->

                <td>

                  <span class="pet-count">

                    🐾

                    {{
                      customer.pets_count
                      || 0
                    }}

                    hewan

                  </span>

                </td>


                <!-- DATE -->

                <td>

                  {{
                    formatDate(
                      customer.user
                        ?.created_at
                    )
                  }}

                </td>


                <!-- ACTION -->

                <td>

                  <button
                    class="detail-button"
                    @click="
                      openDetail(customer)
                    "
                  >
                    👁️ Lihat Detail
                  </button>

                </td>

              </tr>

            </tbody>

          </table>

        </div>

      </section>



      <!-- ===================================================
           INFORMATION
      ==================================================== -->

      <section class="info-card">

        <div class="info-icon">
          ℹ️
        </div>

        <div>

          <h3>
            Pengelolaan Customer
          </h3>

          <p>
            Akun customer dibuat melalui
            proses registrasi. Admin hanya
            melihat informasi customer dan
            data hewan yang terdaftar,
            sehingga data akun tidak diubah
            secara sembarangan melalui
            dashboard admin.
          </p>

        </div>

      </section>



      <!-- ===================================================
           MODAL DETAIL
      ==================================================== -->

      <div
        v-if="showDetail"
        class="modal-overlay"
        @click.self="closeDetail"
      >

        <section class="detail-modal">

          <div class="modal-header">

            <div>

              <span class="modal-badge">
                DETAIL CUSTOMER
              </span>

              <h2>
                Informasi Customer
              </h2>

            </div>


            <button
              class="close-button"
              @click="closeDetail"
            >
              ✕
            </button>

          </div>



          <!-- DETAIL LOADING -->

          <div
            v-if="detailLoading"
            class="detail-loading"
          >

            <div class="loader"></div>

            <p>
              Memuat detail customer...
            </p>

          </div>



          <!-- DETAIL -->

          <template
            v-else-if="selectedCustomer"
          >

            <!-- PROFILE -->

            <section class="profile-section">

              <div class="profile-avatar">

                {{
                  selectedCustomer.user
                    ?.name
                    ?.charAt(0)
                    ?.toUpperCase()
                    || '?'
                }}

              </div>


              <div>

                <h3>
                  {{
                    selectedCustomer.user
                      ?.name
                    || '-'
                  }}
                </h3>

                <p>
                  {{
                    selectedCustomer.user
                      ?.email
                    || '-'
                  }}
                </p>

              </div>

            </section>



            <!-- CUSTOMER INFORMATION -->

            <section class="detail-grid">

              <div class="detail-item">

                <span>
                  Nama
                </span>

                <strong>
                  {{
                    selectedCustomer.user
                      ?.name
                    || '-'
                  }}
                </strong>

              </div>


              <div class="detail-item">

                <span>
                  Email
                </span>

                <strong>
                  {{
                    selectedCustomer.user
                      ?.email
                    || '-'
                  }}
                </strong>

              </div>


              <div class="detail-item">

                <span>
                  Nomor Telepon
                </span>

                <strong>
                  {{
                    selectedCustomer.phone
                    || 'Belum diisi'
                  }}
                </strong>

              </div>


              <div class="detail-item">

                <span>
                  Tanggal Registrasi
                </span>

                <strong>
                  {{
                    formatDate(
                      selectedCustomer.user
                        ?.created_at
                    )
                  }}
                </strong>

              </div>


              <div
                class="
                  detail-item
                  full-detail
                "
              >

                <span>
                  Alamat
                </span>

                <strong>
                  {{
                    selectedCustomer.address
                    || 'Belum diisi'
                  }}
                </strong>

              </div>

            </section>



            <!-- PETS -->

            <section class="pets-section">

              <div class="pets-heading">

                <div>

                  <h3>
                    🐾 Hewan Peliharaan
                  </h3>

                  <p>
                    Hewan yang terdaftar
                    pada akun customer.
                  </p>

                </div>


                <span class="pets-total">

                  {{
                    selectedCustomer.pets
                      ?.length || 0
                  }}

                  hewan

                </span>

              </div>



              <!-- NO PET -->

              <div
                v-if="
                  !selectedCustomer.pets
                    ?.length
                "
                class="no-pets"
              >

                <span>
                  🐾
                </span>

                <p>
                  Customer belum
                  menambahkan data hewan.
                </p>

              </div>



              <!-- PET LIST -->

              <div
                v-else
                class="pet-grid"
              >

                <article
                  v-for="
                    pet
                    in selectedCustomer.pets
                  "
                  :key="pet.id"
                  class="pet-card"
                >

                  <div class="pet-header">

                    <div class="pet-icon">

                      {{
                        animalIcon(
                          pet.animal_type
                        )
                      }}

                    </div>


                    <div>

                      <h4>
                        {{ pet.name }}
                      </h4>

                      <span>
                        {{
                          pet.animal_type
                        }}
                      </span>

                    </div>

                  </div>


                  <div class="pet-information">

                    <div>

                      <span>
                        Ras
                      </span>

                      <strong>
                        {{
                          pet.breed
                          || '-'
                        }}
                      </strong>

                    </div>


                    <div>

                      <span>
                        Gender
                      </span>

                      <strong>
                        {{
                          pet.gender
                          || '-'
                        }}
                      </strong>

                    </div>


                    <div>

                      <span>
                        Berat
                      </span>

                      <strong>
                        {{
                          formatWeight(
                            pet.weight
                          )
                        }}
                      </strong>

                    </div>


                    <div>

                      <span>
                        Tanggal Lahir
                      </span>

                      <strong>
                        {{
                          formatDate(
                            pet.birth_date
                          )
                        }}
                      </strong>

                    </div>

                  </div>


                  <div
                    v-if="pet.notes"
                    class="pet-notes"
                  >

                    <span>
                      Catatan
                    </span>

                    <p>
                      {{ pet.notes }}
                    </p>

                  </div>

                </article>

              </div>

            </section>

          </template>

        </section>

      </div>

    </main>

  </PublicLayout>

</template>


<style scoped>

/* =========================================================
   PAGE
========================================================= */

.admin-page {
  min-height: 750px;

  padding: 38px 24px 90px;

  background: #f8fafc;
}


/* =========================================================
   HEADER
========================================================= */

.page-header {
  max-width: 1200px;

  margin: auto;

  padding: 30px;

  box-sizing: border-box;

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
  max-width: 620px;

  margin: 0;

  color: #cbd5e1;

  font-size: 11px;

  line-height: 1.7;
}


.header-icon {
  font-size: 55px;
}


/* =========================================================
   ALERT
========================================================= */

.alert {
  max-width: 1200px;

  margin: 18px auto 0;

  padding: 13px 15px;

  box-sizing: border-box;

  border-radius: 9px;

  font-size: 10px;
}


.error-alert {
  border: 1px solid #fecaca;

  background: #fef2f2;

  color: #b91c1c;
}


/* =========================================================
   STATISTICS
========================================================= */

.statistics {
  max-width: 1200px;

  margin: 22px auto;

  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 14px;
}


.stat-card {
  padding: 18px;

  display: flex;

  align-items: center;

  gap: 13px;

  border:
    1px solid #e5e7eb;

  border-radius: 12px;

  background: white;
}


.stat-icon {
  width: 45px;

  height: 45px;

  display: flex;

  align-items: center;

  justify-content: center;

  border-radius: 10px;

  background: #eff6ff;

  font-size: 20px;
}


.stat-card span {
  display: block;

  color: #6b7280;

  font-size: 9px;
}


.stat-card strong {
  display: block;

  margin-top: 3px;

  color: #111827;

  font-size: 22px;
}


/* =========================================================
   CONTENT
========================================================= */

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


.search-box {
  width: 300px;

  padding: 0 11px;

  box-sizing: border-box;

  display: flex;

  align-items: center;

  gap: 7px;

  border:
    1px solid #d1d5db;

  border-radius: 8px;

  background: #f9fafb;
}


.search-box input {
  width: 100%;

  padding: 10px 0;

  border: none;

  outline: none;

  background: transparent;

  font-size: 9px;
}


/* =========================================================
   TABLE
========================================================= */

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


.customer-info {
  min-width: 180px;

  display: flex;

  align-items: center;

  gap: 10px;
}


.avatar {
  width: 40px;

  height: 40px;

  display: flex;

  align-items: center;

  justify-content: center;

  flex-shrink: 0;

  border-radius: 50%;

  background:
    linear-gradient(
      135deg,
      #2563eb,
      #4f46e5
    );

  color: white;

  font-size: 14px;

  font-weight: 800;
}


.customer-info strong,
.customer-info small {
  display: block;
}


.customer-info strong {
  color: #111827;

  font-size: 10px;
}


.customer-info small {
  margin-top: 3px;

  color: #9ca3af;

  font-size: 8px;
}


.address-cell {
  max-width: 200px;
}


.pet-count {
  display: inline-block;

  padding: 6px 9px;

  border-radius: 20px;

  background: #eff6ff;

  color: #2563eb;

  font-size: 8px;

  font-weight: 700;
}


.detail-button {
  padding: 7px 10px;

  border:
    1px solid #bfdbfe;

  border-radius: 7px;

  background: white;

  color: #2563eb;

  font-size: 8px;

  font-weight: 700;

  cursor: pointer;
}


.detail-button:hover {
  background: #eff6ff;
}


/* =========================================================
   STATE
========================================================= */

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


/* =========================================================
   INFO
========================================================= */

.info-card {
  max-width: 1200px;

  margin: 20px auto 0;

  padding: 18px;

  box-sizing: border-box;

  display: flex;

  align-items: center;

  gap: 13px;

  border:
    1px solid #dbeafe;

  border-radius: 12px;

  background: #eff6ff;
}


.info-icon {
  font-size: 24px;
}


.info-card h3 {
  margin: 0 0 4px;

  color: #1e3a8a;

  font-size: 11px;
}


.info-card p {
  margin: 0;

  color: #64748b;

  font-size: 9px;

  line-height: 1.6;
}


/* =========================================================
   MODAL
========================================================= */

.modal-overlay {
  position: fixed;

  inset: 0;

  z-index: 1000;

  padding: 30px 20px;

  box-sizing: border-box;

  display: flex;

  align-items: center;

  justify-content: center;

  overflow-y: auto;

  background:
    rgba(15, 23, 42, 0.65);
}


.detail-modal {
  width: 100%;

  max-width: 850px;

  max-height: 90vh;

  overflow-y: auto;

  padding: 25px;

  box-sizing: border-box;

  border-radius: 16px;

  background: white;

  box-shadow:
    0 25px 60px
    rgba(0,0,0,0.2);
}


.modal-header {
  padding-bottom: 16px;

  display: flex;

  align-items: center;

  justify-content: space-between;

  border-bottom:
    1px solid #e5e7eb;
}


.modal-badge {
  color: #2563eb;

  font-size: 8px;

  font-weight: 800;

  letter-spacing: 1px;
}


.modal-header h2 {
  margin: 5px 0 0;

  color: #111827;

  font-size: 18px;
}


.close-button {
  width: 34px;

  height: 34px;

  border:
    1px solid #e5e7eb;

  border-radius: 8px;

  background: white;

  color: #6b7280;

  cursor: pointer;
}


.detail-loading {
  min-height: 250px;

  display: flex;

  flex-direction: column;

  align-items: center;

  justify-content: center;

  color: #6b7280;

  font-size: 9px;
}


/* PROFILE */

.profile-section {
  margin-top: 20px;

  padding: 18px;

  display: flex;

  align-items: center;

  gap: 13px;

  border-radius: 12px;

  background: #f8fafc;
}


.profile-avatar {
  width: 50px;

  height: 50px;

  display: flex;

  align-items: center;

  justify-content: center;

  border-radius: 50%;

  background:
    linear-gradient(
      135deg,
      #2563eb,
      #4f46e5
    );

  color: white;

  font-size: 18px;

  font-weight: 800;
}


.profile-section h3 {
  margin: 0 0 3px;

  color: #111827;

  font-size: 14px;
}


.profile-section p {
  margin: 0;

  color: #6b7280;

  font-size: 9px;
}


/* DETAIL */

.detail-grid {
  margin-top: 17px;

  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 10px;
}


.detail-item {
  padding: 13px;

  border:
    1px solid #e5e7eb;

  border-radius: 9px;
}


.detail-item span,
.detail-item strong {
  display: block;
}


.detail-item span {
  margin-bottom: 4px;

  color: #9ca3af;

  font-size: 8px;
}


.detail-item strong {
  color: #374151;

  font-size: 10px;

  font-weight: 600;
}


.full-detail {
  grid-column: 1 / -1;
}


/* PETS */

.pets-section {
  margin-top: 25px;
}


.pets-heading {
  margin-bottom: 13px;

  display: flex;

  align-items: center;

  justify-content: space-between;
}


.pets-heading h3 {
  margin: 0 0 3px;

  color: #111827;

  font-size: 13px;
}


.pets-heading p {
  margin: 0;

  color: #9ca3af;

  font-size: 8px;
}


.pets-total {
  padding: 5px 9px;

  border-radius: 20px;

  background: #eff6ff;

  color: #2563eb;

  font-size: 8px;

  font-weight: 700;
}


.no-pets {
  padding: 35px;

  border:
    1px dashed #d1d5db;

  border-radius: 10px;

  color: #9ca3af;

  text-align: center;
}


.no-pets span {
  font-size: 28px;
}


.no-pets p {
  margin: 7px 0 0;

  font-size: 9px;
}


.pet-grid {
  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 12px;
}


.pet-card {
  padding: 15px;

  border:
    1px solid #e5e7eb;

  border-radius: 11px;

  background: #fff;
}


.pet-header {
  display: flex;

  align-items: center;

  gap: 9px;
}


.pet-icon {
  width: 38px;

  height: 38px;

  display: flex;

  align-items: center;

  justify-content: center;

  border-radius: 9px;

  background: #f8fafc;

  font-size: 18px;
}


.pet-header h4 {
  margin: 0;

  color: #111827;

  font-size: 11px;
}


.pet-header span {
  color: #9ca3af;

  font-size: 8px;
}


.pet-information {
  margin-top: 13px;

  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 8px;
}


.pet-information div {
  padding: 8px;

  border-radius: 7px;

  background: #f8fafc;
}


.pet-information span,
.pet-information strong {
  display: block;
}


.pet-information span {
  color: #9ca3af;

  font-size: 7px;
}


.pet-information strong {
  margin-top: 2px;

  color: #475569;

  font-size: 8px;
}


.pet-notes {
  margin-top: 10px;

  padding-top: 10px;

  border-top:
    1px solid #f3f4f6;
}


.pet-notes span {
  color: #9ca3af;

  font-size: 7px;
}


.pet-notes p {
  margin: 3px 0 0;

  color: #64748b;

  font-size: 8px;

  line-height: 1.5;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

  .header-icon {
    display: none;
  }


  .statistics {
    grid-template-columns: 1fr;
  }


  .toolbar {
    align-items: stretch;

    flex-direction: column;
  }


  .search-box {
    width: 100%;
  }


  .detail-grid,
  .pet-grid {
    grid-template-columns: 1fr;
  }


  .full-detail {
    grid-column: auto;
  }

}
</style>