<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('space_owners')->restrictOnDelete();
            $table->string('nama_space', 100);
            $table->integer('harga_per_jam');
            $table->enum('tipe', ['desk', 'meeting_room', 'private_office']);
            $table->integer('kapasitas');
            $table->text('deskripsi');
            $table->string('foto', 255)->nullable();
            $table->timestamps();

            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};