<?php

use App\Models\CouncilHousing;
use Database\Seeders\CouncilHousingSeeder;
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
        Schema::create('council_housing', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('councildistrict')->constrained('council_districts', 'councildistrict');
            $table->date('relevance_start')->nullabe();       
            $table->date('relevance_end')->nullable();     
            $table->integer('units');
            $table->text('source');
        });

        $council_housing = new CouncilHousing();

        $seeder = new CouncilHousingSeeder();

        $seeder->run($council_housing);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_housing');
    }
};
