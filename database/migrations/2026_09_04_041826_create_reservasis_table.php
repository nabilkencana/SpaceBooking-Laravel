<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservasis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking', 50)->unique();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('space_id')->constrained('spaces')->restrictOnDelete();
            $table->foreignId('diskon_id')->nullable()->constrained('diskons')->nullOnDelete();
            $table->date('tanggal_reservasi');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('durasi_jam');
            $table->integer('harga_per_jam');
            $table->integer('total_harga_awal');
            $table->integer('potongan_diskon')->default(0);
            $table->integer('total_bayar');
            $table->enum('status', ['belum_dikonfirm', 'disetujui', 'aktif', 'selesai', 'dibatalkan'])->default('belum_dikonfirm');
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->timestamps();

            $table->index('member_id');
            $table->index('status');
            $table->index(['space_id', 'tanggal_reservasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservasis');
    }
};