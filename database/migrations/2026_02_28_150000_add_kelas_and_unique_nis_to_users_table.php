<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'kelas')) {
                $table->string('kelas', 50)->nullable()->after('nis');
                $table->index('kelas');
            }
        });

        if ($this->canAddUniqueNisIndex()) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('nis');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('users', 'users_nis_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_nis_unique');
            });
        }

        if (Schema::hasColumn('users', 'kelas')) {
            Schema::table('users', function (Blueprint $table) {
                if ($this->indexExists('users', 'users_kelas_index')) {
                    $table->dropIndex('users_kelas_index');
                }
                $table->dropColumn('kelas');
            });
        }
    }

    private function canAddUniqueNisIndex(): bool
    {
        if ($this->indexExists('users', 'users_nis_unique')) {
            return false;
        }

        $duplicates = DB::table('users')
            ->select('nis')
            ->whereNotNull('nis')
            ->where('nis', '!=', '')
            ->groupBy('nis')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('nis')
            ->all();

        if (count($duplicates) > 0) {
            throw new RuntimeException(
                'Tidak bisa menambahkan unique index users.nis karena ditemukan NIS duplikat: '.
                implode(', ', array_slice($duplicates, 0, 5))
            );
        }

        return true;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $results = DB::select('SHOW INDEX FROM '.$table.' WHERE Key_name = ?', [$indexName]);
            return count($results) > 0;
        }

        if ($driver === 'sqlite') {
            $results = DB::select("PRAGMA index_list('".$table."')");
            foreach ($results as $result) {
                if (($result->name ?? null) === $indexName) {
                    return true;
                }
            }
        }

        return false;
    }
};
