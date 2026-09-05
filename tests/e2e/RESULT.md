# E2E Smoke Test Report — Smart Space Booking

**Tanggal Uji:** 2026-09-05 06:36:44 UTC  
**Environment:** Local Testing Server (`http://127.0.0.1:8000/api`)  
**Hasil:** **50/50 PASS** (Failed: 0)

## Ringkasan Eksekusi

Semua endpoint yang terdaftar di `routes/api.php` telah diuji secara otomatis menggunakan HTTP request riil (`curl`), mencakup autentikasi Sanctum, katalog publik, validasi promo, flow lengkap reservasi member, pencegahan bentrok jadwal (conflict 400), admin approval, check-in, check-out, laporan pendapatan, serta upload media multipart.

| Status | Total |
|---|---|
| **Total Test Case** | **50** |
| **Passed (Sukses)** | **50** |
| **Failed (Gagal)** | **0** |

## Tabel Detail Pengujian Per Endpoint

| No | Method | Endpoint | Expected Code | Actual Code | Status | Deskripsi |
|---|---|---|---|---|---|---|
| #1 | GET | `` | 200 | 200 | PASS | Public Root Info |
| #2 | GET | `/health` | 200 | 200 | PASS | Health Check |
| #3 | POST | `/auth/register/member` | 201 | 201 | PASS | Register Member Baru |
| #4 | POST | `/auth/register/admin-space` | 201 | 201 | PASS | Register Admin Coworking Baru |
| #5 | POST | `/auth/login` | 200 | 200 | PASS | Login Admin Demo |
| #6 | POST | `/auth/login` | 200 | 200 | PASS | Login Member Budi |
| #7 | POST | `/auth/login` | 200 | 200 | PASS | Login Temp Member for Logout |
| #8 | GET | `/auth/profile` | 200 | 200 | PASS | Member Profile Detail |
| #9 | POST | `/auth/logout` | 200 | 200 | PASS | Logout Session |
| #10 | GET | `/spaces/types` | 200 | 200 | PASS | Daftar Tipe Space |
| #11 | GET | `/spaces/availability?id_space=1&tanggal=2026-09-12&jam_mulai=15:00&durasi_jam=2` | 200 | 200 | PASS | Cek Ketersediaan Space |
| #12 | GET | `/spaces` | 200 | 200 | PASS | Katalog Semua Space |
| #13 | GET | `/spaces/1` | 200 | 200 | PASS | Detail Space ID 1 |
| #14 | GET | `/diskon/active` | 200 | 200 | PASS | Daftar Promo Diskon Aktif |
| #15 | POST | `/diskon/check` | 200 | 200 | PASS | Validasi Kode Promo DISKONHEMAT20 |
| #16 | GET | `/diskon/1` | 200 | 200 | PASS | Detail Promo Diskon ID 1 |
| #17 | POST | `/reservasi` | 201 | 201 | PASS | Member Booking Space ID 1 |
| #18 | POST | `/reservasi` | 400 | 400 | PASS | Cek Bentrok Jadwal (Expected 400 Conflict) |
| #19 | POST | `/reservasi` | 201 | 201 | PASS | Booking Space ID 2 untuk Cancel Test |
| #20 | PATCH | `/reservasi/9/cancel` | 200 | 200 | PASS | Member Cancel Reservasi Sendiri |
| #21 | GET | `/reservasi/my` | 200 | 200 | PASS | Daftar Reservasi Aktif Member |
| #22 | GET | `/reservasi/my/history` | 200 | 200 | PASS | Riwayat Reservasi & Pengeluaran |
| #23 | GET | `/reservasi/8` | 200 | 200 | PASS | Detail Reservasi Member |
| #24 | GET | `/reservasi/8/e-ticket` | 200 | 200 | PASS | E-Ticket & QR Payload |
| #25 | GET | `/admin/profile` | 200 | 200 | PASS | Admin Lihat Profil Coworking |
| #26 | PUT | `/admin/profile` | 200 | 200 | PASS | Admin Update Profil Coworking |
| #27 | GET | `/admin/spaces` | 200 | 200 | PASS | Admin List Managed Spaces |
| #28 | POST | `/admin/spaces` | 201 | 201 | PASS | Admin Create New Space |
| #29 | GET | `/admin/spaces/7` | 200 | 200 | PASS | Admin Get Space Detail |
| #30 | PUT | `/admin/spaces/7` | 200 | 200 | PASS | Admin Update Space |
| #31 | DELETE | `/admin/spaces/7` | 200 | 200 | PASS | Admin Delete Space |
| #32 | GET | `/admin/members` | 200 | 200 | PASS | Admin List Members |
| #33 | POST | `/admin/members` | 201 | 201 | PASS | Admin Create Member |
| #34 | GET | `/admin/members/5` | 200 | 200 | PASS | Admin Show Member |
| #35 | PUT | `/admin/members/5` | 200 | 200 | PASS | Admin Update Member |
| #36 | DELETE | `/admin/members/5` | 200 | 200 | PASS | Admin Delete Member |
| #37 | GET | `/admin/diskon` | 200 | 200 | PASS | Admin List Diskon |
| #38 | POST | `/admin/diskon` | 201 | 201 | PASS | Admin Create Diskon Promo |
| #39 | GET | `/admin/diskon/4` | 200 | 200 | PASS | Admin Show Diskon |
| #40 | PUT | `/admin/diskon/4` | 200 | 200 | PASS | Admin Update Diskon |
| #41 | DELETE | `/admin/diskon/4` | 200 | 200 | PASS | Admin Delete Diskon |
| #42 | GET | `/admin/reservasi` | 200 | 200 | PASS | Admin List All Reservasi |
| #43 | PATCH | `/admin/reservasi/8/status` | 200 | 200 | PASS | Admin Approve Reservasi (disetujui) |
| #44 | POST | `/admin/reservasi/8/check-in` | 200 | 200 | PASS | Admin Check-In Member (disetujui -> aktif) |
| #45 | POST | `/admin/reservasi/8/check-out` | 200 | 200 | PASS | Admin Check-Out Member (aktif -> selesai) |
| #46 | GET | `/admin/reports/monthly` | 200 | 200 | PASS | Admin Monthly Occupancy & Usage Report |
| #47 | GET | `/admin/reports/income` | 200 | 200 | PASS | Admin Revenue & Income Report |
| #48 | POST | `/upload/image` | 201 | 201 | PASS | Upload General Image (PNG) |
| #49 | POST | `/upload/members` | 201 | 201 | PASS | Upload Member Photo (PNG) |
| #50 | POST | `/upload/spaces` | 201 | 201 | PASS | Upload Space Image (PNG) |

