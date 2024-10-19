<?php

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
        $status = Status::where('currentstatusid', 6)->first();

        if(!$status){
            return;
        }

        $status->definition = "The property owner applied to HPD to extend the correction date for the violation, and HPD granted the request.";

        $status->save();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
