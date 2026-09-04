# Design.md — Design System
### Smart Space Booking (Web)

> Wireframe resmi pada soal UKK (Lampiran D) dibuat untuk **mobile app**. Dokumen ini mengadaptasi alur & fitur yang sama ke **layout website**, dengan referensi gaya landing page/dashboard coworking space modern (hero section, card grid, sidebar admin).

---

## 1. Prinsip Desain

- **Bersih & profesional** — cocok untuk target pengguna profesional/startup/mahasiswa.
- **Card-based layout** — katalog space ditampilkan sebagai grid card (bukan list vertikal seperti di mobile), memanfaatkan lebar layar desktop.
- **Data-dense untuk Admin** — panel admin memakai tabel + sidebar navigasi, bukan bottom nav seperti di mobile.
- **Trust signal** — tampilkan foto asli ruangan, badge status jelas, dan konfirmasi visual di setiap alur transaksi.

## 2. Palet Warna

| Peran | Warna | Hex |
|---|---|---|
| Primary (brand) | Indigo/Biru tua | `#4F46E5` |
| Primary Hover | Indigo lebih gelap | `#4338CA` |
| Secondary/Accent | Amber (untuk CTA harga/promo) | `#F59E0B` |
| Background | Putih/Abu sangat muda | `#FFFFFF` / `#F8FAFC` |
| Surface/Card | Putih dengan border tipis | `#FFFFFF`, border `#E2E8F0` |
| Teks Utama | Abu gelap | `#0F172A` |
| Teks Sekunder | Abu medium | `#64748B` |
| Success (Disetujui/Selesai) | Hijau | `#16A34A` |
| Warning (Belum Dikonfirmasi) | Kuning | `#EAB308` |
| Info (Aktif) | Biru | `#2563EB` |
| Danger (Dibatalkan) | Merah | `#DC2626` |

## 3. Tipografi

- **Font:** `Inter` atau `Plus Jakarta Sans` (Google Fonts, kesan modern & mudah dibaca).
- Heading (H1): 32–40px, bold
- Heading (H2): 24–28px, semibold
- Body: 16px, regular
- Caption/label kecil: 13–14px, medium

## 4. Adaptasi Layout: Mobile → Web

| Elemen Mobile | Adaptasi Web |
|---|---|
| Bottom navigation bar (Beranda, Reservasi, Tiket, Akun) | **Top navbar** (Member) atau **Sidebar kiri** (Admin) |
| List space vertikal 1 kolom | **Grid card** 3 kolom (desktop) / 2 kolom (tablet) |
| Form reservasi full-screen | **Modal / panel samping (drawer)** saat klik "Pesan" dari halaman detail space |
| Halaman "Status Pemesanan" list | Tabel dengan filter tab (Semua/Belum Dikonfirmasi/Disetujui/dst.) + badge warna status |
| E-Ticket full-screen dengan QR besar | Card e-ticket terpusat, ukuran QR proporsional, tombol "Unduh PDF" dan "Bagikan" |
| Dashboard Admin (9 layar terpisah) | **Sidebar + Dashboard utama** menampilkan ringkasan (stat card: total member, total space, reservasi bulan ini, pendapatan bulan ini) di satu halaman, detail di sub-halaman |

## 5. Komponen Utama

- **Navbar (Member):** Logo, menu (Cari Space, Reservasi Saya, Histori), avatar + dropdown akun.
- **Sidebar (Admin):** Dashboard, Profil Lokasi, Member, Space, Diskon, Reservasi, Laporan.
- **SpaceCard:** foto, nama space, badge tipe, kapasitas (ikon orang), harga/jam, tombol "Lihat Detail".
- **StatusBadge:** pill berwarna sesuai status (lihat palet §2).
- **BookingForm:** date picker, time picker, stepper durasi jam, input kode promo dengan tombol "Cek", ringkasan harga real-time (subtotal → diskon → total).
- **StatCard (Admin Dashboard):** angka besar + label + ikon, untuk total member/space/reservasi/pendapatan.
- **DataTable (Admin):** untuk daftar member, space, diskon, reservasi — dengan search, filter, dan aksi (edit/hapus/lihat).
- **QRCodeCard:** tampilan e-ticket dengan QR code, kode booking, dan rincian pembayaran.

## 6. Referensi Visual

Gaya yang dijadikan acuan (landing page & dashboard coworking/workspace rental modern):
- Hero section dengan pencarian space langsung di atas (search bar besar + filter tipe).
- Grid card katalog dengan foto dominan, informasi harga jelas di pojok card.
- Dashboard admin dengan sidebar gelap/terang minimalis dan area konten dengan card statistik di bagian atas.

*(Referensi gambar telah ditampilkan di percakapan sebagai mood board — gunakan sebagai inspirasi gaya visual, bukan untuk ditiru identik/copy asset.)*

## 7. Spacing & Radius

- Border radius standar: `8px` (card), `6px` (button/input), `999px` (badge/pill).
- Spacing unit dasar: kelipatan `4px` (Tailwind default: `p-2`, `p-4`, `p-6`, `p-8`).
- Shadow card: halus, `shadow-sm` hingga `shadow-md` saat hover.