## Pembuktian End-to-End Flow Reservasi
1. **Pencarian & Katalog**: Member melihat tipe space (`/api/spaces/types`) dan ketersediaan (`/api/spaces/availability`).
2. **Booking & Diskon**: Member memesan space ID 1 dengan diskon `DISKONHEMAT20` (`/api/reservasi`) -> Status: `belum_dikonfirm`.
3. **Pencegahan Bentrok**: Request booking kedua pada space dan waktu yang bertubrukan ditolak seketika -> HTTP 400 Bad Request.
4. **Approval Admin**: Admin coworking menyetujui reservasi (`/api/admin/reservasi/{id}/status`) -> Status: `disetujui`.
5. **E-Ticket**: Member mengunduh e-ticket dan kode QR (`/api/reservasi/{id}/e-ticket`).
6. **Check-In**: Admin memvalidasi kedatangan member (`/api/admin/reservasi/{id}/check-in`) -> Status: `aktif`, timestamp tercatat.
7. **Check-Out**: Admin memproses selesai (`/api/admin/reservasi/{id}/check-out`) -> Status: `selesai`, timestamp tercatat.
8. **Laporan & Riwayat**: Transaksi masuk ke riwayat member (`/api/reservasi/my/history`) dan laporan pendapatan admin (`/api/admin/reports/income`).

