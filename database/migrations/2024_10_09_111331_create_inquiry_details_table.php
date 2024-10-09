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
        Schema::create('inquiry_details', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('mobile', 12)->nullable();
            $table->bigInteger('service_type_id')->default(0);
            $table->bigInteger('status_id')->default(1);
            $table->dateTime('inquiry_date')->nullable();
            $table->dateTime('service_date')->nullable();
            $table->text('address')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_details');
    }
};
