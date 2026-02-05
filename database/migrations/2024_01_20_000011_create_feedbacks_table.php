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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('rating_respon')->comment('1-5 rating for response speed');
            $table->tinyInteger('rating_kualitas')->comment('1-5 rating for repair quality');
            $table->tinyInteger('rating_pelayanan')->comment('1-5 rating for service quality');
            $table->text('komentar')->nullable();
            $table->boolean('is_satisfied');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('pengaduan_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
