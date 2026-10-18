<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kasir_transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->uuid('submission_key')->unique();
            $table->timestamp('completed_at');
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('amount_received');
            $table->unsignedBigInteger('change_amount');
            $table->string('payment_method', 20)->default('cash');
            $table->timestamps();

            $table->index(['completed_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasir_transaksis');
    }
};
