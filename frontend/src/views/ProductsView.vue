<script setup>
import { onMounted, ref } from 'vue'
import PublicLayout from '../layouts/PublicLayout.vue'
import api from '../services/api'

const products = ref([])
const categories = ref([])

const search = ref('')
const selectedCategory = ref('')

const loading = ref(false)
const error = ref('')

const loadCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data.data
  } catch (err) {
    console.error(err)
  }
}

const loadProducts = async () => {
  loading.value = true
  error.value = ''

  try {
    const params = {}

    if (search.value.trim()) {
      params.search = search.value.trim()
    }

    if (selectedCategory.value) {
      params.category_id = selectedCategory.value
    }

    const response = await api.get('/products', {
      params,
    })

    products.value = response.data.data
  } catch (err) {
    console.error(err)

    error.value =
      'Gagal mengambil data produk. Pastikan backend Laravel sedang berjalan.'
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  loadProducts()
}

const resetFilter = () => {
  search.value = ''
  selectedCategory.value = ''
  loadProducts()
}

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value))
}

onMounted(async () => {
  await loadCategories()
  await loadProducts()
})
</script>

<template>
  <PublicLayout>

    <!-- HEADER -->
    <section class="page-header">
      <div class="header-content">

        <span class="header-badge">
          🛍️ Pet Shop
        </span>

        <h1>Produk Pet Shop</h1>

        <p>
          Temukan berbagai kebutuhan terbaik untuk
          hewan kesayangan Anda.
        </p>

      </div>
    </section>


    <!-- CONTENT -->
    <section class="products-section">

      <!-- FILTER -->
      <div class="filter-card">

        <div class="search-box">
          <label>Cari Produk</label>

          <input
            v-model="search"
            type="text"
            placeholder="Contoh: Royal Canin..."
            @keyup.enter="handleSearch"
          />
        </div>


        <div class="category-box">
          <label>Kategori</label>

          <select v-model="selectedCategory">
            <option value="">
              Semua Kategori
            </option>

            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
        </div>


        <button
          class="search-button"
          @click="handleSearch"
        >
          Cari
        </button>

        <button
          class="reset-button"
          @click="resetFilter"
        >
          Reset
        </button>

      </div>


      <!-- RESULT INFO -->
      <div class="result-header">

        <div>
          <h2>Daftar Produk</h2>

          <p v-if="!loading">
            {{ products.length }} produk ditemukan
          </p>
        </div>

      </div>


      <!-- LOADING -->
      <div
        v-if="loading"
        class="state-box"
      >
        <div class="loader"></div>

        <p>Memuat produk...</p>
      </div>


      <!-- ERROR -->
      <div
        v-else-if="error"
        class="state-box error"
      >
        <span>⚠️</span>

        <h3>Terjadi Kesalahan</h3>

        <p>
          {{ error }}
        </p>

        <button @click="loadProducts">
          Coba Lagi
        </button>
      </div>


      <!-- EMPTY -->
      <div
        v-else-if="products.length === 0"
        class="state-box"
      >
        <span class="empty-icon">
          🐾
        </span>

        <h3>Produk tidak ditemukan</h3>

        <p>
          Coba gunakan kata pencarian atau
          kategori yang berbeda.
        </p>

        <button @click="resetFilter">
          Tampilkan Semua
        </button>
      </div>


      <!-- PRODUCTS -->
      <div
        v-else
        class="product-grid"
      >

        <article
          v-for="product in products"
          :key="product.id"
          class="product-card"
        >

          <!-- IMAGE -->
          <div class="product-image">

            <img
              v-if="product.image"
              :src="product.image"
              :alt="product.name"
            />

            <div
              v-else
              class="image-placeholder"
            >
              🐾
            </div>


            <span
              v-if="product.stock > 0"
              class="stock-badge"
            >
              Stok {{ product.stock }}
            </span>

            <span
              v-else
              class="stock-badge empty"
            >
              Stok Habis
            </span>

          </div>


          <!-- INFORMATION -->
          <div class="product-info">

            <span class="category">
              {{
                product.category?.name
                ?? 'Tanpa Kategori'
              }}
            </span>

            <h3>
              {{ product.name }}
            </h3>

            <p class="description">
              {{
                product.description
                || 'Tidak ada deskripsi produk.'
              }}
            </p>


            <div class="product-bottom">

              <strong class="price">
                {{ formatRupiah(product.price) }}
              </strong>

              <button class="detail-button">
                Detail
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
  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #f8fafc
    );

  padding: 65px 24px;
}

.header-content {
  max-width: 1200px;
  margin: auto;
}

.header-badge {
  display: inline-block;

  background: #dbeafe;
  color: #1d4ed8;

  padding: 7px 13px;

  border-radius: 30px;

  font-size: 13px;
  font-weight: 600;
}

