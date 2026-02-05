<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            // Add sub_kategori_id column after kategori_id
            if (!Schema::hasColumn('pengaduans', 'sub_kategori_id')) {
                $table->foreignId('sub_kategori_id')->nullable()->after('kategori_id');
            }
            
            // Add lantai column after gedung_id
            if (!Schema::hasColumn('pengaduans', 'lantai')) {
                $table->string('lantai', 10)->nullable()->after('gedung_id');
            }
            
            // Add ruangan_id column after lantai
            if (!Schema::hasColumn('pengaduans', 'ruangan_id')) {
                $table->foreignId('ruangan_id')->nullable()->after('lantai');
            }
            
            // Add tanggal_kejadian column
            if (!Schema::hasColumn('pengaduans', 'tanggal_kejadian')) {
                $table->date('tanggal_kejadian')->nullable()->after('deskripsi');
            }
            
            // Add alasan_tolak column
            if (!Schema::hasColumn('pengaduans', 'alasan_tolak')) {
                $table->text('alasan_tolak')->nullable()->after('catatan_teknisi');
            }
            
            // Rename prioritas to urgensi if needed (keeping compatibility)
            // We'll use urgensi as alias since prioritas already exists
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->dropColumn(['sub_kategori_id', 'lantai', 'ruangan_id', 'tanggal_kejadian', 'alasan_tolak']);
        });
    }
};
