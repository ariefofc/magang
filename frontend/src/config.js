/**
 * Central config — single source of truth untuk semua env vars.
 *
 * Cara pakai:
 *   import { cfg } from '@/config'   // atau '../config'
 *   cfg.APP_NAME   // "MS Harmony"
 *   cfg.API_BASE   // "/api"
 *
 * Vite replace `import.meta.env.VITE_*` saat build,
 * jadi nilai di sini sudah statis di production bundle.
 */

const env = import.meta.env

export const cfg = Object.freeze({
  // ─── API ───────────────────────────────────────────
  API_BASE: env.VITE_API_BASE_URL || '/api',
  BACKEND_URL: env.VITE_BACKEND_URL || 'http://localhost:5000',

  // ─── Branding ──────────────────────────────────────
  APP_NAME: env.VITE_APP_NAME || 'MS Harmony',
  APP_TAGLINE: env.VITE_APP_TAGLINE || 'Premium Kebaya Rental',
  APP_DESCRIPTION: env.VITE_APP_DESCRIPTION || 'Koleksi kebaya premium untuk momen istimewa Anda.',

  // ─── Kontak ────────────────────────────────────────
  CONTACT_WA: env.VITE_CONTACT_WHATSAPP || '0812-xxxx-xxxx',
  CONTACT_EMAIL: env.VITE_CONTACT_EMAIL || 'info@msharmony.com',
  CONTACT_IG: env.VITE_CONTACT_INSTAGRAM || '@msharmony.kebaya',
  STUDIO_ADDRESS: env.VITE_STUDIO_ADDRESS || 'Studio MS Harmony, Yogyakarta',

  // ─── Polling ───────────────────────────────────────
  PAYMENT_POLL_MS: Number(env.VITE_PAYMENT_POLL_INTERVAL) || 5000,
  CHAT_POLL_MS: Number(env.VITE_CHAT_POLL_INTERVAL) || 3000,

  // ─── Dev ───────────────────────────────────────────
  PORT: Number(env.VITE_PORT) || 5173,
})

/** Shorthand: split APP_NAME ke dua bagian untuk two-tone render */
export function brandParts() {
  const parts = cfg.APP_NAME.split(' ')
  return { first: parts[0] || 'MS', rest: parts.slice(1).join(' ') || 'Harmony' }
}
