<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel menus.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('desc')->nullable();
            $table->integer('price');
            $table->string('time')->nullable(); // Waktu penyajian, misal: '10 Menit'
            $table->string('category'); // Makanan Utama, Camilan, Minuman, dll
            $table->integer('stock')->default(0);
            $table->text('image')->nullable(); // URL gambar
            $table->timestamps();
        });
    }

    /**
     * Kembalikan migration (hapus tabel).
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};