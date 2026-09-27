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
        Schema::create('servicemen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();

            // Персональні дані
            $table->string('full_name');
            $table->string('rank')->nullable();
            $table->string('position')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('family_contact')->nullable();
            $table->string('status')->default('in_progress');

            // Лікування
            $table->date('evacuation_date')->nullable();
            $table->text('diagnosis')->nullable();
            $table->string('severity')->nullable();
            $table->boolean('has_amputation')->default(false);
            $table->boolean('has_prosthetic')->default(false);
            $table->boolean('has_certificate_5')->default(false);
            $table->string('ukopfo_group')->nullable();
            $table->date('ecopfo_date')->nullable();
            $table->string('disability_group')->default('none');
            $table->unsignedTinyInteger('work_capacity_loss_percent')->nullable();
            $table->string('facility_type')->nullable();
            $table->string('facility_city')->nullable();
            $table->string('facility_name')->nullable();

            // Виплати
            $table->string('material_aid_status')->default('not_applicable');

            // Дата контактів
            $table->date('first_contact_after_evacuation_date')->nullable();
            $table->date('last_contact_date')->nullable();
            $table->date('last_visit_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicemen');
    }
};
