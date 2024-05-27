<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Category;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('volunteer_works', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('address');
            $table->unsignedDecimal('point', 5, 2);
            $table->unsignedSmallInteger('count_worker');
            $table->string('status')->default('pending');
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_works');
    }
};
