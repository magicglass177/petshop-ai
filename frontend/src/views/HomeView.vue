<script setup>
import { RouterLink } from 'vue-router'
import PublicLayout from '../layouts/PublicLayout.vue'
</script>

<template>
  <PublicLayout>

    <section class="hero">
      <div class="hero-content">

        <div class="hero-text">
          <span class="badge">
            ✨ Pet Shop dengan Teknologi AI
          </span>

          <h1>
            Semua Kebutuhan
            <span>Hewan Kesayangan</span>
            dalam Satu Tempat
          </h1>

          <p>
            Temukan produk dan layanan untuk hewan
            kesayangan Anda serta gunakan AI Pet Assistant
            untuk mendapatkan rekomendasi kebutuhan
            perawatan secara cepat.
          </p>

          <div class="hero-actions">
            <RouterLink
              to="/products"
              class="primary-button"
            >
              Lihat Produk
            </RouterLink>

            <RouterLink
              to="/ai-assistant"
              class="secondary-button"
            >
              ✨ Coba AI Pet Assistant
            </RouterLink>
          </div>

          <div class="guest-info">
            ✓ AI Pet Assistant dapat digunakan tanpa login
          </div>
        </div>

        <div class="hero-visual">
          <div class="pet-card">
            <div class="pet-icons">
              🐱 🐶
            </div>

            <h3>PetShop AI</h3>

            <p>
              Smart Care for Your Pet
            </p>
          </div>
        </div>

      </div>
    </section>


    <section class="features">

      <div class="section-heading">
        <span>Layanan Kami</span>
        <h2>Apa yang tersedia di PetShop AI?</h2>

        <p>
          Berbagai kebutuhan hewan peliharaan
          tersedia dalam satu sistem.
        </p>
      </div>

      <div class="feature-grid">

        <div class="feature-card">
          <div class="feature-icon">🛍️</div>

          <h3>Produk Pet Shop</h3>

          <p>
            Temukan makanan, vitamin, aksesoris,
            mainan dan berbagai kebutuhan hewan.
          </p>

          <RouterLink to="/products">
            Lihat Produk →
          </RouterLink>
        </div>

        <div class="feature-card">
          <div class="feature-icon">✂️</div>

          <h3>Layanan Perawatan</h3>

          <p>
            Informasi berbagai layanan perawatan
            untuk hewan kesayangan Anda.
          </p>

          <RouterLink to="/services">
            Lihat Layanan →
          </RouterLink>
        </div>

        <div class="feature-card highlight">
          <div class="feature-icon">✨</div>

          <h3>AI Pet Assistant</h3>

          <p>
            Ceritakan kebutuhan hewan Anda dan AI
            membantu memberikan rekomendasi layanan
            yang relevan.
          </p>

          <RouterLink to="/ai-assistant">
            Coba AI →
          </RouterLink>
        </div>

      </div>

    </section>

  </PublicLayout>
</template>

<style scoped>
.hero {
  background:
    linear-gradient(
      135deg,
      #eff6ff,
      #f8fafc
    );

  padding: 90px 24px;
}

.hero-content {
  max-width: 1200px;
  margin: auto;

  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  align-items: center;
  gap: 70px;
}

.badge {
  display: inline-block;
  background: #dbeafe;
  color: #1d4ed8;

  padding: 8px 14px;
  border-radius: 30px;

  font-size: 13px;
  font-weight: 600;

  margin-bottom: 22px;
}

.hero h1 {
  font-size: 52px;
  line-height: 1.12;
  color: #111827;
  margin: 0;
}

.hero h1 span {
  color: #2563eb;
}

.hero-text > p {
  color: #6b7280;
  line-height: 1.8;
  font-size: 16px;

  max-width: 620px;
  margin: 25px 0;
}

.hero-actions {
  display: flex;
  gap: 12px;
}

.primary-button,
.secondary-button {
  display: inline-block;
  padding: 13px 21px;

  border-radius: 10px;
  text-decoration: none;

  font-size: 14px;
  font-weight: 600;
}

.primary-button {
  background: #2563eb;
  color: white;
}

.secondary-button {
  background: white;
  color: #2563eb;
  border: 1px solid #bfdbfe;
}

.guest-info {
  margin-top: 18px;
  color: #059669;
  font-size: 13px;
}

.hero-visual {
  display: flex;
  justify-content: center;
}

.pet-card {
  width: 340px;
  height: 340px;

  background: white;
  border-radius: 35px;

  box-shadow:
    0 30px 70px rgba(37, 99, 235, 0.15);

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.pet-icons {
  font-size: 80px;
}

.pet-card h3 {
  margin: 20px 0 5px;
  font-size: 25px;
  color: #111827;
}

.pet-card p {
  color: #6b7280;
}

.features {
  max-width: 1200px;
  margin: auto;
  padding: 90px 24px;
}

.section-heading {
  text-align: center;
  margin-bottom: 50px;
}

.section-heading span {
  color: #2563eb;
  font-size: 13px;
  font-weight: 700;
}

.section-heading h2 {
  color: #111827;
  font-size: 32px;
  margin: 10px 0;
}

.section-heading p {
  color: #6b7280;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 25px;
}

.feature-card {
  padding: 30px;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  background: white;
}

.feature-card.highlight {
  border-color: #bfdbfe;
  background: #eff6ff;
}

.feature-icon {
  font-size: 36px;
}

.feature-card h3 {
  color: #111827;
}

.feature-card p {
  color: #6b7280;
  line-height: 1.7;
  min-height: 80px;
}

.feature-card a {
  color: #2563eb;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
}

@media (max-width: 800px) {
  .hero-content {
    grid-template-columns: 1fr;
  }

  .hero h1 {
    font-size: 40px;
  }

  .feature-grid {
    grid-template-columns: 1fr;
  }

  .footer-container {
    flex-direction: column;
  }
}
</style>