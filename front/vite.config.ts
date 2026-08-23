import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Builds directly into Laravel's public/ dir so apache can serve the SPA
// and the API from a single DocumentRoot (see docker/apache/httpd-vhost.conf).
// emptyOutDir is false because public/ also holds Laravel's own front
// controller (index.php, .htaccess, etc.) which must not be wiped.
export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../public',
    emptyOutDir: false,
  },
  server: {
    host: true,
    port: 5173,
    proxy: {
      '/api': { target: 'http://apache', changeOrigin: true },
      '/sanctum': { target: 'http://apache', changeOrigin: true },
    },
  },
})
