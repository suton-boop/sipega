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
        Schema::create('job_classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_name'); // e.g. "Kelas 1", "Kelas 10"
            $table->decimal('base_amount', 12, 2); // e.g. 5,900,000
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_classes');
    }
};
