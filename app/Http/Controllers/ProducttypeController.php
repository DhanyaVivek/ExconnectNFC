<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redirect;
use DB;
use View;
use Session;
use Auth;
use File;
use App\tblProducttype;
class ProducttypeController extends Controller
{
    //
    public function producttype()
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data['data'] = DB::table('tbl_producttypes')->orderBy('id', 'asc')->get();
                if(count($data) > 0)
                {
                    return view('admin.products.producttype', $data);
                }
                else
                {
                    return view('admin.products.producttype');
                }
        }
    }
    public function newproducttype()
    {
       
        if (!Auth::check()) { return redirect('/'); } else {
            return view('admin.products.newproducttype');
        } 
       
    }
    
    public function postproducttype(Request $req)
    {
        $this->producttypevalidation($req);
        $image = $req->file('image'); // Assignment
        tblProducttype::create($req->all());
         $pstid = DB::getPdo()->lastInsertId();
         $product_type = $req->input('product_type');
        if($image != "")
        {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check !== false) 
            {
                if($_FILES['image']['size'] < 1048576) 
                {
                    $filename_sm = $_FILES['image']['name'];
                    $ext_sm = \File::extension($filename_sm);

                    $time_sm = $pstid.'.'.$ext_sm;

                    $Blogimge="producttype/".$time_sm;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $add="producttype/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
                    {
                        $images[] = $Blogimge;
                        DB::table('tbl_producttypes')->where('id', $pstid)->update(['image' => $Blogimge]);
                    }
                }
            }
        }
            $deta = $product_type;

            $slug = str_slug(strtolower($deta), '-'); 
            $allSlugs = $this->getproductRelatedSlugs($slug); 
        
            if($allSlugs == 0)
            {
                // Update query 
        
                DB::table('tbl_producttypes')->where('id', $pstid)->update(['slug' => $slug]);
            }
            else
            {
                for ($s = 1; $s <= 400; $s++) {
                    $newSlug = $slug.'-'.$s;
                    $allnewSlugs = $this->getproductRelatedSlugs($newSlug); 
        
                    if ($allnewSlugs == 0) 
                    {
                        // Update query 
                        DB::table('tbl_producttypes')->where('id', $pstid)->update(['slug' => $newSlug]);
                        $s = 400;
                    }
                }

        }
        return redirect()->back()->withErrors(['Added Successfully!!']);
        
    }
    public function producttypevalidation($request)
    {
        return $this->Validate($request, [
            'product_type' => 'required',
            // check type
            'image' => 'mimes:jpeg,jpg,png',
            // check size
            

            'image' => 'max:2048',
            'image' => 'dimensions:min_width=700,min_height=700,max_width=700,max_height=700',
        ]);
    }
    public function viewproducttypevalidation($request)
    {
       
       
        $id = $request->input('id'); 

       
           return $this->Validate($request, [
            'product_type' => 'required',
            // check type
            'image' => 'mimes:jpeg,jpg,png',
           
          //check size
            

            'image' => 'max:2048',
            'image' => 'dimensions:min_width=700,min_height=700,max_width=700,max_height=700',
        ]);
        
        
    }
    public function viewproducttype($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data['data'] = DB::table('tbl_producttypes')->where('id', '=', $id)->get();

                if(count($data) > 0)
                {
                    return view('admin.products.viewproducttype', $data);
                }
                else
                {
                    return view('admin.products.viewproducttype');
                }
        }
    }
    public function updateproducttype(Request $req)
    {
        $this->producttypevalidation($req);
        $id = $req->input('id'); 
        $image = $req->file('image'); // Assignment
        
        $userUpdate  = tblProducttype::where('id',$id)->first();
        if ($userUpdate) {
           $speak = $userUpdate->update($req->all());
        }

        if($image != "")
        {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check !== false) 
            {
                if($_FILES['image']['size'] < 1048576) 
                {
                    $filename_sm = $_FILES['image']['name'];
                    $ext_sm = \File::extension($filename_sm);

                    $time_sm = $id.'.'.$ext_sm;

                    $Blogimge="producttype/".$time_sm;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $add="producttype/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
                    {
                        $images[] = $Blogimge;
                        DB::table('tbl_producttypes')->where('id', $id)->update(['image' => $Blogimge]);
                    }
                }
            }

        }
        $product_type = $req->input('product_type');
        $deta = $product_type;
    
        $slug = str_slug(strtolower($deta), '-'); 
        $allSlugs = $this->getproductRelatedSlugs($slug); 
    
        if($allSlugs == 0)
        {
            // Update query 
    
            DB::table('tbl_producttypes')->where('id', $id)->update(['slug' => $slug]);
        }
        else
        {
            for ($s = 1; $s <= 400; $s++) {
                $newSlug = $slug.'-'.$s;
                $allnewSlugs = $this->getproductRelatedSlugs($newSlug); 
    
                if ($allnewSlugs == 0) 
                {
                    // Update query 
                    DB::table('tbl_producttypes')->where('id', $id)->update(['slug' => $newSlug]);
                    $s = 400;
                }
            }
        }
        //return redirect('/video-management')->withErrors(['Video Added Successfully!!']);
        return redirect()->back()->withErrors(['Updated Successfully!!']);
    }
    protected function getproductRelatedSlugs($slug)
    {
        $slugcount = DB::table('tbl_producttypes')->where(['slug' => $slug])->count();
        return $slugcount;
    }
    public function removeproducttypeimage($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_producttypes')->where('id', '=', $id)->get();
                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }
                
                DB::table('tbl_producttypes')->where('id', $id)->update(['image' => '']);

                return redirect()->back()->withErrors(['Image Removed Successfully!!']);
        }

    }
    public function producttyperemove($id)
{
    if (!Auth::check()) { return redirect('/'); } else {
            $data = DB::table('tbl_producttypes')->where('id', '=', $id)->get();
            DB::table('tbl_producttypes')->where('id', $id)->delete();
            return redirect()->back()->withErrors(['Details Removed Successfully!!']);
    }
}

}
