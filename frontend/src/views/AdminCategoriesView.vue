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

const categories = ref([])

const loading = ref(true)
const saving = ref(false)

const error = ref('')
const success = ref('')

const search = ref('')

const showForm = ref(false)
const editingId = ref(null)

const form = ref({
  name: '',
  description: '',
})


/*
|--------------------------------------------------------------------------
| COMPUTED
|--------------------------------------------------------------------------
*/

const filteredCategories = computed(() => {
  const keyword =
    search.value
      .toLowerCase()
      .trim()

  if (!keyword) {
    return categories.value
  }

  return categories.value.filter(
    (category) => {

      const name =
        category.name
          ?.toLowerCase() || ''

      const description =
        category.description
          ?.toLowerCase() || ''

      return (
        name.includes(keyword) ||
        description.includes(keyword)
      )
    }
  )
})


/*
|--------------------------------------------------------------------------
| LOAD DATA
|--------------------------------------------------------------------------
*/

const loadCategories = async () => {
  loading.value = true
  error.value = ''

  try {

    const response =
      await api.get('/categories')

    categories.value =
      response.data.data

  } catch (err) {

    console.error(err)

    error.value =
      'Data kategori gagal dimuat.'

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
    description: '',
  }

  editingId.value = null

  showForm.value = false
}


/*
|--------------------------------------------------------------------------
| OPEN ADD FORM
|--------------------------------------------------------------------------
*/

const openAddForm = () => {

  success.value = ''
  error.value = ''

  form.value = {
    name: '',
    description: '',
  }

  editingId.value = null

  showForm.value = true
}


/*
|--------------------------------------------------------------------------
| OPEN EDIT FORM
|--------------------------------------------------------------------------
*/

const openEditForm = (category) => {

  success.value = ''
  error.value = ''

  editingId.value =
    category.id

  form.value = {
    name: category.name,
    description:
      category.description || '',
  }

  showForm.value = true

  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}


/*
|--------------------------------------------------------------------------
| SAVE CATEGORY
|--------------------------------------------------------------------------
*/

const saveCategory = async () => {

  error.value = ''
  success.value = ''

  if (!form.value.name.trim()) {

    error.value =
      'Nama kategori wajib diisi.'

    return
  }


  saving.value = true


  const payload = {
    name:
      form.value.name.trim(),

    description:
      form.value.description.trim()
        || null,
  }


  try {

    if (editingId.value) {

      await api.put(
        `/admin/categories/${editingId.value}`,
        payload
      )

      success.value =
        'Kategori berhasil diperbarui.'

    } else {

      await api.post(
        '/admin/categories',
        payload
      )

      success.value =
        'Kategori berhasil ditambahkan.'
    }


    resetForm()

    await loadCategories()


  } catch (err) {

    console.error(err)


    if (
      err.response?.status === 422
    ) {

      const errors =
        err.response?.data?.errors

      if (errors) {

        const firstError =
          Object.values(errors)
            .flat()[0]

        error.value =
          firstError ||
          'Data kategori tidak valid.'

      } else {

        error.value =
          err.response?.data?.message ||
          'Data kategori tidak valid.'
      }

    } else if (
      err.response?.status === 403
    ) {

      error.value =
        'Anda tidak memiliki akses untuk melakukan tindakan ini.'

    } else {

      error.value =
        'Terjadi kesalahan saat menyimpan kategori.'
    }

  } finally {

    saving.value = false

  }
}


/*
|--------------------------------------------------------------------------
| DELETE CATEGORY
|--------------------------------------------------------------------------
*/

const deleteCategory = async (category) => {

  error.value = ''
  success.value = ''


  const confirmed =
    window.confirm(
      `Hapus kategori "${category.name}"?`
    )


  if (!confirmed) {
    return
  }


  try {

    await api.delete(
      `/admin/categories/${category.id}`
    )

    success.value =
      'Kategori berhasil dihapus.'

    await loadCategories()


  } catch (err) {

    console.error(err)


    if (
      err.response?.status === 422
    ) {

      error.value =
        err.response?.data?.message ||
        'Kategori tidak dapat dihapus karena masih digunakan oleh produk.'

    } else if (
      err.response?.status === 403
    ) {

      error.value =
        'Anda tidak memiliki akses untuk menghapus kategori.'

    } else {

      error.value =
        'Terjadi kesalahan saat menghapus kategori.'
    }
  }
}


