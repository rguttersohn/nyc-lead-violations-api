<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE INDEX violations_ix ON violations (building_id, inspectiondate);');

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Db::statement('DROP INDEX IF EXISTS violations_ix');
    }
};