.header-content h1 {
  margin: 16px 0 10px;

  color: #111827;

  font-size: 38px;
}

.header-content p {
  color: #6b7280;

  margin: 0;

  max-width: 600px;

  line-height: 1.7;
}


.products-section {
  max-width: 1200px;

  margin: auto;

  padding: 50px 24px 90px;
}


/* FILTER */

.filter-card {
  display: grid;

  grid-template-columns:
    minmax(250px, 1fr)
    240px
    auto
    auto;

  align-items: end;

  gap: 15px;

  padding: 20px;

  background: white;

  border: 1px solid #e5e7eb;

  border-radius: 15px;

  box-shadow:
    0 5px 20px rgba(0, 0, 0, 0.04);
}

.search-box,
.category-box {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.filter-card label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.filter-card input,
.filter-card select {
  width: 100%;

  height: 43px;

  padding: 0 13px;

  border: 1px solid #d1d5db;

  border-radius: 8px;

  outline: none;

  background: white;

  color: #374151;
}

.filter-card input:focus,
.filter-card select:focus {
  border-color: #2563eb;
}

.search-button,
.reset-button {
  height: 43px;

  padding: 0 20px;

  border-radius: 8px;

  cursor: pointer;

  font-weight: 600;

  border: none;
}

.search-button {
  background: #2563eb;
  color: white;
}

.search-button:hover {
  background: #1d4ed8;
}

.reset-button {
  background: #f3f4f6;
  color: #374151;
}

.reset-button:hover {
  background: #e5e7eb;
}


/* RESULT */

.result-header {
  margin: 40px 0 22px;
}

.result-header h2 {
  margin: 0 0 5px;
  color: #111827;
}

.result-header p {
  margin: 0;
  color: #6b7280;
  font-size: 13px;
}


/* PRODUCT GRID */

.product-grid {
  display: grid;

  grid-template-columns:
    repeat(3, minmax(0, 1fr));

  gap: 24px;
}

.product-card {
  overflow: hidden;

  background: white;

  border: 1px solid #e5e7eb;

  border-radius: 16px;

  transition:
    transform 0.2s,
    box-shadow 0.2s;
}

.product-card:hover {
  transform: translateY(-4px);

  box-shadow:
    0 15px 35px rgba(0, 0, 0, 0.08);
}


/* IMAGE */

.product-image {
  position: relative;

  height: 220px;

  background: #f8fafc;

  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;

  object-fit: cover;
}

.image-placeholder {
  width: 100%;
  height: 100%;

  display: flex;

  justify-content: center;
  align-items: center;

  font-size: 65px;

  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #f8fafc
    );
}

.stock-badge {
  position: absolute;

  top: 13px;
  right: 13px;

  padding: 6px 10px;

  border-radius: 20px;

  background: #dcfce7;
  color: #15803d;

  font-size: 11px;
  font-weight: 700;
}

.stock-badge.empty {
  background: #fee2e2;
  color: #b91c1c;
}


/* PRODUCT INFO */

.product-info {
  padding: 20px;
}

.category {
  color: #2563eb;

  font-size: 12px;
  font-weight: 600;
}

.product-info h3 {
  color: #111827;

  margin: 8px 0;

  font-size: 18px;
}

.description {
  color: #6b7280;

  line-height: 1.6;

  font-size: 13px;

  min-height: 42px;

  display: -webkit-box;

  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;

  overflow: hidden;
}

.product-bottom {
  margin-top: 20px;

  padding-top: 16px;

  border-top: 1px solid #f3f4f6;

  display: flex;

  justify-content: space-between;
  align-items: center;

  gap: 15px;
}

.price {
  color: #111827;

  font-size: 18px;
}

.detail-button {
  border: none;

  background: #eff6ff;
  color: #2563eb;

  padding: 9px 14px;

  border-radius: 8px;

  font-size: 12px;
  font-weight: 600;

  cursor: pointer;
}


/* STATE */

.state-box {
  min-height: 300px;

  border: 1px dashed #d1d5db;

  border-radius: 16px;

  display: flex;

  flex-direction: column;

  align-items: center;
  justify-content: center;

  text-align: center;

  padding: 30px;

  color: #6b7280;
}

.state-box h3 {
  color: #111827;
  margin-bottom: 5px;
}

.state-box button {
  margin-top: 10px;

  border: none;

  border-radius: 8px;

  background: #2563eb;

  color: white;

  padding: 10px 16px;

  cursor: pointer;
}

.empty-icon {
  font-size: 45px;
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


/* RESPONSIVE */

@media (max-width: 900px) {
  .product-grid {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .filter-card {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 600px) {
  .product-grid {
    grid-template-columns: 1fr;
  }

  .filter-card {
    grid-template-columns: 1fr;
  }

  .header-content h1 {
    font-size: 30px;
  }
}
</style>