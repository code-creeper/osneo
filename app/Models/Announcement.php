<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = ['subject', 'body'];

    protected $casts = [
        'role_ids' => 'array'
    ];

    public function users(): BelongsToMany
    {
        return $this->recipients();
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('read_at');
    }

    public function readRecipients(): BelongsToMany
    {
        return $this->recipients()->wherePivotNotNull('read_at');
    }
}
