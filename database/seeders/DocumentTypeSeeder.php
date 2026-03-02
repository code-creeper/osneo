<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use DB;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        DocumentType::resetAutoIncrement();

        $types = config('constants.document_types');

        foreach ($types as $key => $name) {
            DocumentType::updateOrCreate([
                'key' => $key,
                'name' => $name,
                'lexoffice' => DocumentType::isLexoffice($key)
            ]);
        }

    }
}
