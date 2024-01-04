<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class tblShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'first_name',
        'last_name',
        'org_name',
        'designation',
        'theme',
        'primary_contact',
        'secondary_contact',
        'personal_mail',
        'office_mail',
        'address_line1',
        'address_line2',
        'pincode',
        'website',
        'fb',
        'country_code',
        'whatsapp',
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
        'agentid',
        'regid',
        'shortbio',
        'margin',
        'salespartnerid',
        'regstatius',
       'googlereview', 

    ];
    public static function companyusercount($id)
    {
      $par = "";
      $agents1 = DB::table('tbl_shops')->where(['agentid' => $x])->count(); 

      return $par;
    }
}
