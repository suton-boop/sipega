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
        Schema::table('meetings', function (Blueprint $table) {
            $table->text('agenda')->nullable();
            $table->text('minutes_text')->nullable();
            $table->string('minutes_file_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->enum('type', ['Working Day', 'Shared Leave', 'Holiday', 'Overtime'])->default('Working Day');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['agenda', 'minutes_text', 'minutes_file_path', 'created_by', 'is_active']);
        });
    }
};
