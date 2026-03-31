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
        Schema::table('clarity_insights', function (Blueprint $table) {
            $table->dateTime('fetched_for')->change();
        });
    }

    public function down(): void
    {
        Schema::table('clarity_insights', function (Blueprint $table) {
            $table->date('fetched_for')->change();
        });
    }
};
