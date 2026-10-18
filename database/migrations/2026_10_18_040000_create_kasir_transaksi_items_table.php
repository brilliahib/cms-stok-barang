<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kasir_transaksi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_transaksi_id')
                ->constrained('kasir_transaksis')
                ->cascadeOnDelete();
            $table->foreignId('barang_id')
                ->nullable()
                ->constrained('barangs')
                ->nullOnDelete();
            $table->string('item_type', 20);
            $table->string('item_name', 255);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('line_total');
            $table->timestamps();

            $table->index(['barang_id', 'kasir_transaksi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasir_transaksi_items');
    }
};
