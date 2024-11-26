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
        $twillFilesTable = config('twill.files_table', 'twill_files');

        Schema::table($twillFilesTable, function (Blueprint $table) {
            $table->string('duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $twillFilesTable = config('twill.files_table', 'twill_files');
        Schema::table($twillFilesTable, function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
