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
        Schema::create('prep_order_details', function (Blueprint $table) {
            $table->id();
            $table->string('prep_order_id');
            $table->string('product_id')->nullable();
            $table->string('fnsku')->nullable();
            $table->integer('qty')->default(1);
            $table->integer('pack')->default(1);
            $table->integer('status')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prep_order_details');
    }
};
