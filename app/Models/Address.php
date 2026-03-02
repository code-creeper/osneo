<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'is_service_location' => 'bool'
    ];

    public function fullAddress(): string
    {
        return "$this->street, $this->city $this->zip_code";
    }
}
