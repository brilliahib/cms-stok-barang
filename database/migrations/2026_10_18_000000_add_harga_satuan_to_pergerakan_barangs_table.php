<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pergerakan_barangs', function (Blueprint $table) {
            $table->decimal('harga_satuan', 10, 2)->nullable()->after('jumlah');
        });

        Schema::table('barangs', function (Blueprint $table) {
            $table->decimal('harga', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('barangs')->whereNull('harga')->update(['harga' => 0]);

        Schema::table('barangs', function (Blueprint $table) {
            $table->decimal('harga', 10, 2)->nullable(false)->change();
        });

        Schema::table('pergerakan_barangs', function (Blueprint $table) {
            $table->dropColumn('harga_satuan');
        });
    }
};
