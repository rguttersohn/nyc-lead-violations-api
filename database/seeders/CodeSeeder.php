<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Code;

class CodeSeeder extends Seeder
{

    private array $code_explanations = [
        
        "616"=>"Peeling paint or paint on damaged subsurface presumed by law to contain lead.",
        "617"=>"Peeling paint or paint on damaged subsurface that was identified via testing as containing lead at > 0.5 mg/cm².",
        "624"=>"Peeling paint or paint on damaged subsurface that was identified via testing as containing lead at 0.5 mg/cm².",
        "618"=>"After a child was lead poisoned, landlord was audited but failed to provide required documentation of previous ongoing compliance with lead paint laws.",
        "619"=>"Landlord failed to perform annual inspections for lead hazards.",
        "620"=>"Landlord failed to document annual inspections for lead hazards.",
        "626"=>"Landlord failed to have all painted surfaces tested for lead  content.",
        "614"=>"Did not provide documentation that lead hazards abated before apartment was re-rented.",
        "623"=>"Did not provide documentation that lead hazards abated before apartment was re-rented.",
        "621"=>"Peeling paint on door and window friction surfaces, presumed by law to contain lead, was not abated before apartment was re-rented.",
        "622"=>"Paint on door and window friction surfaces that was identified via testing as containing lead at > 0.5 mg/cm² was not abated before apartment was re-rented.",
        "625"=>"Paint on door and window friction surfaces that was identified via testing as containing lead at  0.5 mg/cm² was not abated before apartment was re-rented."
         
    ];


    /**
     * Run the database seeds.
     */
    public function run(Code $code): void
    {
        foreach($this->code_explanations as $ordernumber=>$definition):
            
            $code->create([
                'ordernumber'=> $ordernumber,
                'definition' => $definition
            ]);

        endforeach;
    }
}
