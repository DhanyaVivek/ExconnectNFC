<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class tblCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_name',
        'office_mail',
        'address_line1',
        'address_line2',
        'pincode',
        'website',
        'fb',
        'linkedin',
        'instagram',
        'twitter',
        'youtube',
        'gpay',
        'paytm',
        'phonepe',
        'jupiter',
        'service1',
        'service2',
        'service3',
        'service4',
        'service5',
        'gallery1',
        'gallery2',
        'gallery3',
        'gallery4',
        'regid',
        'secondary_contact'
    ];
}
