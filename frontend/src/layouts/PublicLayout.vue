<script setup>
import Navbar from '../components/Navbar.vue'
</script>

<template>
  <div class="public-layout">

    <Navbar />

    <main>
      <slot />
    </main>

    <footer>
      <div class="footer-container">
        <div>
          <strong>🐾 PetShop AI</strong>
          <p>
            Sistem Informasi Pet Shop dengan
            teknologi Artificial Intelligence.
          </p>
        </div>

        <p>
          © 2026 PetShop AI
        </p>
      </div>
    </footer>

  </div>
</template>

<style scoped>
footer {
  background: #111827;
  color: white;
  padding: 35px 24px;
}

.footer-container {
  max-width: 1200px;
  margin: auto;

  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 30px;
}

.footer-container p {
  color: #9ca3af;
  font-size: 13px;
}
</style>