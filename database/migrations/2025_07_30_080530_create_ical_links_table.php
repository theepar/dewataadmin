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
        Schema::create('ical_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_id')->nullable()->constrained('villas')->onDelete('set null');
            $table->foreignId('villa_unit_id')->nullable()->constrained('villa_units')->onDelete('set null'); // Tambah relasi ke unit
            $table->text('ical_url');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ical_links');
    }
};
