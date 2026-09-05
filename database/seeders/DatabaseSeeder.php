<?php

namespace Database\Seeders;

use App\Models\Diskon;
use App\Models\Member;
use App\Models\Reservasi;
use App\Models\Space;
use App\Models\SpaceOwner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Reservasi::truncate();
        Diskon::truncate();
        Space::truncate();
        Member::truncate();
        SpaceOwner::truncate();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // ==================== ADMIN + SPACE OWNER ====================
        $adminUser = User::create([
            'username' => 'admin_demo',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin_space',
        ]);

        $spaceOwner = SpaceOwner::create([
            'user_id' => $adminUser->id,
            'nama_coworking' => 'Moklet Hub Coworking Space',
            'nama_pemilik' => 'Ahmad Bidin, S.Kom',
            'telp' => '081298765432',
            'alamat' => 'Jl. Danau Ranau No. 1, Sawojajar, Malang',
            'deskripsi' => 'Coworking space modern dengan fasilitas lengkap: WiFi 100Mbps, ruang meeting, private office, pantry, dan area santai. Buka setiap hari 07.00-22.00 WIB.',
        ]);

        // ==================== MEMBER ====================
        $memberData = [
            [
                'username' => 'budi.member',
                'nama_member' => 'Budi Raharjo',
                'instansi' => 'SMK Telkom Malang',
                'alamat' => 'Jl. Danau Ranau No. 1, Sawojajar, Malang',
                'telp' => '085712345678',
            ],
            [
                'username' => 'siti.member',
                'nama_member' => 'Siti Nurhaliza',
                'instansi' => 'Universitas Brawijaya',
                'alamat' => 'Jl. Veteran No. 8, Ketawanggede, Malang',
                'telp' => '081234567890',
            ],
            [
                'username' => 'agus.member',
                'nama_member' => 'Agus Salim',
                'instansi' => 'PT Maju Teknologi',
                'alamat' => 'Jl. Soekarno Hatta No. 99, Lowokwaru, Malang',
                'telp' => '087812345678',
            ],
        ];

        $members = [];
        foreach ($memberData as $data) {
            $user = User::create([
                'username' => $data['username'],
                'password' => Hash::make('Member123!'),
                'role' => 'member',
            ]);

            $members[] = Member::create([
                'user_id' => $user->id,
                'nama_member' => $data['nama_member'],
                'instansi' => $data['instansi'],
                'alamat' => $data['alamat'],
                'telp' => $data['telp'],
                'foto' => null,
            ]);
        }

        // ==================== SPACES (6: 2 desk, 2 meeting_room, 2 private_office) ====================
        $spacesData = [
            [
                'nama_space' => 'Personal Desk - Flexi 01',
                'harga_per_jam' => 20000,
                'tipe' => 'desk',
                'kapasitas' => 1,
                'deskripsi' => 'Meja kerja individual yang tenang dengan colokan listrik, WiFi 100Mbps, lampu meja LED, dan free refill air mineral.',
            ],
            [
                'nama_space' => 'Personal Desk - Flexi 02',
                'harga_per_jam' => 25000,
                'tipe' => 'desk',
                'kapasitas' => 1,
                'deskripsi' => 'Meja kerja premium dekat jendela dengan pemandangan kota, monitor 24 inch, ergonomic chair, dan free flow kopi/teh.',
            ],
            [
                'nama_space' => 'Meeting Room Alpha',
                'harga_per_jam' => 100000,
                'tipe' => 'meeting_room',
                'kapasitas' => 8,
                'deskripsi' => 'Ruang rapat kedap suara kapasitas 8 orang, Smart TV 55 inch, soundbar Bluetooth, whiteboard kaca, AC dingin, dan conference speaker.',
            ],
            [
                'nama_space' => 'Meeting Room Beta',
                'harga_per_jam' => 150000,
                'tipe' => 'meeting_room',
                'kapasitas' => 12,
                'deskripsi' => 'Ruang rapat besar kapasitas 12 orang, proyektor 4K, video conference camera, whiteboard interaktif, dan pantry corner.',
            ],
            [
                'nama_space' => 'Private Office - Suite A',
                'harga_per_jam' => 75000,
                'tipe' => 'private_office',
                'kapasitas' => 4,
                'deskripsi' => 'Ruang kantor privat untuk tim kecil 2-4 orang, akses 24 jam, pintu terkunci, furnitur lengkap, dan akses prioritas ke meeting room.',
            ],
            [
                'nama_space' => 'Private Office - Suite B',
                'harga_per_jam' => 120000,
                'tipe' => 'private_office',
                'kapasitas' => 8,
                'deskripsi' => 'Ruang kantor eksekutif kapasitas 8 orang, private pantry, jendela besar, akses 24 jam dengan keamanan tambahan, dan dedicated support staff.',
            ],
        ];

        $spaces = [];
        foreach ($spacesData as $data) {
            $spaces[] = Space::create([
                'owner_id' => $spaceOwner->id,
                'nama_space' => $data['nama_space'],
                'harga_per_jam' => $data['harga_per_jam'],
                'tipe' => $data['tipe'],
                'kapasitas' => $data['kapasitas'],
                'deskripsi' => $data['deskripsi'],
                'foto' => null,
            ]);
        }

        // ==================== DISKON (1 aktif, 1 expired, 1 akan datang) ====================
        $diskonAktif = Diskon::create([
            'nama_diskon' => 'DISKONHEMAT20',
            'persentase_diskon' => 20,
            'tanggal_awal' => Carbon::create(2026, 8, 1, 0, 0, 0),
            'tanggal_akhir' => Carbon::create(2026, 12, 31, 23, 59, 59),
        ]);

        Diskon::create([
            'nama_diskon' => 'PROMOLEBARAN',
            'persentase_diskon' => 30,
            'tanggal_awal' => Carbon::create(2026, 3, 1, 0, 0, 0),
            'tanggal_akhir' => Carbon::create(2026, 12, 31, 23, 59, 59),
        ]);

        Diskon::create([
            'nama_diskon' => 'PROMOTAHUNBARU',
            'persentase_diskon' => 50,
            'tanggal_awal' => Carbon::create(2026, 12, 25, 0, 0, 0),
            'tanggal_akhir' => Carbon::create(2027, 12, 5, 23, 59, 59),
        ]);

        // ==================== RESERVASI (7 dengan variasi status & bulan) ====================
        $buatReservasi = function (array $params) {
            $hargaPerJam = $params['space']->harga_per_jam;
            $totalHargaAwal = $hargaPerJam * $params['durasi_jam'];
            $potonganDiskon = 0;

            if (! empty($params['diskon'])) {
                $potonganDiskon = (int) round($totalHargaAwal * $params['diskon']->persentase_diskon / 100);
            }

            $totalBayar = $totalHargaAwal - $potonganDiskon;
            $jamMulai = Carbon::parse($params['jam_mulai']);
            $jamSelesai = $jamMulai->copy()->addHours($params['durasi_jam']);

            Reservasi::create([
                'kode_booking' => $params['kode_booking'],
                'member_id' => $params['member']->id,
                'space_id' => $params['space']->id,
                'diskon_id' => $params['diskon']?->id,
                'tanggal_reservasi' => $params['tanggal']->format('Y-m-d'),
                'jam_mulai' => $jamMulai->format('H:i'),
                'jam_selesai' => $jamSelesai->format('H:i'),
                'durasi_jam' => $params['durasi_jam'],
                'harga_per_jam' => $hargaPerJam,
                'total_harga_awal' => $totalHargaAwal,
                'potongan_diskon' => $potonganDiskon,
                'total_bayar' => $totalBayar,
                'status' => $params['status'],
                'check_in_at' => $params['check_in_at'] ?? null,
                'check_out_at' => $params['check_out_at'] ?? null,
                'created_at' => $params['tanggal']->copy()->subDays(random_int(3, 10)),
                'updated_at' => $params['tanggal']->copy()->subDays(random_int(0, 5)),
            ]);
        };

        // 1. Selesai - bulan lalu (Agustus), dengan diskon aktif
        $buatReservasi([
            'kode_booking' => 'BOOK-20260810-0001',
            'member' => $members[0],
            'space' => $spaces[0],
            'diskon' => $diskonAktif,
            'tanggal' => Carbon::create(2026, 8, 10),
            'jam_mulai' => '09:00',
            'durasi_jam' => 3,
            'status' => 'selesai',
            'check_in_at' => Carbon::create(2026, 8, 10, 9, 2, 0),
            'check_out_at' => Carbon::create(2026, 8, 10, 12, 0, 0),
        ]);

        // 2. Selesai - bulan lalu (Agustus), tanpa diskon
        $buatReservasi([
            'kode_booking' => 'BOOK-20260815-0002',
            'member' => $members[1],
            'space' => $spaces[2],
            'diskon' => null,
            'tanggal' => Carbon::create(2026, 8, 15),
            'jam_mulai' => '13:00',
            'durasi_jam' => 4,
            'status' => 'selesai',
            'check_in_at' => Carbon::create(2026, 8, 15, 13, 0, 0),
            'check_out_at' => Carbon::create(2026, 8, 15, 17, 0, 0),
        ]);

        // 3. Selesai - bulan ini (September), dengan diskon
        $buatReservasi([
            'kode_booking' => 'BOOK-20260902-0003',
            'member' => $members[2],
            'space' => $spaces[4],
            'diskon' => $diskonAktif,
            'tanggal' => Carbon::create(2026, 9, 2),
            'jam_mulai' => '08:00',
            'durasi_jam' => 8,
            'status' => 'selesai',
            'check_in_at' => Carbon::create(2026, 9, 2, 8, 5, 0),
            'check_out_at' => Carbon::create(2026, 9, 2, 16, 0, 0),
        ]);

        // 4. Disetujui - bulan ini, belum check-in
        $buatReservasi([
            'kode_booking' => 'BOOK-20260905-0004',
            'member' => $members[0],
            'space' => $spaces[1],
            'diskon' => null,
            'tanggal' => Carbon::create(2026, 9, 5),
            'jam_mulai' => '10:00',
            'durasi_jam' => 5,
            'status' => 'disetujui',
        ]);

        // 5. Belum dikonfirmasi - bulan ini
        $buatReservasi([
            'kode_booking' => 'BOOK-20260908-0005',
            'member' => $members[1],
            'space' => $spaces[3],
            'diskon' => null,
            'tanggal' => Carbon::create(2026, 9, 8),
            'jam_mulai' => '14:00',
            'durasi_jam' => 2,
            'status' => 'belum_dikonfirm',
        ]);

        // 6. Dibatalkan - bulan lalu
        $buatReservasi([
            'kode_booking' => 'BOOK-20260820-0006',
            'member' => $members[2],
            'space' => $spaces[0],
            'diskon' => $diskonAktif,
            'tanggal' => Carbon::create(2026, 8, 20),
            'jam_mulai' => '09:00',
            'durasi_jam' => 6,
            'status' => 'dibatalkan',
        ]);

        // 7. Aktif (sedang berlangsung) - hari ini, sudah check-in
        $buatReservasi([
            'kode_booking' => 'BOOK-20260904-0007',
            'member' => $members[1],
            'space' => $spaces[5],
            'diskon' => $diskonAktif,
            'tanggal' => Carbon::today(),
            'jam_mulai' => '09:00',
            'durasi_jam' => 8,
            'status' => 'aktif',
            'check_in_at' => Carbon::now()->subHours(2),
        ]);

        $this->command->info('Seeder berhasil dijalankan!');
        $this->command->info('Admin: admin_demo / Admin123!');
        $this->command->info('Member: budi.member, siti.member, agus.member / Member123!');
    }
}
