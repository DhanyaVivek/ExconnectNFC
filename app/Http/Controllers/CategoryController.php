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
use App\Models\tblCategory;
use App\Models\tblSubcategory;
use App\tblTopcategory;
use App\tblSecsubcategory;

class CategoryController extends Controller
{
    // Category Starts here

    public function category()
    {
        if(!Auth::check()) { return redirect('/'); } else {

             
                $data['data'] = DB::table('tbl_categories')->orderBy('id', 'asc')->get();
              

                if(count($data) > 0)
                {
                    return view('admin.category.categorysettings', $data);
                }
                else
                {
                    return view('admin.category.categorysettings');
                }
        }
    }
    public function newcategory()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            	return view('admin.category.newcategory');
            } 
    }
    
    public function postcategory(Request $req)
    {
        $this->categoryvalidation($req);
        $image = $req->file('image'); // Assignment

        tblCategory::create($req->all());
        $pstid = DB::getPdo()->lastInsertId();

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

                    $Blogimge="category/".$time_sm;
	                //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

	                $add="category/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

	                if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
	                {
		                $images[] = $Blogimge;
	                    DB::table('tbl_categories')->where('id', $pstid)->update(['image' => $Blogimge]);
	                }
                }
            }
			

		}
        return redirect()->back()->withErrors(['Category Added Successfully!!']);
        
    }
    public function categoryvalidation($request)
    {
    	return $this->Validate($request, [
            'category' => 'required',
            // check type
			'image' => 'mimes:jpeg,jpg,png',
			// check size
			'image' => 'max:2048',
			'image' => 'dimensions:min_width=280,min_height=120,max_width=280,max_height=120',
        ]);
    }
    public function viewcategory($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data['data'] = DB::table('tbl_categories')->where('id', '=', $id)->get();

                if(count($data) > 0)
                {
                    return view('admin.category.viewcategory', $data);
                }
                else
                {
                    return view('admin.category.viewcategory');
                }
        }
    }
    public function updatecategory(Request $req)
    {
        $this->categoryvalidation($req);

        $id = $req->input('id'); 
         $primage = $req->input('primage');
         $imgid = time();

        // $image = $req->file('image'); // Assignment
        
        $userUpdate  = tblCategory::where('id',$id)->first();
        if ($userUpdate) {
           $speak = $userUpdate->update($req->all());
        }

        if($_FILES["image"]["tmp_name"] != "")
        {
        	$check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check !== false) 
            {
                if($_FILES['image']['size'] < 1048576) 
                {
                	$filename_sm = $_FILES['image']['name'];
                	$ext_sm = \File::extension($filename_sm);

                	$time_sm = $imgid.'.'.$ext_sm;

                    $Blogimge="category/".$time_sm;
	                //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

	                $add="category/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

	                if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
	                {
		                $images[] = $Blogimge;
	                    DB::table('tbl_categories')->where('id', $id)->update(['image' => $Blogimge]);
                        if(File::exists($primage)) 
                        {
                            File::delete($primage);
                        }
	                }
                }
            }

		}
        return redirect()->back()->withErrors(['Updated Successfully!!']);
    }
    public function deletecategory($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_categories')->where('id', '=', $id)->get();
                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }
                
                DB::table('tbl_categories')->where('id', $id)->delete();

                return redirect()->back()->withErrors(['Category Removed Successfully!!']);
        }

    }
    public function removecategoryimage($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_categories')->where('id', '=', $id)->get();
                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }
                
                DB::table('tbl_categories')->where('id', $id)->update(['image' => '']);

                return redirect()->back()->withErrors(['Image Removed Successfully!!']);
        }

    }

    //Top Category Starts here

    public function topcategory()
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data['data'] = DB::table('tbl_topcategories')->orderBy('id', 'asc')->get();
                $data['city'] = DB::table('locations')->orderBy('id', 'asc')->get();
                if(count($data) > 0)
                {
                    return view('admin.category.topcategory', $data);
                }
                else
                {
                    return view('admin.category.topcategory');
                }
        }
    }
    public function newtopcategory()
    {
       
        if (!Auth::check()) { return redirect('/'); } else {
            $data['cat'] = DB::table('tbl_categories')->where('status', '=', '1')->orderBy('id', 'asc')->get();
            if(count($data) > 0)
            {
                return view('admin.category.newtopcategory', $data);
            }
            else
            {
                return view('admin.category.newtopcategory');
            }
    }
       
    }
    
    public function posttopcategory(Request $req)
    {
        $this->topcategoryvalidation($req);
        $image = $req->file('image'); // Assignment
        tblTopcategory::create($req->all());
        $pstid = DB::getPdo()->lastInsertId();

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

                    $Blogimge="category/topcategory/".$time_sm;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $add="category/topcategory/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
                    {
                        $images[] = $Blogimge;
                        DB::table('tbl_topcategories')->where('id', $pstid)->update(['image' => $Blogimge]);
                    }
                }
            }
            

        }
        return redirect()->back()->withErrors(['Top Category Added Successfully!!']);
        
    }
    public function topcategoryvalidation($request)
    {
        return $this->Validate($request, [
            'topcategory' => 'required',
            // check type
            'image' => 'mimes:jpeg,jpg,png',
            // check size
            'image' => 'max:2048',
            'image' => 'dimensions:min_width=140,min_height=140,max_width=160,max_height=160',
        ]);
    }
    public function viewtopcategory($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data['data'] = DB::table('tbl_topcategories')->where('id', '=', $id)->get();

                if(count($data) > 0)
                {
                    return view('admin.category.viewtopcategory', $data);
                }
                else
                {
                    return view('admin.category.viewtopcategory');
                }
        }
    }
    public function updatetopcategory(Request $req)
    {
        $this->topcategoryvalidation($req);
        $id = $req->input('id'); 
        $image = $req->file('image'); // Assignment
        
        $userUpdate  = tblTopcategory::where('id',$id)->first();
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

                    $Blogimge="category/topcategory/".$time_sm;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $add="category/topcategory/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
                    {
                        $images[] = $Blogimge;
                        DB::table('tbl_topcategories')->where('id', $id)->update(['image' => $Blogimge]);
                    }
                }
            }

        }
        //return redirect('/video-management')->withErrors(['Video Added Successfully!!']);
        return redirect()->back()->withErrors(['Updated Successfully!!']);
    }
    public function topcategoryremove($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_topcategories')->where('id', '=', $id)->get();
                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }
                
                DB::table('tbl_topcategories')->where('id', $id)->delete();

                return redirect()->back()->withErrors(['Image Removed Successfully!!']);
        }

    }
    public function removetopcategoryimage($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_topcategories')->where('id', '=', $id)->get();
                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }
                
                DB::table('tbl_topcategories')->where('id', $id)->update(['image' => '']);

                return redirect()->back()->withErrors(['Image Removed Successfully!!']);
        }

    }

    // Subcategory Starts here

    public function subcategory()
    {
        if (!Auth::check()) { return redirect('/'); } else {

                $data['data'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();

                if(count($data) > 0)
                {
                    return view('admin.category.subcategorysettings', $data);
                }
                else
                {
                    return view('admin.category.subcategorysettings');
                }
        }
    }
    public function subcategoryss($x)
    {
        if (!Auth::check()) { return redirect('/'); } else {

                $data['data'] = DB::table('tbl_subcategories')->where('topcategory', '=', $x)->orderBy('id', 'asc')->get();

                if(count($data) > 0)
                {
                    return view('admin.category.subcategorysettings', $data);
                }
                else
                {
                    return view('admin.category.subcategorysettings');
                }
        }
    }
    public function newsubcategory()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            	$data['data'] = DB::table('tbl_categories')->where('status', '=', '1')->orderBy('id', 'asc')->get();

                if(count($data) > 0)
                {
                    return view('admin.category.newsubcategory', $data);
                }
                else
                {
                    return view('admin.category.newsubcategory');
                }
        }
    }
    
    public function postsubcategory(Request $req)
    {
        $this->subcategoryvalidation($req);
        $image = $req->file('image'); // Assignment

        tblSubcategory::create($req->all());
        $pstid = DB::getPdo()->lastInsertId();

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

                    $Blogimge="subcategory/".$time_sm;
	                //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

	                $add="subcategory/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

	                if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
	                {
		                $images[] = $Blogimge;
	                    DB::table('tbl_subcategories')->where('id', $pstid)->update(['image' => $Blogimge]);
	                }
                }
            }
			

		}
        return redirect()->back()->withErrors(['Sub Category Added Successfully!!']);
        
    }
    public function subcategoryvalidation($request)
    {
    	return $this->Validate($request, [
            'category' => 'required',
            'subcategory' => 'required',
            // check type
			'image' => 'mimes:jpeg,jpg,png',
			// check size
			'image' => 'max:2048',
        ]);
    }
    public function vsubcategoryvalidation($request)
    {
    	return $this->Validate($request, [
            'subcategory' => 'required',
            // check type
			'image' => 'mimes:jpeg,jpg,png',
			// check size
			'image' => 'max:2048',
        ]);
    }
    public function viewsubcategory($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data['data'] = DB::table('tbl_subcategories')->where('id', '=', $id)->get();
                $data['cat'] = DB::table('tbl_categories')->where('status', '=', '1')->orderBy('id', 'asc')->get();

                if(count($data) > 0)
                {
                    return view('admin.category.viewsubcategory', $data);
                }
                else
                {
                    return view('admin.category.viewsubcategory');
                }
        }
    }
    public function updatesubcategory(Request $req)
    {
        $this->vsubcategoryvalidation($req);
        $id = $req->input('id'); 
        $image = $req->file('image'); // Assignment
        
        $userUpdate  = tblSubcategory::where('id',$id)->first();
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

                    $Blogimge="subcategory/".$time_sm;
	                //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

	                $add="subcategory/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

	                if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
	                {
		                $images[] = $Blogimge;
	                    DB::table('tbl_subcategories')->where('id', $id)->update(['image' => $Blogimge]);
	                }
                }
            }

		}
        //return redirect('/video-management')->withErrors(['Video Added Successfully!!']);
        return redirect()->back()->withErrors(['Updated Successfully!!']);
    }
   public function deletesubcategory($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_subcategories')->where('id', '=', $id)->get();
                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }
                
                DB::table('tbl_subcategories')->where('id', $id)->delete();

                return redirect()->back()->withErrors(['Image Removed Successfully!!']);
        }

    }
    public function removesubcategoryimage($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_subcategories')->where('id', '=', $id)->get();
                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }
                
                DB::table('tbl_subcategories')->where('id', $id)->update(['image' => '']);

                return redirect()->back()->withErrors(['Image Removed Successfully!!']);
        }

    }

    // Second Subcategory Starts here

    public function secsubcategorys()
    {
        if (!Auth::check()) { return redirect('/'); } else {

                $data['data'] = DB::table('tbl_secsubcategories')->orderBy('id', 'asc')->get();

                if(count($data) > 0)
                {
                    return view('admin.category.secsubcategorysettings', $data);
                }
                else
                {
                    return view('admin.category.subcategorysettings');
                }
        }
    }

    public function postsecsubcategory(Request $req)
    {
        $this->secsubcategoryvalidation($req);
        $image = $req->file('image'); // Assignment

        tblSecsubcategory::create($req->all());
        $pstid = DB::getPdo()->lastInsertId();

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

                    $Blogimge="subcategory/sec/".$time_sm;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $add="subcategory/sec/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
                    {
                        $images[] = $Blogimge;
                        DB::table('tbl_secsubcategories')->where('id', $pstid)->update(['image' => $Blogimge]);
                    }
                }
            }
            

        }
        return redirect()->back()->withErrors(['Second Sub Category Added Successfully!!']);
        
    }
    public function secsubcategoryvalidation($request)
    {
        return $this->Validate($request, [
            
            
            
            // check type
            'image' => 'mimes:jpeg,jpg,png',
            // check size
            'image' => 'max:2048',
            'image' => 'dimensions:min_width=140,min_height=140,max_width=160,max_height=160',
        ]);
    }
    
    public function viewsecsubcategory($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data['data'] = DB::table('tbl_secsubcategories')->where('id', '=', $id)->get();
                $data['cat'] = DB::table('tbl_categories')->where('status', '=', '1')->orderBy('id', 'asc')->get();
                
                if(count($data) > 0)
                {
                    return view('admin.category.viewsecsubcategory', $data);
                }
                else
                {
                    return view('admin.category.viewsecsubcategory');
                }
        }
    }
    public function updatesecsubcategory(Request $req)
    {
        $this->secsubcategoryvalidation($req);
        $id = $req->input('id'); 
        $image = $req->file('image'); // Assignment
        
        $userUpdate  = tblSecsubcategory::where('id',$id)->first();
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

                    $Blogimge="subcategory/sec/".$time_sm;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $add="subcategory/sec/".$time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['image']['tmp_name'],$add)) 
                    {
                        $images[] = $Blogimge;
                        DB::table('tbl_secsubcategories')->where('id', $id)->update(['image' => $Blogimge]);
                    }
                }
            }

        }
        //return redirect('/video-management')->withErrors(['Video Added Successfully!!']);
        return redirect()->back()->withErrors(['Updated Successfully!!']);
    }
    public function secsubcategoryremove($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_secsubcategories')->where('id', '=', $id)->get();
                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }
                
                DB::table('tbl_secsubcategories')->where('id', $id)->delete();

                return redirect()->back()->withErrors(['Image Removed Successfully!!']);
        }

    }
    public function removesecsubcategoryimage($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_secsubcategories')->where('id', '=', $id)->get();
                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }
                
                DB::table('tbl_secsubcategories')->where('id', $id)->update(['image' => '']);

                return redirect()->back()->withErrors(['Image Removed Successfully!!']);
        }
    }
}
