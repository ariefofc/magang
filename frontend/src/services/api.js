import axios from 'axios'
import { cfg } from '../config'

const api = axios.create({
  baseURL: cfg.API_BASE,
  withCredentials: true,
  headers: { 'Content-Type': 'application/json' },
})

export default api
