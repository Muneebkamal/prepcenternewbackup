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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('item')->nullable();
            $table->string('msku')->nullable();
            $table->string('asin')->nullable();
            $table->string('fnsku')->nullable();
            $table->integer('pack')->default(1);
             $table->boolean('poly_bag')->default(0); // Checkbox value
            $table->string('poly_bag_size')->nullable();
            $table->boolean('shrink_wrap')->default(0); // Checkbox value
            $table->string('shrink_wrap_size')->nullable();
            $table->integer('no_of_pcs_in_carton')->nullable();
            $table->string('carton_size')->nullable();
            $table->string('label_1')->nullable();
            $table->string('label_2')->nullable();
            $table->string('label_3')->nullable();
            $table->string('packing_link')->nullable();
            $table->string('ti_in_item_page')->nullable();
            $table->string('bubble_wrap')->nullable();
            $table->string('weight')->nullable();
            $table->string('weight_oz')->nullable();
            $table->string('weight_lb')->nullable();
            $table->string('cotton_size_sales')->nullable();
            $table->longText('image')->nullable();
            $table->string('use_orignal_box')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
