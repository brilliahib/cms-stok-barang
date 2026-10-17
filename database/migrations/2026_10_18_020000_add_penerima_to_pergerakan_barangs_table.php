<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pergerakan_barangs', function (Blueprint $table) {
            $table->string('penerima')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('pergerakan_barangs', function (Blueprint $table) {
            $table->dropColumn('penerima');
        });
    }
};
