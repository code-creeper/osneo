<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class LeaveReason extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];

    public $timestamps = false;

    public array $translatable = ['name'];

    protected $casts = [
        'paid' => 'bool',
        'deductible' => 'bool',
    ];

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'reason_id');
    }

}
