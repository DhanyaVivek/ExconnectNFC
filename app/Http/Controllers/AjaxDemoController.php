<?php

namespace App\Http\Controllers;
use DB;
use Auth;
use Illuminate\Http\Request;
use App\Stock;
use App\tblStockEntry;
use App\Cart;
use App\tblCart;
use App\tblWishlist;
use App\tblStocklog;
use App\User;


class AjaxDemoController extends Controller
{
  
  
    public function updatemcart(Request $request)
    {
          // $request->cart_id;
      //  return $request->all();

        $total_price = $request->countval * $request->priceval;
        $stotal_price = $request->countval * $request->spriceval;

        $data['data'] = DB::table('carts')->where('id', $request->cart_id)->update(['count' => $request->countval,
        'total_price' => $total_price,'seltotalprice' => $stotal_price]);
        return $stotal_price;
    }  
   
   
   
   
    public function selectprize(Request $request)
    {
        $agents = DB::table('stocks')->where([['id', '=', $request->id_country]])->pluck("MRP", "quantitytype")->all();
        $val = '';
        //$data = view('ajax-select',compact('states'))->render();
        //return response()->json(['options'=>$data]);
        foreach ($agents as $agent=>$value) {
           $val = $value.'#'.$agent;
      }
      return $val;
    }
    public function selectvarient(Request $request)
    {
        $states = DB::table('tbl_product_variants')->where(['product' => $request->id_country])->pluck("varient", "id")->all();
        $data = view('excart.ajax.selectvarient',compact('states'))->render();
        return response()->json(['options'=>$data]);
        
    }
      public function companyapprove(Request $request)
    {
        $states = DB::table('users')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function companyreject(Request $request)
    {
        $states = DB::table('users')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }
    public function varientfind(Request $request)
    {
        $agents = DB::table('tbl_cart_products')->where(['id' => $request->id_country])->get();
        $amt = '';
        $amount = '';
        $stockcount = '';
        foreach ($agents as $agent) 
        {
            if($agent->subscription == '0')
            {
                $stockcount = DB::table('stocks')->where(['productname' => $request->id_country])->count();
                if($stockcount == 1)
                {
                    $age = DB::table('stocks')->where(['productname' => $request->id_country])->get();
                        foreach ($age as $ages) {
                           if($ages->discountprice == "")
                            {
                                $amt = $ages->productprice;
                            }
                            else
                            {
                                $amt = $ages->discountprice;
                            }
                            $amount = $amt.'#'.$ages->id;
                        }
                }
            }
            else
            {
                if($agent->discountrate == "")
                {
                    $amt = $agent->actualrate;
                }
                else
                {
                    $amt = $agent->discountrate;
                }

                $amount = $amt.'#';
            }
        }

       return $amount;
    }

    public function varientpricefind(Request $request)
    {
        $amt = "";
        $amount = '';
        $age = DB::table('stocks')->where(['productname' => $request->product, 'productvariant' => $request->id_country])->get();
        foreach ($age as $ages) 
        {
            if($ages->discountprice == "")
            {
                $amt = $ages->productprice;
            }
            else
            {
                $amt = $ages->discountprice;
            }
            $amount = $amt.'#'.$ages->id;
        }
       return $amount;
    }

    public function selectdepartment(Request $request)
    {
        $states = DB::table('statuses')->where([['id', '=', $request->id_country]])->pluck("userid","id")->all();
        $data = view('ajax-department',compact('states'))->render();
        return response()->json(['options'=>$data]);
    }
    public function selectbottleprize(Request $request)
    {
        $productname = "";
        $quantitytype = "";
        $stockid = $request->prdt;

        $data = DB::table('stocks')->where([['id', '=', $stockid]])->get();

        foreach($data as $obj)
        {
           $productname = $obj->productname;
           $quantitytype = $obj->quantitytype; 
        }

        if(($productname != '') && ($quantitytype))
        {
            $charge = '';
            $newdata = DB::table('tbl_bottle_charges')->where([['productname', '=', $productname], ['quantitytype', '=', $quantitytype]])->get();

            foreach($newdata as $object)
            {
               $charge = $object->bottlecharge;
            }
        }
        return $charge;
    }

    // Category Approve Starts here

    public function categoryapprove(Request $request)
    {
        $states = DB::table('tbl_categories')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function categoryreject(Request $request)
    {
        $states = DB::table('tbl_categories')->where('id', $request->db_id)->update(['status' => '0']);
        $prds = DB::table('tbl_cart_products')->where('category', $request->db_id)->get();
        foreach ($prds as $key) {
            $dta = DB::table('tbl_cart_products')->where('id', $key->id)->update(['status' => '0']);
            $varts = DB::table('tbl_product_variants')->where('product', $key->id)->update(['status' => '0']);
        }
        
        return "Success";
    }

    public function cancelapprove(Request $request)
    {
        $date = date('Y-m-d');

        $states = DB::table('tbl_ordermasters')->where('id', $request->db_id)->update(['order_status' => '3',
        'cancel_date'=> $date]);
        $order = DB::table('orders')->where(['order_id' =>$request->db_id])->get();
   
        foreach ($order as $object) {
        $data = DB::table('tbl_stocks')->where(['product_id' =>$object->product_id,'size' =>$object->size])->get();
        foreach ($data as $obj) {
            $prequantity = $obj->quantity;
            $stockid = $obj->id;
              $curquantity =  $prequantity+$object->count;
        
             $varts = DB::table('tbl_stocks')->where('id', $stockid)->update(['quantity' => $curquantity]);
            
        }}
        $datao = DB::table('tbl_ordermasters')->where(['id' => $request->db_id])->get();
        foreach($datao as $objecto)
        {
            $fname = $objecto->fname;
            $phone = $objecto->phone;
            $orderr_id = $objecto->orderr_id;
        }
        $text = urlencode(' Hi '.$fname.', Your order '.$orderr_id.' is cancelled successfully. Refunds were initiated and will be credited to your account in 5 to 7 business days. LASH Boutique.');
        $opts = array(
          'http'=>array(
            'method'=>"GET",
            //'content' => "$parameters",
            'header'=>"Accept-language: en\r\n" .
                      "Cookie: foo=bar\r\n"
          )
        );
 
        $context = stream_context_create($opts);
 
        $fp = fopen("http://thesmsbuddy.com/api/v1/sms/send?key=5X8UY14g88jhtYtuk9NyB1ITWhs1Xpxn&type=1&to=".$phone."&sender=LASHBQ&message=".$text."&flash=0&template_id=1707168560952800664", "r", false, $context);
        $response = stream_get_contents($fp);
        fpassthru($fp);
        fclose($fp);

        return "Order Cancelled";
    }

    public function cancelreject(Request $request)
    {
        $states = DB::table('tbl_ordermasters')->where('id', $request->db_id)->update(['order_status' => '0']);
        $order = DB::table('orders')->where(['order_id' =>$request->db_id])->get();
   
        foreach ($order as $object) {
        $data = DB::table('tbl_stocks')->where(['product_id' =>$object->product_id,
        'size' =>$object->size])->get();
        foreach ($data as $obj) {
            $prequantity = $obj->quantity;
            $stockid = $obj->id;
              $curquantity =  $prequantity - $object->count;
        
             $varts = DB::table('tbl_stocks')->where('id', $stockid)->update(['quantity' => $curquantity]);
            
        }}
        
        return "Order approved";
    }

    public function categoryorder(Request $request)
    {
        $states = DB::table('tbl_categories')->where('id', $request->id)->update(['order' => $request->val]);
        return "Success";
    }

    //slider
    public function slidersorder(Request $request)
    {
        $states = DB::table('tbl_sliders')->where('id', $request->id)->update(['order' => $request->val]);
        return "Success";
    }
    public function slidersapprove(Request $request)
    {
        $states = DB::table('tbl_sliders')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function slidersreject(Request $request)
    {
        $states = DB::table('tbl_sliders')->where('id', $request->db_id)->update(['status' => '0']);
       
        
        return "Success";
    }
    public function shopapprove(Request $request)
    {
        $states = DB::table('tbl_shops')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function shopreject(Request $request)
    {
        $states = DB::table('tbl_shops')->where('id', $request->db_id)->update(['status' => '0']);
       
        
        return "Success";
    }
    //shop filter
    public function sortfind(Request $request)
    {
        
        $ascend =  $request->sortval;
        if($ascend =="priceasc")
        {
            return $states =DB::table('tbl_cart_products')
        ->orderBy('actualrate', 'asc');
       return   $ascend =  $request->sortval;

        }
       // return "Success";
    }
    // Sub Category Approve Starts here

    public function subcategoryapprove(Request $request)
    {
        $states = DB::table('tbl_subcategories')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function subcategoryreject(Request $request)
    {
        $states = DB::table('tbl_subcategories')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }
   //Product type
   // Sub Category Approve Starts here

   public function producttypeapprove(Request $request)
   {
       $states = DB::table('producttype')->where('id', $request->db_id)->update(['status' => '1']);
       return "Success";
   }

   public function productypereject(Request $request)
   {
       $states = DB::table('producttype')->where('id', $request->db_id)->update(['status' => '0']);
       return "Success";
   }

    // Merchant Approve Starts here

    public function merchantapprove(Request $request)
    {
        $states = DB::table('tbl_merchants')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function merchantreject(Request $request)
    {
        $states = DB::table('tbl_merchants')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }
    public function deliveryapprove(Request $request)
    {
        $states = DB::table('tbl_deliveries')->where('id', $request->db_id)->update(['status' => '1']);
        return "Approved";
    }
    public function deliveryreject(Request $request)
    {
        $states = DB::table('tbl_deliveries')->where('id', $request->db_id)->update(['status' => '0']);
        return "Rejected";
    }
    public function locationapprove(Request $request)
    {
        $states = DB::table('tbl_locations')->where('id', $request->db_id)->update(['status' => '1']);
        return "Approved";
    }
    public function locationreject(Request $request)
    {
        $states = DB::table('tbl_locations')->where('id', $request->db_id)->update(['status' => '0']);
        return "Rejected";
    }
    public function courierapprove(Request $request)
    {
        $states = DB::table('tbl_couriers')->where('id', $request->db_id)->update(['status' => '1']);
        return "Approved";
    }
    public function courierreject(Request $request)
    {
        $states = DB::table('tbl_couriers')->where('id', $request->db_id)->update(['status' => '0']);
        return "Rejected";
    }
    // Store Approve Strats here

    public function storeapprove(Request $request)
    {
        $states = DB::table('tbl_stores')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function storereject(Request $request)
    {
        $states = DB::table('tbl_stores')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }

    //  Category Looping starts from here
    public function sizelevel(Request $request)
    {
        
        
        
         $data['states'] =DB::table('tbl_sizes')->orderBy('id', 'asc')
                    ->get(["size","id"]);
        return response()->json($data);
         

    }
    public function colorlevel(Request $request)
    {
        
        
        
         $data['states'] =DB::table('tbl_colors')->orderBy('id', 'asc')
                    ->get(["color","id"]);
        return response()->json($data);
         

    }
    public function topcategorylevel(Request $request)
    {
        
        
        
         $data['states'] =DB::table('tbl_subcategories')->where("category",$request->country_id)
                    ->get(["subcategory","id"]);
        return response()->json($data);
         

    }

    public function subcategorylevel(Request $request)
    {
        $data['cities'] =DB::table('tbl_subcategories')->where("topcategory",$request->state_id)
           ->get(["subcategory","id"]);
        return response()->json($data);
    }

    public function secsubcatlevel(Request $request)
    {
        $data['cities'] =DB::table('tbl_secsubcategories')->where("topcategory",$request->state_id)
        ->get(["secsubcategory","id"]);
     return response()->json($data);
    }

    //Store find starts here

    public function storefindlevel(Request $request)
    {
        $states = DB::table('tbl_stores')->where(['merchant' => $request->id_country, 'status' => '1'])->pluck("storename","id")->all();
        $data = view('excart.ajax.store',compact('states'))->render();
        return response()->json(['options'=>$data]);
    }

    // Cart Product Approve Strats here

    public function cartproductapprove(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');
        $time = date('H:i');

        DB::table('tbl_product_approve_reject')->insert(['cart_id' => $request->db_id, 'approve_date' => $date, 'approve_time' => $time]);

        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['status' => '1']);
        return "Product Enabled";
    }

    public function cartproductreject(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');
        $time = date('H:i');

        DB::table('tbl_product_approve_reject')->insert(['cart_id' => $request->db_id, 'reject_date' => $date, 'reject_time' => $time]);

        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['status' => '0','enabledate'=>'']);
        return "Product Disabled";
    }

    // Featured Cart Product Approve Strats here

    public function featuredapprove(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['featured' => '1']);
        return "Add to featured Products";
    }

    public function featuredreject(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['featured' => '0']);
        return "Remove from featured products";
    }
    public function bestapprove(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['best' => '1']);
        return "Add to Best sellers";
    }

    public function bestreject(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['best' => '0']);
        return "Remove from Best sellers";
    }
    public function offerapprove(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['offer' => '1']);
        return "Add to Offerzone";
    }

