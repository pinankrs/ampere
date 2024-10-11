<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inquiry_details', function (Blueprint $table) {
            $table->bigInteger('branch_id')->default(0)->after('service_type_id');
            //$table->renameColumn('service_date', 'confirm_date');
        });

        DB::statement('ALTER TABLE inquiry_details CHANGE service_date confirm_date DATETIME');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiry_details', function (Blueprint $table) {
            //
        });
        DB::statement('ALTER TABLE inquiry_details CHANGE confirm_date service_date DATETIME');
    }
};
