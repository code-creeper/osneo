<?php

namespace App\Models;

use App\Traits\HasPreferences;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as BaseRole;
use Spatie\Translatable\HasTranslations;

class Role extends BaseRole
{
    use HasFactory;
    use HasPreferences;
    use HasTranslations;
}
