<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run()
    {
        $contacts = \DB::table('debkre')->get();

        foreach ($contacts as $contact) {
            Contact::firstOrCreate([
               'name' => $contact->name,
               'description' => $contact->description,
               'number' => $contact->number,
               'type' => $contact->typ,
               'active' => !$contact->isDisabled,
            ], []);
        }
    }
}
