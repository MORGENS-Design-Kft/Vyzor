<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Projects\Models\Project;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clarity_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
            $table->string('metric_name');
            $table->string('dimension1')->nullable();
            $table->string('dimension2')->nullable();
            $table->string('dimension3')->nullable();
            $table->json('data');
            $table->integer('num_of_days');
            $table->date('fetched_for');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clarity_insights');
    }
};
