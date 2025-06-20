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
        Schema::create('daily_inputs', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->nullable();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('total_time_in_sec')->nullable();
            $table->integer('total_paid')->default(0);
            $table->double('total_packing_cost')->default(0);
            $table->double('total_item_hour')->default(0);
            $table->double('rate')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_inputs');
    }
};
