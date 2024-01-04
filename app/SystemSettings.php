<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class SystemSettings extends Model
{
    protected $fillable = [
        'systemname', 'contact', 'email', 'description', 'subname', 'currency',
    ];
    public static function systemname($x) 
    {
      $par = "";

      $agents1 = DB::table('system_settings')->pluck($x); 

      foreach ($agents1 as $agent1=>$value1) {
          $par = $value1;
    	}
    	return $par;
    }
}
