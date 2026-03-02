<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class DocumentType extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public array $translatable = ['name'];

    public $timestamps = false;

    protected $casts = [
        'subscriber_ids' => 'array',
        'lexoffice' => 'bool',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::created(function () {
            cache()->forget('document_types');
        });

        self::updated(function () {
            cache()->forget('document_types');
        });

        self::deleted(function () {
            cache()->forget('document_types');
        });
    }

    public function properties(): HasMany
    {
        return $this->hasMany(DocumentProperty::class);
    }

    public function scopeForLexOffice(Builder $query, int $lexOffice = 1): Builder
    {
        return $query->where('lexoffice', $lexOffice);
    }

    public static function isLexoffice($key): bool
    {
        return in_array($key, ['R', 'GUS', 'STORNO', 'POS', 'MAHNUNG', 'KASSE']);
    }
}
