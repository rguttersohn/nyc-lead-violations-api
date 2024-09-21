<?php

use App\Models\AssemblyHousing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\AssemblyHousingSeeder;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assembly_housing', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('assemblydistrict')->constrained('assembly_districts', 'assemblydistrict');
            $table->date('relevance_start')->nullabe();       
            $table->date('relevance_end')->nullable();     
            $table->integer('units');
            $table->text('source');
        });

        
        $assembly_housing = new AssemblyHousing();

        $assembly_seeder = new AssemblyHousingSeeder();

        $assembly_seeder->run($assembly_housing);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembly_housing');
    }
};
