<?php

namespace App\Models;

use App\Enums\DocumentPropertyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentProperty extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public $timestamps = false;

    protected $casts = [
        'type' => DocumentPropertyType::class,
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function scopeForNaming(Builder $query, $documentTypeId): Builder
    {
        return $query->where('document_type_id', $documentTypeId)
            ->where('active', 1)
            ->where('is_name', 1)
            ->latest('order');
    }
}
