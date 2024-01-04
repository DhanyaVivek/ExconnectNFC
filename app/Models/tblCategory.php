<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class tblCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'category',
      
    ];
    public static function categoryfind($x) 
    {
      $par = '';

      $agents1 = DB::table('tbl_categories')->where(['id' => $x])->pluck('category'); 

      foreach ($agents1 as $agent1=>$value1) {
          $par = $value1;
      }
      return $par;
    }

    // Find Top Category from ID 

    

    // Find Category from Topcategory 

    

    // Find Subcategory from ID 

    public static function subcategoryfind($x) 
    {
      $par = '';

      $agents1 = DB::table('tbl_subcategories')->where(['id' => $x])->pluck('subcategory'); 

      foreach ($agents1 as $agent1=>$value1) {
          $par = $value1;
      }
      return $par;
    }

    // Find SecSubcategory from ID 

    public static function secsubcategoryfind($x) 
    {
      $par = '';

      $agents1 = DB::table('tbl_secsubcategories')->where(['id' => $x])->pluck('secsubcategory'); 

      foreach ($agents1 as $agent1=>$value1) {
          $par = $value1;
      }
      return $par;
    }
    public static function attrifind($x) 
    {

      return   $data['attri'] = DB::table('tbl_attributes')->where(['product_id' => $x])->count();
      
    }
}
