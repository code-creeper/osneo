<?php

namespace App\Models;

use App\ActivityFormatters\TagActivityFormatter;
use App\Contracts\ActivityFormatter;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model implements ActivityFormatter
{
    use HasFactory, LogsActivity;
    use TagActivityFormatter;

    protected $fillable = [
        'name', 'color', 'model'
    ];

	/*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function leaves(): MorphToMany
    {
        return $this->morphedByMany(Leave::class, 'taggable');
    }
}
