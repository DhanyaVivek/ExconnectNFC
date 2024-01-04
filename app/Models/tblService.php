<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tblService extends Model
{
    use HasFactory;

    protected $fillable = [
        'services',
        'shop_id'
    ];
}
