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
        Schema::create('rest_correct_requests', function (Blueprint $table) {
        $table->id();
        // 外部キー制約。名前が長すぎるエラーを避けるため第2引数で名前を指定
        $table->foreignId('attendance_correct_request_id',)
            ->constrained('attendance_correct_requests')
            ->onDelete('cascade');
        $table->time('proposed_start_time');
        $table->time('proposed_end_time')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rest_correct_requests');
    }
};
