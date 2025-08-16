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
        Schema::create('ship_plans', function (Blueprint $table) {
            $table->id();
            $table->string('custom_id')->unique();
            $table->integer('employe_id')->nullable();
            $table->string('sku_method')->nullable();
            $table->string('market_place')->default('US');
            $table->string('fullment_capability')->nullable();
            $table->text('name')->nullable();
            $table->boolean('show_filter')->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->double('handling_cost')->default(0);
            $table->double('shipment_fee')->default(0);
            $table->softDeletes(); // For soft delete functionality
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_plans');
    }
};
