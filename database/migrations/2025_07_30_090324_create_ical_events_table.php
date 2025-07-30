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
            // Foreign Key ke tabel 'ical_links'
            $table->foreignId('ical_link_id')->constrained('ical_links')->onDelete('cascade');

            $table->string('uid')->unique()->nullable();
            $table->string('summary')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status')->nullable(); // Status dari iCal property

            // Kolom tambahan untuk detail dari deskripsi iCal
            $table->string('guest_name')->nullable();
            $table->string('reservation_id')->nullable();
            $table->boolean('is_cancelled')->default(false); // Status batal

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
