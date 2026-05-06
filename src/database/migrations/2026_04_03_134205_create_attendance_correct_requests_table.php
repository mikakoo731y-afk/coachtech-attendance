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
        Schema::create('attendance_correct_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->time('proposed_clock_in')->nullable();
        $table->time('proposed_clock_out')->nullable();
        $table->text('reason'); // 修正理由（備考）
        $table->tinyInteger('status')->default(1)->comment('1:承認待ち, 2:承認済み');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_correct_requests');
    }
};
