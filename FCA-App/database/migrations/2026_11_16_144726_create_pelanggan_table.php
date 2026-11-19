<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')
                ->nullable()
                ->unique() // 1 user hanya boleh tertaut ke 1 data pelanggan
                ->constrained('users')
                ->nullOnDelete();
            $table->string('nama');
            $table->string('no_hp', 20)->unique();
            $table->text('alamat');
            $table->foreignId('id_paket')
                ->constrained('paket')
                ->restrictOnDelete(); // Cegah paket terhapus jika masih dipakai pelanggan
            $table->unsignedInteger('tarif_khusus')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->unsignedTinyInteger('siklus_tagihan'); // Berisi 5 atau 21
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};