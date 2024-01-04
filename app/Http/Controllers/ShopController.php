<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route; 
use DB;
use View;
use Session;
use Auth;
use File;
use App\Models\tblShop;
use Illuminate\Support\Str;


class ShopController extends Controller
{
    public function shops()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            $data['data'] = DB::table('tbl_shops')->orderBy('id', 'desc')->paginate(10);
            $data['category'] = DB::table('tbl_categories')->where(['subscription' => '0'])->orderBy('id', 'desc')->get();
            $data['subcategory'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();
            //$data['data'] = DB::table('tbl_cart_products')->where(['subscription' => '0'])->orderBy('id', 'desc')->paginate(20);
                 
            if(count($data) > 0)
            {
                return view('admin.shops.cartproducts', $data);
            }
            else
            {
                return view('admin.products.cartproducts');
            }
        }
    }
    public function newshop()
    {
        if (!Auth::check()) { return redirect('/'); } else {
    		$data['cat'] = DB::table('tbl_categories')->orderBy('id', 'asc')->get();
            $data['category'] = DB::table('tbl_categories')->orderBy('id', 'desc')->get();
            $data['subcategory'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();
         

            if(count($data) > 0)
            {
                return view('admin.shops.newshop', $data);
            }
            else
            {
                return view('admin.shops.newshop');
            }
        }
    }
    public function postshop(Request $req)
    {
      //  $this->cartproductvalidation($req);
        
      
        $shopname = $req->input('shopname');
        $logo = $req->file('logo'); 
             
       // return $req->all();
        tblShop::create($req->all());

        $pstid = DB::getPdo()->lastInsertId();
 
           
        $deta = $shopname;

        $slug = str_slug(strtolower($deta), '-'); 
        $allSlugs = $this->getproductRelatedSlugs($slug); 

        if($allSlugs == 0)
        {
            // Update query 

            DB::table('tbl_shops')->where('id', $pstid)->update(['slug' => $slug]);
        }
        else
        {
            for ($s = 1; $s <= 400; $s++) {
                $newSlug = $slug.'-'.$s;
                $allnewSlugs = $this->getproductRelatedSlugs($newSlug); 

                if ($allnewSlugs == 0) 
                {
                    // Update query 
                    DB::table('tbl_shops')->where('id', $pstid)->update(['slug' => $newSlug]);
                    $s = 400;
                }
            }
        }
        
         
        // Web Product Image Starts here
        if($logo!="")
        {
        if($_FILES["logo"]["tmp_name"] != "")
        {
            $wpcheck = getimagesize($_FILES["logo"]["tmp_name"]);
            if($wpcheck !== false) 
            {
                if($_FILES['logo']['size'] < 1048576) 
                {
                    $w1filename_sm = $_FILES['logo']['name'];
                    $w1ext_sm = \File::extension($w1filename_sm);

                    $w1time_sm = $pstid.'.'.$w1ext_sm;

                    $w1Blogimge="cart/product/web/product/".$w1time_sm;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['logo']['tmp_name'],$w1add)) 
                    {
                        $images[] = $w1Blogimge;
                        DB::table('tbl_shops')->where('id', $pstid)->update(['logo' => $w1Blogimge]);
                    }
                }
            }
        }
    }
    return redirect()->back()->withErrors(['Shop Details Added Successfully!!']);    
}
protected function getproductRelatedSlugs($slug)
{
    $slugcount = DB::table('tbl_shops')->where(['slug' => $slug])->count();
    return $slugcount;
}
}
