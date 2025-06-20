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
        Schema::create('daily_input_details', function (Blueprint $table) {
            $table->id();
            $table->integer('daily_input_id');
            $table->string('fnsku')->nullable();
            // $table->string('item')->nullable();
            $table->integer('qty')->default(1);
            $table->integer('pack')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_input_details');
    }
};
