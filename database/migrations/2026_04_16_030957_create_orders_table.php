<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk membuat tabel orders.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique(); // Kode unik seperti ORD001
            $table->string('customer'); // Nama pemesan
            $table->json('items'); // Menyimpan array menu yang dipesan (JSON)
            $table->integer('total'); // Total harga pesanan
            $table->string('status')->default('Menunggu'); // Status: Menunggu, Sedang Disiapkan, Siap Diambil, Selesai
            $table->timestamps();
        });
    }

    /**
     * Kembalikan migration (hapus tabel).
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};