<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Option 1: Modify the ENUM to include 'preparing' if it's an ENUM field
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'preparing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        // Option 2 (commented out): Alternatively, convert to VARCHAR for more flexibility
        // Schema::table('orders', function (Blueprint $table) {
        //     DB::statement("ALTER TABLE orders MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original ENUM without 'preparing'
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
