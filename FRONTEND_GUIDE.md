# Frontend Integration Guide
**RPF Backend API - Optimization Update**

Dokumen ini berisi panduan untuk tim Frontend (Web/Mobile) terkait perubahan pada Backend API setelah proses Security Hardening & Performance Optimization.

## 1. ⚠️ Critical Security Implementations

### A. Rate Limiting (Anti-Spam/Brute Force)
API sekarang menerapkan batasan request yang ketat. Frontend wajib menangani error `429 Too Many Requests`.

| Endpoint | Limit | Perilaku Frontend |
|----------|-------|-------------------|
| `POST /api/v1/login` | 5 hit / menit | Tampilkan: "Terlalu banyak percobaan login. Akun dikunci sementara selama 1 menit." |
| `POST /api/v1/contact` | 5 hit / menit | Tampilkan: "Mohon tunggu sebentar sebelum mengirim pesan lagi." |
| Global API | 60 hit / menit | Implementasi **Retry Logic** (exponential backoff) atau tampilkan notifikasi "Server sibuk". |

### B. Honeypot (Anti-Bot Contact Form)
Endpoint `POST /api/v1/contact` memiliki mekanisme anti-bot "invisible".
**Wajib di Frontend:**
1.  Tambahkan input field `website` dan `fax` pada form.
2.  **Sembunyikan** field tersebut dengan CSS (`display: none` atau `opacity: 0`).
3.  Pastikan user **TIDAK** mengisi field tersebut.
4.  (Optional) Kirim field `_token_time` berisi timestamp (UNIX) saat user mulai mengetik di form.

**Contoh Payload Aman:**
```json
{
  "name": "User Asli",
  "email": "user@example.com",
  "message": "Halo...",
  "website": "",    // WAJIB KOSONG
  "fax": ""         // WAJIB KOSONG
}
```

### C. Input Sanitization
Backend akan otomatis memblokir request yang mengandung karakter berbahaya (SQL Injection / script tags).
*   **Do:** Kirim data text bersih / JSON standard.
*   **Don't:** Mengirim query params yang mengandung quote `'` `--` atau script tags `<script>`.
*   **Error:** Akan mereturn `403 Forbidden` jika terdeteksi.

## 2. ⚡ Performance Behavior

### A. Caching (Read Operations)
Response untuk endpoint GET (Products, News, Banners) sekarang di-cache.
*   **Perilaku:** Data mungkin delay update 5-30 menit jika diakses publik (tanpa login admin).
*   **Admin:** Operasi Create/Update/Delete akan otomatis menghapus cache (Instant Update). Frontend tidak perlu melakukan apa-apa, nikmati load time yang lebih cepat!

### B. Asynchronous Process (Queue)
Endpoint `POST /api/v1/contact` akan mereturn `201 Created` dengan sangat cepat (< 100ms).
*   **Note:** Email notifikasi dikirim di background. Jangan tunggu response email terkirim untuk menampilkan pesan "Sukses" ke user. Langsung tampilkan saja begitu dapat response 201.

## 3. 📝 HTTP Headers

### Request Headers
Pastikan selalu mengirim header ini agar tidak terkena redirect atau format error HTML:
```http
Accept: application/json
Content-Type: application/json
```

### Response Security Headers
Backend mengirim header keamanan ketat.
*   **HTTPS Only:** Di production, API akan menolak koneksi HTTP biasa (Force HTTPS). Pastikan base URL frontend menggunakan `https://`.
*   **No Iframe:** API menolak diload di dalam `<iframe>` (Header `X-Frame-Options: DENY`).

## 4. API Documentation
Dokumentasi lengkap schema request & response tersedia di:
`{BASE_URL}/docs` (misal: `https://api.example.com/docs`)
Gunakan ini sebagai acuan tipe data dan validasi terbaru.
