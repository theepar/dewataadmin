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
        Schema::create('ical_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_unit_id')->constrained('villa_units')->onDelete('cascade');

            $table->string('uid')->nullable();
            $table->string('summary')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status')->nullable(); // Status dari iCal property

            // Kolom tambahan untuk detail dari deskripsi iCal/Airbnb
            $table->string('guest_name')->nullable();
            $table->string('reservation_id')->nullable();
            $table->string('property_name')->nullable();
            $table->integer('jumlah_orang')->nullable();
            $table->integer('durasi')->nullable();
            $table->boolean('is_cancelled')->default(false); // Status batal

            $table->index(['villa_unit_id', 'start_date', 'end_date']);
            $table->unique(['villa_unit_id', 'uid']); // Composite unique

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ical_events');
    }
};
