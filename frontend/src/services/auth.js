import { reactive } from 'vue'
import api from './api'

const savedToken = localStorage.getItem('token')
const savedUser = localStorage.getItem('user')

export const authState = reactive({
  token: savedToken || null,
  user: savedUser
    ? JSON.parse(savedUser)
    : null,
  initialized: false,
})

export const setAuth = (token, user) => {
  authState.token = token
  authState.user = user

  localStorage.setItem('token', token)
  localStorage.setItem(
    'user',
    JSON.stringify(user)
  )
}

export const clearAuth = () => {
  authState.token = null
  authState.user = null

  localStorage.removeItem('token')
  localStorage.removeItem('user')
}

export const initializeAuth = async () => {
  const token = localStorage.getItem('token')

  if (!token) {
    authState.initialized = true
    return
  }

  try {
    const response = await api.get('/me')

    const user = response.data.data

    authState.token = token
    authState.user = user

    localStorage.setItem(
      'user',
      JSON.stringify(user)
    )
  } catch (error) {
    console.error(
      'Token tidak valid:',
      error
    )

    clearAuth()
  } finally {
    authState.initialized = true
  }
}