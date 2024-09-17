<?php

use Database\Seeders\CodeSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Code;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('codes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->smallInteger('ordernumber')->unique();
            $table->text('definition');
        });

        $code = new Code();
        $seed = new CodeSeeder($code);

        $seed->run($code);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codes');
    }
};
