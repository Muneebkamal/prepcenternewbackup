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
        Schema::create('ship_plan_details', function (Blueprint $table) {
            $table->id();
            $table->integer('ship_plan_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('boxes')->nullable();
            $table->integer('units')->nullable();
            $table->date('expiration')->nullable();
            $table->integer('template')->nullable();
            $table->softDeletes(); // For soft delete functionality
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_plan_details');
    }
};
