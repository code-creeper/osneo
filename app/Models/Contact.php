<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

class Contact extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $casts = [
        'customer' => SchemalessAttributes::class,
        'is_company' => 'bool',
        'is_customer' => 'bool',
        'is_supplier' => 'bool',
    ];


    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $contact){
            if (!$contact->name){
                $contact->name = "$contact->first_name $contact->last_name";
            }
        });
    }

    public function scopeWithCustomer(): Builder
    {
        return $this->customer->modelScope();
    }

    public function scopeIsCustomer(Builder $query): Builder
    {
        return $query->where('is_customer', 1);
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
