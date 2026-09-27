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
        Schema::create('serviceman_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serviceman_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('submission_date')->nullable();
            $table->date('awarded_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serviceman_awards');
    }
};
