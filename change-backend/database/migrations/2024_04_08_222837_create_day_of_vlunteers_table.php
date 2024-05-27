<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Volunteer_work;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('day_of_volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Volunteer_work::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('day_of_week');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_of_volunteers');
    }
};
