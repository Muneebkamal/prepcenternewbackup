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
        Schema::create('packing_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('template_name');
            $table->string('template_type');
            $table->integer('units_per_box')->nullable();
            $table->float('box_length')->nullable();
            $table->float('box_width')->nullable();
            $table->float('box_height')->nullable();
            $table->float('box_weight')->nullable();
            $table->string('labeling_by')->nullable();
            $table->boolean('original_pack')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_templates');
    }
};
