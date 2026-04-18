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
        Schema::create('master_data_import_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('school_key')->nullable();
            $table->string('mode', 30)->default('replace_safe');
            $table->string('scope', 30)->default('full');
            $table->string('status', 30)->default('uploaded');
            $table->string('filename');
            $table->string('storage_path');
            $table->string('checksum', 64)->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('summary_json')->nullable();
            $table->json('errors_json')->nullable();
            $table->json('warnings_json')->nullable();
            $table->string('error_report_path')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('school_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data_import_batches');
    }
};
