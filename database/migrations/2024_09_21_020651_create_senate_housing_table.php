<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SenateHousing;
use Database\Seeders\SenateHousingSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('senate_housing', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('senatedistrict')->constrained('senate_districts', 'senatedistrict');
            $table->date('relevance_start')->nullabe();       
            $table->date('relevance_end')->nullable();     
            $table->integer('units');
            $table->text('source');
        });

        $senate_housing = new SenateHousing();

        $seeder = new SenateHousingSeeder();

        $seeder->run($senate_housing);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('senate_housing');
    }
};