/*
|--------------------------------------------------------------------------
| MOUNT
|--------------------------------------------------------------------------
*/

onMounted(() => {
  loadCategories()
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
            🗂️ KELOLA KATALOG
          </span>

          <h1>
            Kategori Produk
          </h1>

          <p>
            Kelola kategori yang digunakan
            untuk mengelompokkan produk
            pada katalog PetShop AI.
          </p>

        </div>


        <button
          class="add-button"
          @click="openAddForm"
        >
          + Tambah Kategori
        </button>

      </section>



      <!-- ===================================================
           ALERT
      ==================================================== -->

      <section
        v-if="success"
        class="alert success-alert"
      >
        ✓ {{ success }}
      </section>


      <section
        v-if="error"
        class="alert error-alert"
      >
        ⚠ {{ error }}
      </section>



      <!-- ===================================================
           FORM
      ==================================================== -->

      <section
        v-if="showForm"
        class="form-card"
      >

        <div class="form-heading">

          <div>

            <span>
              {{
                editingId
                  ? '✏️'
                  : '➕'
              }}
            </span>

            <div>

              <h2>
                {{
                  editingId
                    ? 'Edit Kategori'
                    : 'Tambah Kategori'
                }}
              </h2>

              <p>
                Isi informasi kategori
                produk.
              </p>

            </div>

          </div>


          <button
            class="close-button"
            type="button"
            @click="resetForm"
          >
            ✕
          </button>

        </div>


        <form
          class="category-form"
          @submit.prevent="saveCategory"
        >

          <div class="form-group">

            <label>
              Nama Kategori
              <span>*</span>
            </label>

            <input
              v-model="form.name"
              type="text"
              placeholder="Contoh: Makanan Kucing"
              maxlength="255"
              required
            />

          </div>


          <div class="form-group">

            <label>
              Deskripsi
            </label>

            <textarea
              v-model="form.description"
              rows="4"
              placeholder="Deskripsi singkat kategori..."
            ></textarea>

          </div>


          <div class="form-actions">

            <button
              type="button"
              class="cancel-button"
              @click="resetForm"
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
                  : editingId
                    ? 'Simpan Perubahan'
                    : 'Tambah Kategori'
              }}
            </button>

          </div>

        </form>

      </section>



      <!-- ===================================================
           SUMMARY
      ==================================================== -->

      <section class="summary-grid">

        <div class="summary-card">

          <div class="summary-icon">
            🗂️
          </div>

          <div>

            <span>
              Total Kategori
            </span>

            <strong>
              {{ categories.length }}
            </strong>

          </div>

        </div>


        <div class="summary-card">

          <div class="summary-icon">
            📦
          </div>

          <div>

            <span>
              Produk Terhubung
            </span>

            <strong>
              {{
                categories.reduce(
                  (total, category) =>
                    total +
                    Number(
                      category.products_count
                      || 0
                    ),
                  0
                )
              }}
            </strong>

          </div>

        </div>

      </section>



      <!-- ===================================================
           CONTENT
      ==================================================== -->

      <section class="content-card">

        <!-- TOOLBAR -->

        <div class="toolbar">

          <div>

            <h2>
              Daftar Kategori
            </h2>

            <p>
              Semua kategori produk
              yang tersedia.
            </p>

          </div>


          <div class="search-box">

            <span>
              🔍
            </span>

            <input
              v-model="search"
              type="text"
              placeholder="Cari kategori..."
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
            Memuat kategori...
          </p>

        </div>



        <!-- EMPTY -->

        <div
          v-else-if="
            filteredCategories.length === 0
          "
          class="state"
        >

          <div class="empty-icon">
            🗂️
          </div>

          <h3>
            Kategori tidak ditemukan
          </h3>

          <p>
            {{
              search
                ? 'Tidak ada kategori yang sesuai dengan pencarian.'
                : 'Belum ada kategori produk.'
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
                  No.
                </th>

                <th>
                  Kategori
                </th>

                <th>
                  Deskripsi
                </th>

                <th>
                  Produk
                </th>

                <th class="action-heading">
                  Aksi
                </th>

              </tr>

            </thead>


            <tbody>

              <tr
                v-for="
                  (category, index)
                  in filteredCategories
                "
                :key="category.id"
              >

                <td class="number-cell">
                  {{ index + 1 }}
                </td>


                <td>

                  <div class="category-name">

                    <div class="category-icon">
                      🗂️
                    </div>

                    <div>

                      <strong>
                        {{ category.name }}
                      </strong>

                      <small>
                        ID: {{ category.id }}
                      </small>

                    </div>

                  </div>

                </td>


                <td class="description-cell">

                  {{
                    category.description
                      || 'Tidak ada deskripsi'
                  }}

                </td>


                <td>

                  <span class="product-count">

                    {{
                      category.products_count
                      || 0
                    }}

                    produk

                  </span>

                </td>


                <td>

                  <div class="table-actions">

                    <button
                      class="edit-button"
                      @click="
                        openEditForm(category)
                      "
                    >
                      ✏️ Edit
                    </button>


                    <button
                      class="delete-button"
                      @click="
                        deleteCategory(category)
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
           INFORMATION
      ==================================================== -->

      <section class="info-card">

        <div class="info-icon">
          💡
        </div>

        <div>

          <h3>
            Tentang Kategori Produk
          </h3>

          <p>
            Kategori digunakan untuk
            mengelompokkan produk pada katalog.
            Kategori yang masih memiliki produk
            tidak dapat dihapus agar relasi data
            tetap terjaga.
          </p>

        </div>

      </section>

    </main>

  </PublicLayout>

</template>


<style scoped>

/* =========================================================
   PAGE
========================================================= */

.admin-page {
  min-height: 750px;

  padding:
    38px 24px 90px;

  background: #f8fafc;
}


/* =========================================================
   HEADER
========================================================= */

.page-header {
  max-width: 1200px;

  margin: auto;

  padding: 30px;

  display: flex;

  align-items: center;

  justify-content: space-between;

  gap: 30px;

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

  margin-bottom: 17px;

  color: #93c5fd;

  text-decoration: none;

  font-size: 10px;

  font-weight: 600;
}


.back-link:hover {
  color: white;
}


.page-badge {
  display: inline-block;

  padding: 5px 10px;

  border-radius: 20px;

  background:
    rgba(255, 255, 255, 0.1);

  color: #bfdbfe;

  font-size: 8px;

  font-weight: 800;

  letter-spacing: 1px;
}


.page-header h1 {
  margin: 10px 0 7px;

  font-size: 28px;
}


.page-header p {
  max-width: 600px;

  margin: 0;

  color: #cbd5e1;

  font-size: 11px;

  line-height: 1.7;
}


.add-button {
  padding: 12px 17px;

  flex-shrink: 0;

  border: none;

  border-radius: 9px;

  background: #2563eb;

  color: white;

  font-size: 11px;

  font-weight: 700;

  cursor: pointer;
}


.add-button:hover {
  background: #1d4ed8;
}


/* =========================================================
   ALERT
========================================================= */

.alert {
  max-width: 1200px;

  margin:
    18px auto 0;

  padding: 13px 16px;

  border-radius: 9px;

  font-size: 11px;
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


/* =========================================================
   FORM
========================================================= */

.form-card {
  max-width: 1200px;

  margin:
    20px auto;

  padding: 25px;

  border: 1px solid #dbeafe;

  border-radius: 15px;

  background: white;

  box-shadow:
    0 12px 35px
    rgba(0, 0, 0, 0.05);
}


.form-heading {
  display: flex;

  align-items: center;

  justify-content: space-between;

  margin-bottom: 22px;

  padding-bottom: 17px;

  border-bottom:
    1px solid #f3f4f6;
}


.form-heading > div {
  display: flex;

  align-items: center;

  gap: 10px;
}


.form-heading > div > span {
  font-size: 24px;
}


.form-heading h2 {
  margin: 0;

  color: #111827;

  font-size: 16px;
}


.form-heading p {
  margin: 3px 0 0;

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


.category-form {
  max-width: 700px;
}


.form-group {
  margin-bottom: 18px;
}


.form-group label {
  display: block;

  margin-bottom: 7px;

  color: #374151;

  font-size: 10px;

  font-weight: 700;
}


.form-group label span {
  color: #dc2626;
}


.form-group input,
.form-group textarea {
  width: 100%;

  box-sizing: border-box;

  padding: 11px 12px;

  border:
    1px solid #d1d5db;

  border-radius: 8px;

  outline: none;

  background: white;

  color: #111827;

  font-family: inherit;

  font-size: 11px;
}


.form-group input:focus,
.form-group textarea:focus {
  border-color: #2563eb;

  box-shadow:
    0 0 0 3px
    rgba(37, 99, 235, 0.08);
}


.form-group textarea {
  resize: vertical;
}


.form-actions {
  display: flex;

  justify-content: flex-end;

  gap: 8px;

  margin-top: 22px;
}


.cancel-button,
.save-button {
  padding: 10px 15px;

  border-radius: 8px;

  font-size: 10px;

  font-weight: 700;

  cursor: pointer;
}


.cancel-button {
  border:
    1px solid #d1d5db;

  background: white;

  color: #4b5563;
}


.save-button {
  border: none;

  background: #2563eb;

  color: white;
}


.save-button:hover {
  background: #1d4ed8;
}


.save-button:disabled {
  opacity: 0.6;

  cursor: not-allowed;
}


/* =========================================================
   SUMMARY
========================================================= */

.summary-grid {
  max-width: 1200px;

  margin:
    22px auto;

  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 15px;
}


.summary-card {
  padding: 18px;

  display: flex;

  align-items: center;

  gap: 13px;

  border:
    1px solid #e5e7eb;

  border-radius: 12px;

  background: white;
}


.summary-icon {
  width: 45px;

  height: 45px;

  display: flex;

  align-items: center;

  justify-content: center;

  border-radius: 10px;

  background: #eff6ff;

  font-size: 20px;
}


.summary-card span {
  display: block;

  color: #6b7280;

  font-size: 9px;
}


.summary-card strong {
  display: block;

  margin-top: 2px;

  color: #111827;

  font-size: 21px;
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
  width: 250px;

  padding:
    0 11px;

  display: flex;

  align-items: center;

  gap: 7px;

  border:
    1px solid #d1d5db;

  border-radius: 8px;

  background: #f9fafb;
}


.search-box span {
  font-size: 12px;
}


.search-box input {
  width: 100%;

  padding:
    10px 0;

  border: none;

  outline: none;

  background: transparent;

  font-size: 10px;
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
  padding:
    12px 15px;

  background: #f9fafb;

  color: #6b7280;

  text-align: left;

  font-size: 9px;

  font-weight: 700;

  text-transform: uppercase;

  letter-spacing: 0.4px;
}


td {
  padding:
    15px;

  border-top:
    1px solid #f3f4f6;

  color: #4b5563;

  font-size: 10px;

  vertical-align: middle;
}


tbody tr:hover {
  background: #fafafa;
}


.number-cell {
  width: 40px;

  color: #9ca3af;
}


.category-name {
  display: flex;

  align-items: center;

  gap: 10px;
}


.category-icon {
  width: 37px;

  height: 37px;

  display: flex;

  align-items: center;

  justify-content: center;

  flex-shrink: 0;

  border-radius: 9px;

  background: #eff6ff;

  font-size: 16px;
}


.category-name strong,
.category-name small {
  display: block;
}


.category-name strong {
  color: #111827;

  font-size: 11px;
}


.category-name small {
  margin-top: 3px;

  color: #9ca3af;

  font-size: 8px;
}


.description-cell {
  max-width: 320px;

  color: #6b7280;

  line-height: 1.6;
}


.product-count {
  display: inline-block;

  padding: 5px 8px;

  border-radius: 20px;

  background: #f3f4f6;

  color: #4b5563;

  font-size: 8px;

  font-weight: 600;
}


.action-heading {
  width: 160px;
}


.table-actions {
  display: flex;

  gap: 6px;
}


.edit-button,
.delete-button {
  padding: 7px 9px;

  border-radius: 6px;

  background: white;

  font-size: 9px;

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


/* =========================================================
   STATES
========================================================= */

.state {
  min-height: 280px;

  display: flex;

  flex-direction: column;

  align-items: center;

  justify-content: center;

  padding: 30px;

  color: #6b7280;

  text-align: center;
}


.state h3 {
  margin:
    10px 0 4px;

  color: #111827;

  font-size: 14px;
}


.state p {
  margin: 0;

  font-size: 10px;
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
   INFORMATION
========================================================= */

.info-card {
  max-width: 1200px;

  margin:
    20px auto 0;

  padding: 18px;

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
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

  .page-header {
    align-items: flex-start;

    flex-direction: column;
  }


  .summary-grid {
    grid-template-columns: 1fr;
  }


  .toolbar {
    align-items: stretch;

    flex-direction: column;
  }


  .search-box {
    width: auto;
  }


  .form-actions {
    flex-direction: column;
  }


  .cancel-button,
  .save-button {
    width: 100%;
  }

}

</style>