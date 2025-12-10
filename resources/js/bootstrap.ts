import axios from 'axios'

declare global {
  interface Window {
    axios: typeof axios
  }
}

window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const savedToken = localStorage.getItem('api_token')
if (savedToken) {
  window.axios.defaults.headers.common.Authorization = `Bearer ${savedToken}`
}

window.axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('api_token')
      delete window.axios.defaults.headers.common.Authorization
      window.location.href = '/'
    }
    return Promise.reject(error)
  }
)