    public function offerreject(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['offer' => '0']);
        return "Remove from Offerzone";
    }

    // Top Cart Product Approve Strats here

    public function topapprove(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['top' => '1']);
        return "Success";
    }

    public function topreject(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['top' => '0']);
        return "Success";
    }

    // Pincode Approve Strats here

    public function pincodeapprove(Request $request)
    {
        $states = DB::table('tbl_pincodes')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function pincodereject(Request $request)
    {
        $states = DB::table('tbl_pincodes')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }

    // Banner Approve Strats here

    public function bannerapprove(Request $request)
    {
        $states = DB::table('tbl_appbanners')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function bannerreject(Request $request)
    {
        $states = DB::table('tbl_appbanners')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }

    // Ads Iamge Approve Strats here

    public function adsapprove(Request $request)
    {
        $states = DB::table('tbl_adimages')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function adsreject(Request $request)
    {
        $states = DB::table('tbl_adimages')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }

    // Variant Approve Strats here

    public function variantapprove(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');
        $time = date('H:i');

        DB::table('tbl_product_approve_reject')->insert(['varient_id' => $request->db_id, 'approve_date' => $date, 'approve_time' => $time]);


        $states = DB::table('tbl_product_variants')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function variantreject(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');
        $time = date('H:i');
        
        DB::table('tbl_product_approve_reject')->insert(['varient_id' => $request->db_id, 'reject_date' => $date, 'reject_time' => $time]);
        
        $states = DB::table('tbl_product_variants')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }

    //  Load Variant name from Product

    public function selectproductlevel(Request $request)
    {
        $states = DB::table('tbl_product_variants')->where(['product' => $request->id_country, 'status' => '1'])->pluck("varient","id")->all();
        $data = view('excart.stock.ajax-pgm-select',compact('states'))->render();
        return response()->json(['options'=>$data]);
    }

    // Delivery Charge Approve Strats here

    public function delchargeapprove(Request $request)
    {
        DB::table('tbl_deliverycharges')->update(['status' => '0']);
        $states = DB::table('tbl_deliverycharges')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function delchargereject(Request $request)
    {
        $states = DB::table('tbl_deliverycharges')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }

    // Subscription Product Approve Strats here

    public function subproductapprove(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function subproductreject(Request $request)
    {
        $states = DB::table('tbl_cart_products')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }

    public function pincodevliditycheck(Request $request)
    {
        //$agents = DB::table('tbl_pincodes')->where([['pincode', '=', $request->id_country]])->pluck("status")->all();

        $agents = DB::table('tbl_pincodes')->where([['pincode', '=', $request->id_country]])->get();
        $val = 0;
        $min = 0;
        $del = 0;
        $par = "";
        
        // foreach ($agents as $agent=>$value) {
        //    $val = $value;
        // }
        // return $val;

        foreach ($agents as $agent) {
           $val = $agent->status;
           $del = $agent->delcharge;
           $min = $agent->minamount;
        }
        $par = $val.'#'.$del.'#'.$min;
        return $par;
    }

    // Category city Approve Starts here

    public function categorycityapprove(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');
        //$user = Auth::user()->id;

        DB::table('tbl_available_category')->insert(['city' => $request->city, 'category' => $request->cat, 'date' => $date]);

        return "sss";
    }

    public function categorycityreject(Request $request)
    {
        DB::table('tbl_available_category')->where(['city' => $request->city, 'category' => $request->cat])->delete();
        return "eee";
    }


    public function topcategorycityapprove(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');
        //$user = Auth::user()->id;

        DB::table('tbl_available_category')->insert(['city' => $request->city, 'topcategory' => $request->cat, 'date' => $date]);

        return "sss";
    }

    public function topcategorycityreject(Request $request)
    {
        DB::table('tbl_available_category')->where(['city' => $request->city, 'topcategory' => $request->cat])->delete();
        return "eee";
    }

    // Product available in city

    public function productavlablefnapprove(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');

        $data = DB::table('tbl_cart_products')->where(['subscription' => '0'])->orderBy('id', 'desc')->get();

        foreach ($data as $obj) {
            $id = $obj->id;

            DB::table('tbl_available_product')->insert(['city' => $request->city, 'product' => $id, 'date' => $date]);

        }

        return "success";
    }
    public function productavlablefnreject(Request $request)
    {
        $data = DB::table('tbl_cart_products')->where(['subscription' => '0'])->orderBy('id', 'desc')->get();

        foreach ($data as $obj) {
            $id = $obj->id;

            DB::table('tbl_available_product')->where(['city' => $request->city, 'product' => $id])->delete();

        }
        
        return "success";
    }

    public function productavlablefnindapprove(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');

        DB::table('tbl_available_product')->insert(['city' => $request->city, 'product' => $request->produt, 'date' => $date]);
        return "success";
    }
    public function productavlablefnindreject(Request $request)
    {
        DB::table('tbl_available_product')->where(['city' => $request->city, 'product' => $request->produt])->delete();

        return "success";
    }

    public function varientavlablefnfnapprove(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');

        $data = DB::table('tbl_product_variants')->orderBy('id', 'desc')->get();

        foreach ($data as $obj) {
            $id = $obj->id;

            DB::table('tbl_available_product')->insert(['city' => $request->city, 'varient' => $id, 'date' => $date]);

        }

        return "success";
    }
    public function varientavlablefnfnreject(Request $request)
    {
        $data = DB::table('tbl_product_variants')->orderBy('id', 'desc')->get();

        foreach ($data as $obj) {
            $id = $obj->id;

            DB::table('tbl_available_product')->where(['city' => $request->city, 'varient' => $id])->delete();

        }
        
        return "success";
    }
    public function varientavlablefnindapprove(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');

        DB::table('tbl_available_product')->insert(['city' => $request->city, 'varient' => $request->produt, 'date' => $date]);
        return "success";
    }
    public function varientavlablefnindreject(Request $request)
    {
        DB::table('tbl_available_product')->where(['city' => $request->city, 'varient' => $request->produt])->delete();

        return "success";
    } 

    public function subproductavlablefnapprove(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $date = date('Y-m-d');

        $data = DB::table('tbl_cart_products')->where(['subscription' => '1'])->orderBy('id', 'desc')->get();

        foreach ($data as $obj) {
            $id = $obj->id;

            DB::table('tbl_available_product')->insert(['city' => $request->city, 'product' => $id, 'date' => $date]);

        }

        return "success";
    }
    public function subproductavlablefnreject(Request $request)
    {
        $data = DB::table('tbl_cart_products')->where(['subscription' => '1'])->orderBy('id', 'desc')->get();

        foreach ($data as $obj) {
            $id = $obj->id;

            DB::table('tbl_available_product')->where(['city' => $request->city, 'product' => $id])->delete();

        }
        
        return "success";
    }
    public function selectpackage(Request $request)
    {
        $states = DB::table('tbl_subscription_days')->where(['product' => $request->product])->pluck("subscriptiondays", "id")->all();
        $data = view('excart.ajax.selectpackage',compact('states'))->render();
        return response()->json(['options'=>$data]);
        
    }
    public function subscriptionproductprice(Request $request)
    {
        $par = 0;
        $data = DB::table('tbl_cart_products')->where(['id' => $request->product])->get();

        foreach ($data as $obj) {
            $par = $obj->actualrate;
        }
        
        return $par;
    }
    public function selectpackagedata(Request $request)
    {
        $val = "";
        $agents = DB::table('tbl_subscription_days')->where(['id' => $request->id])->pluck("subscriptiondays");
        foreach ($agents as $agent=>$value) {
           $val = $value;
        }
        return $val;
    }

    //  Stock updation

    public function stockdirect(Request $request)
    {
        $val = "Stock Updated Successfully";
        $id = $request->stockid;

        $qt = "";
        $PurPrize = "";
        $productprice = "";
        $discountprice = "";

        $data = DB::table('stocks')->where(['id' => $id])->get();
        foreach ($data as $obj) {
            $quantitytype = $obj->quantitytype;
            $PurPrize = $obj->PurPrize;
            $productprice = $obj->productprice;
            $discountprice = $obj->discountprice;
        }


        $Quantity = $request->quantity;
        $productquantitytype = 'Nos';
        $request['Quantity'] = $Quantity;
        $userUpdate  = Stock::where('id',$id)->first();
        if ($userUpdate) {
           $speak = $userUpdate->update($request->all());
        }

        $balance = $request->balance;
        $cur_qty = $request->quantity;
        $qty = $cur_qty;// - $balance;
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        
        $request['productname'] = $request->productname;
        $request['productvariant'] = "";
        $request['quantitytype'] = $quantitytype;
        $request['purchaeprice'] = $PurPrize;
        $request['productprice'] = $productprice;
        $request['discountprice'] = $discountprice;
        $request['quantity'] = $qty;
        $request['productquantitytype'] = 'Nos';
        $request['date'] = date('Y-m-d');
        $request['description'] = "";
        $request['stockid'] = $id;
        $request['time'] = date('H:i');

        tblStockEntry::create($request->all());

        return $val;
    }
    public function varientstockdirect(Request $request)
    {
        $val = "Varient Stock Updated Successfully";
        $id = $request->stockid;

        $qt = "";
        $PurPrize = "";
        $productprice = "";
        $discountprice = "";

        $data = DB::table('stocks')->where(['id' => $id])->get();
        foreach ($data as $obj) {
            $quantitytype = $obj->quantitytype;
            $PurPrize = $obj->PurPrize;
            $productprice = $obj->productprice;
            $discountprice = $obj->discountprice;
        }


        $Quantity = $request->quantity;
        $productquantitytype = 'Nos';
        $request['Quantity'] = $Quantity;
        $userUpdate  = Stock::where('id',$id)->first();
        if ($userUpdate) {
           $speak = $userUpdate->update($request->all());
        }

        $balance = $request->balance;
        $cur_qty = $request->quantity;
        $qty = $cur_qty;// - $balance;

        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');

        $request['productname'] = $request->productname;
        $request['productvariant'] = $request->varient;
        //$request['productvariant'] = $request->input('productvariant');
        $request['quantitytype'] = $quantitytype;
        $request['purchaeprice'] = $PurPrize;
        $request['productprice'] = $productprice;
        $request['discountprice'] = $discountprice;
        $request['quantity'] = $qty;
        $request['productquantitytype'] = 'Nos';
        $request['date'] = date('Y-m-d');
        $request['description'] = "";
        $request['stockid'] = $id;
        $request['time'] = date('H:i');

        tblStockEntry::create($request->all());

        return $val;
    }

    
    // Theme Approve Starts here

    public function themeapprove(Request $request)
    {
        $states = DB::table('tbl_themes')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function themereject(Request $request)
    {
        $states = DB::table('tbl_themes')->where('id', $request->db_id)->update(['status' => '0']);
        $prds = DB::table('tbl_cart_products')->where('category', $request->db_id)->get();
        foreach ($prds as $key) {
            $dta = DB::table('tbl_cart_products')->where('id', $key->id)->update(['status' => '0']);
            $varts = DB::table('tbl_product_variants')->where('product', $key->id)->update(['status' => '0']);
        }
        
        return "Success";
    }

    public function themeorder(Request $request)
    {
        $states = DB::table('tbl_themes')->where('id', $request->id)->update(['order' => $request->val]);
        return "Success";
    }

    // Shape Approve Starts here

    public function shapeapprove(Request $request)
    {
        $states = DB::table('tbl_shapes')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function shapereject(Request $request)
    {
        $states = DB::table('tbl_shapes')->where('id', $request->db_id)->update(['status' => '0']);
        $prds = DB::table('tbl_cart_products')->where('category', $request->db_id)->get();
        foreach ($prds as $key) {
            $dta = DB::table('tbl_cart_products')->where('id', $key->id)->update(['status' => '0']);
            $varts = DB::table('tbl_product_variants')->where('product', $key->id)->update(['status' => '0']);
        }
        
        return "Success";
    }

    public function shapeorder(Request $request)
    {
        $states = DB::table('tbl_shapes')->where('id', $request->id)->update(['order' => $request->val]);
        return "Success";
    }

    // Artist Approve Starts here

    public function artistapprove(Request $request)
    {
        $states = DB::table('tbl_artists')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function artistreject(Request $request)
    {
        $states = DB::table('tbl_artists')->where('id', $request->db_id)->update(['status' => '0']);
        $prds = DB::table('tbl_cart_products')->where('category', $request->db_id)->get();
        foreach ($prds as $key) {
            $dta = DB::table('tbl_cart_products')->where('id', $key->id)->update(['status' => '0']);
            $varts = DB::table('tbl_product_variants')->where('product', $key->id)->update(['status' => '0']);
        }
        
        return "Success";
    }

    public function artistorder(Request $request)
    {
        $states = DB::table('tbl_artists')->where('id', $request->id)->update(['order' => $request->val]);
        return "Success";
    }

    // Art Color Approve Starts here

    public function sizeapprove(Request $request)
    {
        $states = DB::table('tbl_sizes')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }
    public function supplyapprove(Request $request)
    {
        $states = DB::table('users')->where('id', $request->db_id)->update(['type' => '3']);
        return "Successfully changed to reseller";
    }
    public function supplyreject(Request $request)
    {
        $states = DB::table('users')->where('id', $request->db_id)->update(['type' => '2']);
        return "Successfully changed to Customer";
    }
    public function taxreject(Request $request)
    {
        $states = DB::table('tbl_taxes')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }
    public function taxapprove(Request $request)
    {
        $states = DB::table('tbl_taxes')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }
    public function sizereject(Request $request)
    {
        $states = DB::table('tbl_sizes')->where('id', $request->db_id)->update(['status' => '0']);
        return "Success";
    }
    public function artcolorreject(Request $request)
    {
        $states = DB::table('tbl_artcolors')->where('id', $request->db_id)->update(['status' => '0']);
        $prds = DB::table('tbl_cart_products')->where('category', $request->db_id)->get();
        foreach ($prds as $key) {
            $dta = DB::table('tbl_cart_products')->where('id', $key->id)->update(['status' => '0']);
            $varts = DB::table('tbl_product_variants')->where('product', $key->id)->update(['status' => '0']);
        }
        
        return "Success";
    }

    public function artcolororder(Request $request)
    {
        $states = DB::table('tbl_artcolors')->where('id', $request->id)->update(['order' => $request->val]);
        return "Success";
    }

    // Hanging Color Approve Starts here

    public function hangingcolorapprove(Request $request)
    {
        $states = DB::table('tbl_hangingcolors')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function hangingcolorreject(Request $request)
    {
        $states = DB::table('tbl_hangingcolors')->where('id', $request->db_id)->update(['status' => '0']);
        $prds = DB::table('tbl_cart_products')->where('category', $request->db_id)->get();
        foreach ($prds as $key) {
            $dta = DB::table('tbl_cart_products')->where('id', $key->id)->update(['status' => '0']);
            $varts = DB::table('tbl_product_variants')->where('product', $key->id)->update(['status' => '0']);
        }
        
        return "Success";
    }

    public function colororder(Request $request)
    {
        $states = DB::table('tbl_colors')->where('id', $request->id)->update(['order' => $request->val]);
        return "Success";
    }
    public function colorapprove(Request $request)
    {
        $states = DB::table('tbl_colors')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }
    public function colorreject(Request $request)
    {
        $states = DB::table('tbl_colors')->where('id', $request->db_id)->update(['status' => '0']);
        $prds = DB::table('tbl_cart_products')->where('category', $request->db_id)->get();
        foreach ($prds as $key) {
            $dta = DB::table('tbl_cart_products')->where('id', $key->id)->update(['status' => '0']);
            $varts = DB::table('tbl_product_variants')->where('product', $key->id)->update(['status' => '0']);
        }
        
        return "Success";
    }
    public function hangingcolororder(Request $request)
    {
        $states = DB::table('tbl_hangingcolors')->where('id', $request->id)->update(['order' => $request->val]);
        return "Success";
    }
    // Hanging Category Approve Starts here

    public function hangingcategoryapprove(Request $request)
    {
        $states = DB::table('tbl_hanging_categories')->where('id', $request->db_id)->update(['status' => '1']);
        return "Success";
    }

    public function hangingcategoryreject(Request $request)
    {
        $states = DB::table('tbl_hanging_categories')->where('id', $request->db_id)->update(['status' => '0']);
        $prds = DB::table('tbl_cart_products')->where('category', $request->db_id)->get();
        foreach ($prds as $key) {
            $dta = DB::table('tbl_cart_products')->where('id', $key->id)->update(['status' => '0']);
            $varts = DB::table('tbl_product_variants')->where('product', $key->id)->update(['status' => '0']);
        }
        
        return "Success";
    }

    public function hangingcategoryorder(Request $request)
    {
        $states = DB::table('tbl_hanging_categories')->where('id', $request->id)->update(['order' => $request->val]);
        return "Success";
    }
    public function wishok(Request $request)
    { 
        $product_id= $request->product_id;
        $user_id = $request->user_id;
        $check = DB::table('tbl_wishlists')->where('user_id',$user_id)
        ->where('product_id', $product_id)->get();
        if(count($check) > 0)
        {
           
            DB::table('tbl_wishlists')->where('user_id',$user_id)
            ->where('product_id', $product_id)->delete();
            return "nok";
        }
        else{ 
                tblWishlist::create($request->all());
                return "ok";
            }
             
        
                // DB::table('tbl_profile_completions')->where(['user' => $checks->id])->delete();
    }    
    public function wishnok(Request $request)
    { 
        $product_id= $request->product_id;
        $user_id = $request->user_id;
        $check = DB::table('tbl_wishlists')->where('user_id',$user_id)
        ->where('product_id', $product_id)->get();
        if(count($check) > 0)
        {
           
            DB::table('tbl_wishlists')->where('user_id',$user_id)
            ->where('product_id', $product_id)->delete();
            return "nok";
        }
        else{ 
                tblWishlist::create($request->all());
                return "ok";
            }
             
        
                // DB::table('tbl_profile_completions')->where(['user' => $checks->id])->delete();
    }    
    public function addstock(Request $request)
    {
           
        $wl_id = Auth::user()->id;
        $wl_name = Auth::user()->name;
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");
        $stockid = $request->stockid;
          $quantity= $request->quantity;
          $wl_mesage = $wl_name ." was add quantity- ".$quantity." to  stock_id ".$stockid." into the system at ".$dt." ".$time; 

        $data = DB::table('tbl_stocks')->where(['id' => $stockid])->get();
        foreach ($data as $obj) {
            $prequantity = $obj->quantity;
            $pdctid = $obj->product_id;
              $curquantity = $quantity + $prequantity;
            
             $varts = DB::table('tbl_stocks')->where('id', $stockid)->update(['quantity' => $curquantity]);
             $varts1 = DB::table('tbl_stocks')->where('product_id', $pdctid)->update(['stockstatus' => 1]);

             $tblStocklog = new tblStocklog;
             $tblStocklog ->stock_id=$request->stockid;
             $tblStocklog ->quantity=$request->quantity;
             $tblStocklog ->message=$wl_mesage;
             $tblStocklog ->user_id=$wl_id;
             $tblStocklog->save();
              return "Updated Successfully!!";
            }

    }
    public function deductstock(Request $request)
    {

        $wl_id = Auth::user()->id;
        $wl_name = Auth::user()->name;
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");
        $stockid = $request->stockid;
          $quantity= $request->quantity;
          $wl_mesage = $wl_name ." was remove quantity- ".$quantity." to  stock_id ".$stockid." into the system at ".$dt." ".$time; 

          $data = DB::table('tbl_stocks')->where(['id' => $stockid])->get();
        foreach ($data as $obj) {
            $prequantity = $obj->quantity;
              $curquantity =  $prequantity-$quantity;
            
             $varts = DB::table('tbl_stocks')->where('id', $stockid)->update(['quantity' => $curquantity]);
             if($curquantity==0)
             {
                $varts1 = DB::table('tbl_stocks')->where('id', $stockid)->update(['stockstatus' => 2]);
             }
             $tblStocklog = new tblStocklog;
             $tblStocklog ->stock_id=$request->stockid;
             $tblStocklog ->quantity=$request->quantity;
             $tblStocklog ->message=$wl_mesage;
             $tblStocklog ->user_id=$wl_id;
             $tblStocklog->save();
             return "Updated Successfully!!";
            }

    }

    public function addbarcode(Request $request)
    {
        $states = DB::table('tbl_ordermasters')->where('id', $request->orderno)->update(['barcode' => $request->barcode,'orderstatus' => "Shipped"]);
        $states1 = DB::table('orders')->where('order_id', $request->orderno)->update(['barcode' => $request->barcode]);

        return "Success";
    }
    public function loginok(Request $request)
    { 
        $contact = $request->contact;
        $check = DB::table('users')->where('contact',$contact)
       ->get();
        // DB::table('tbl_profile_completions')->insert(['regtime' => $regtime, 'user' => $pstid]);
        if(count($check) > 0)
        {
          $otp = random_int(100000, 999999);
 
         DB::table('users')->where('contact', $contact)->update(['otp' => $otp]);
        // $text = urlencode('The OTP to login into PlotstoHomes is '.$otp.'');
          
            $phone = $contact; 
             
         
          
            $text = urlencode('Use '.$otp.' as the OTP for LASH Boutique login. Please do not share OTP with anyone.');
                   
            $opts = array(
              'http'=>array(
                'method'=>"GET",
                //'content' => "$parameters",
                'header'=>"Accept-language: en\r\n" .
                          "Cookie: foo=bar\r\n"
              )
            );
     
            $context = stream_context_create($opts);
     
            $fp = fopen("http://thesmsbuddy.com/api/v1/sms/send?key=5X8UY14g88jhtYtuk9NyB1ITWhs1Xpxn&type=1&to=".$phone."&sender=LASHBQ&message=".$text."&flash=0&template_id=1707168595099450765", "r", false, $context);
            $response = stream_get_contents($fp);
            fpassthru($fp);
            fclose($fp);
  
     // Print response
     //print_r($response);
         return "success";
        }
         else
         {
            User::create($request->all());

        $pstid = DB::getPdo()->lastInsertId();
        DB::table('users')->where('id', $pstid)->update(['type' => '2']);
        $otp = random_int(100000, 999999);
 
        DB::table('users')->where('contact', $contact)->update(['otp' => $otp]);
        $phone = $contact; 
             
         
          
        $text = urlencode('Use '.$otp.' as the OTP for LASH Boutique login. Please do not share OTP with anyone.');
               
        $opts = array(
          'http'=>array(
            'method'=>"GET",
            //'content' => "$parameters",
            'header'=>"Accept-language: en\r\n" .
                      "Cookie: foo=bar\r\n"
          )
        );
 
        $context = stream_context_create($opts);
 
        $fp = fopen("http://thesmsbuddy.com/api/v1/sms/send?key=5X8UY14g88jhtYtuk9NyB1ITWhs1Xpxn&type=1&to=".$phone."&sender=LASHBQ&message=".$text."&flash=0&template_id=1707168595099450765", "r", false, $context);
        $response = stream_get_contents($fp);
        fpassthru($fp);
        fclose($fp);

 // Print response
 //print_r($response);
     return "success";
         }
             
        
       
        } 
}   
