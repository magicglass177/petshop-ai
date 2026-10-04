import { createApp } from 'vue'
import './style.css'

import App from './App.vue'
import router from './router'

import {
  initializeAuth
} from './services/auth'

const startApp = async () => {
  await initializeAuth()

  createApp(App)
    .use(router)
    .mount('#app')
}

startApp()