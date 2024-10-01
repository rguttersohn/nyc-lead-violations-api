<?php

use Database\Seeders\StatusSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Status;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('currentstatusid')->unique();
            $table->tinyText('name');
            $table->text('definition')->nullable();
        });

        $status = new Status();

        $seeder = new StatusSeeder();

        $seeder->run($status);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
