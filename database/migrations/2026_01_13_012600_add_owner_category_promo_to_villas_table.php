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
        Schema::table('villas', function (Blueprint $table) {
            // Contact info for villa owner
            $table->json('owner_contact')->nullable()->after('amenities');

            // Villa category type
            $table->string('category')->nullable()->after('owner_contact');

            // Promo information
            $table->json('promo')->nullable()->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            $table->dropColumn(['owner_contact', 'category', 'promo']);
        });
    }
};
