<?php

namespace Database\Seeders;

use App\Models\LeaveReason;
use Illuminate\Database\Seeder;

class LeaveReasonSeeder extends Seeder
{
    public function run(): void
    {
        $leave_reasons = array(
            array(
                "id" => 1,
                "name" => "Krankheit",
                "color" => "#ffbc00",
                "paid" => 1,
                "deductible" => 0,
            ),
            array(
                "id" => 2,
                "name" => "Krankheit Kind",
                "color" => "#ffa500",
                "paid" => 1,
                "deductible" => 0,
            ),
            array(
                "id" => 3,
                "name" => "Ãœberstundenabbau",
                "color" => "#a1a3a5",
                "paid" => 0,
                "deductible" => 0,
            ),
            array(
                "id" => 4,
                "name" => "Berufsschule",
                "color" => "#727cf5",
                "paid" => 1,
                "deductible" => 0,
            ),
            array(
                "id" => 5,
                "name" => "Fortbildung",
                "color" => "#5d478b",
                "paid" => 1,
                "deductible" => 0,
            ),
            array(
                "id" => 6,
                "name" => "Unentschuldigte Abwesenheit",
                "color" => "#ff3030",
                "paid" => 0,
                "deductible" => 0,
            ),
            array(
                "id" => 7,
                "name" => "Urlaub",
                "color" => "#0acf97",
                "paid" => 1,
                "deductible" => 1,
            ),
            array(
                "id" => 8,
                "name" => "Notdienst",
                "color" => "#a1e411",
                "paid" => 0,
                "deductible" => 0,
            ),
            array(
                "id" => 9,
                "name" => "Elternzeit",
                "color" => "#ffc21a",
                "paid" => 1,
                "deductible" => 0,
            ),
            array(
                "id" => 10,
                "name" => "AktivitÃ¤ten",
                "color" => "#2cedcd",
                "paid" => 1,
                "deductible" => 0,
            ),
        );

        foreach ($leave_reasons as $reason){
            LeaveReason::create($reason);
        }
    }
}
