<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaduan_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduans')->cascadeOnDelete();
            $table->timestamp('from_start')->nullable();
            $table->timestamp('to_start')->nullable();
            $table->timestamp('from_end')->nullable();
            $table->timestamp('to_end')->nullable();
            $table->text('reason');
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['pengaduan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduan_schedules');
    }
};

