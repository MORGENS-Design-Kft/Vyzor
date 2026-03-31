<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clarity_insights', function (Blueprint $table) {
            $table->date('date_from')->nullable()->after('num_of_days');
            $table->date('date_to')->nullable()->after('date_from');
        });

        // Backfill existing records
        DB::table('clarity_insights')->orderBy('id')->each(function ($row) {
            $dateTo = \Carbon\Carbon::parse($row->fetched_for)->toDateString();
            $dateFrom = \Carbon\Carbon::parse($row->fetched_for)->subDays($row->num_of_days - 1)->toDateString();

            DB::table('clarity_insights')->where('id', $row->id)->update([
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);
        });

        Schema::table('clarity_insights', function (Blueprint $table) {
            $table->date('date_from')->nullable(false)->change();
            $table->date('date_to')->nullable(false)->change();
            $table->dropColumn('num_of_days');
        });
    }

    public function down(): void
    {
        Schema::table('clarity_insights', function (Blueprint $table) {
            $table->integer('num_of_days')->default(1)->after('data');
        });

        DB::table('clarity_insights')->orderBy('id')->each(function ($row) {
            $days = \Carbon\Carbon::parse($row->date_from)->diffInDays(\Carbon\Carbon::parse($row->date_to)) + 1;

            DB::table('clarity_insights')->where('id', $row->id)->update([
                'num_of_days' => $days,
            ]);
        });

        Schema::table('clarity_insights', function (Blueprint $table) {
            $table->dropColumn(['date_from', 'date_to']);
        });
    }
};
