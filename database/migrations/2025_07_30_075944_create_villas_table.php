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
        Schema::create('villas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('ownership_status')->nullable(); // Ubah ke json
            $table->unsignedBigInteger('price_idr')->default(0);
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->json('amenities')->nullable();
            $table->unsignedTinyInteger('bedroom')->default(1);
            $table->unsignedTinyInteger('bed')->default(1);
            $table->unsignedTinyInteger('bathroom')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villas');
    }
};
