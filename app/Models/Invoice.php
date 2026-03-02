<?php

namespace App\Models;

use App\Jobs\SendInvoiceToCreditReformJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

class Invoice extends Model
{
    use HasFactory;

    use SchemalessAttributesTrait;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $schemalessAttributes = [
        'lexoffice_payload',
        'creditreform_payload',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::created(function (self $invoice) {
            SendInvoiceToCreditReformJob::dispatch($invoice);
        });
    }

    public function scopeWithLexofficePayload(): Builder
    {
        return $this->lexoffice_payload->modelScope();
    }

    public function scopeWithCreditreformPayload(): Builder
    {
        return $this->creditreform_payload->modelScope();
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getVoucherAttribute(): object
    {
        return (object)$this->lexoffice_payload->voucher;
    }

    public function getPaymentAttribute(): object
    {
        return (object)$this->lexoffice_payload->payment;
    }

    public function getContactAttribute(): object
    {
        return (object)$this->lexoffice_payload->contact;
    }

    public function getCustomerAttribute()
    {
        if (isset($this->contact->company)) {
            return $this->contact->company['name'];
        }

        return $this->person->firstName." ".$this->person->lastName;
    }

    public function getPersonAttribute(): object|null
    {
        return isset($this->contact->person) ? (object) $this->contact->person : null;
    }

    public function getCreditreformAttribute()
    {
        return $this->creditreform_payload;
    }

    public function totalGrossAmount(): Attribute
    {
        return Attribute::make(get: fn() => $this->type == 'voucher'
            ? money($this->voucher?->totalGrossAmount,null, true)
            : money($this->voucher?->totalPrice['totalGrossAmount'],null, true)
        );
    }

    public function totalOpenAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => money($this->creditreform?->totalOpenGrossAmount ?? $this->payment?->openAmount, null, true)
        );
    }

    public function status(): Attribute
    {
        return Attribute::make(
            get: fn() => ucfirst($this->creditreform?->currentState ?? $this->payment?->voucherStatus)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function isVoucher(): bool
    {
        return $this->type == 'voucher';
    }

}
