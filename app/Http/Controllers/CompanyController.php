<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use DB;
use View;
use Session;
use Auth;
use File;
use App\Models\User;
use App\Mail\Company;
use App\Models\tblCompany;

class CompanyController extends Controller
{
    public function company()
    {
        if (!Auth::check()) { return redirect('/'); } else {
           

                $data['data'] = DB::table('users')->where(['type' => '3'])->orderBy('id', 'desc')->get();

                if(count($data) > 0)
                {
                    return view('admin.company.company', $data);
                }
                else
                {
                    return view('admin.company.company');
                }
            
        }
    }
    public function newcompany()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            

               
                   return view('admin.company.newcompany');
                 
            
        }
    }
    public function postcompany(Request $req)
    {
       
       
        $email = $req->input('email');
        $password = $req->input('password');

        $req['password'] = bcrypt($password);
        $req['password1'] = $password; 
        
        User::create($req->all());
         $pstid = DB::getPdo()->lastInsertId();

        DB::table('users')->where('id', $pstid)->update(['status' => '1' ,'type' => '3','pass' => $password]);
        
        $data = array(
            'email'=>$email,
            'password'=>$password,
            );
        Mail::to($email)->bcc(['vipin@extrememedia.in'])->send(new Company($data));
        

        return redirect()->back()->withErrors(['Company Added Successfully!!']);
    }
    public function companyvalidation($request)
    {
    	return $this->Validate($request, [
            'name' => 'required',
            'contact' => 'required',
            'email' => 'required|unique:users|max:255',
            'password' => 'required',
           
        ]);
    }
    
    public function viewcompany($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
           
                $data['data'] = DB::table('users')->where(['id' => $id])->get();

                if(count($data) > 0)
                {
                    return view('admin.company.viewcompany', $data);
                }
                else
                {
                    return view('admin.company.viewcompany');
                }
            
        }
    }
    public function updatecompany(Request $req)
    {
        
        $id = $req->input('id');
        
        
        $password = $req->input('password');

        $req['password'] = bcrypt($password);
        $req['password1'] = $password; 

        $userUpdate  = User::where('id',$id)->first();
        if ($userUpdate) {
           $speak = $userUpdate->update($req->all());
        }
        DB::table('users')->where('id', $id)->update(['status' => '1' ,'type' => '3','pass' => $password]);

      
        return redirect()->back()->withErrors(['Updation completed Successfully!!']);
    } 
     

    public function companyremove($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
            
              
 
                
                DB::table('users')->where(['id' => $id])->delete();

                return redirect('/company')->withErrors(['Deleted Successfully!!']);
             
        }
    }
    
    public function companyprofile($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
            
           
                $data['data'] = DB::table('users')->where(['regid' => $id])->get();

                if(count($data) > 0)
                {
                    return view('delivery_agent.profile', $data);
                }
                else
                {
                    return view('delivery_agent.profile');
                }
            
        }
    }
    public function viewcompanybasicdetails($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
            
           
                $data['data'] = DB::table('users')->where(['regid' => $id])->get();

                if(count($data) > 0)
                {
                    return view('delivery_agent.company_basic_details', $data);
                }
                else
                {
                    return view('delivery_agent.company_basic_details');
                }
            
        }
    }
    // public function viewcompanydetails($id)
    // {
    //     if (!Auth::check()) { return redirect('/'); } else {
            
           
    //             $data['data'] = DB::table('users')->where(['regid' => $id])->get();

    //             if(count($data) > 0)
    //             {
    //                 return view('delivery_agent.profile', $data);
    //             }
    //             else
    //             {
    //                 return view('delivery_agent.profile');
    //             }
            
    //     }
    // }
    public function viewcompanydetails($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
            
           
                $data['data'] = DB::table('tbl_companies')->where(['regid' => $id])->get();

                if(count($data) > 0)
                {
                    return view('delivery_agent.company_view_details', $data);
                }
                else
                {
                    return view('delivery_agent.company_view_details');
                }
            
        }
    }
    
    public function updatecompanycontact(Request $req)
    {

        $id = $req->input('id');

        // Previous Images
        $prelogo = $req->input('prelogo');

        $logo = $req->file('logo');

        // Gallery Updation starts here
        $gallery1 = $req->file('gallery1');
        $gallery2 = $req->file('gallery2');
        $gallery3 = $req->file('gallery3');
        $gallery4 = $req->file('gallery4');

        $pregallery1 = $req->input('pregallery1');
        $pregallery2 = $req->input('pregallery2');
        $pregallery3 = $req->input('pregallery3');
        $pregallery4 = $req->input('pregallery4');
       
        $userUpdate  = tblCompany::where('regid',$id)->first();
        if ($userUpdate) {
           $speak = $userUpdate->update($req->all());
        }

       
        $imgid = time(); 

        $pstid = $id;
       
    
        
          
            //$data->image_location = $fileName;
       
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

                    $w1time_sm = $imgid.'.'.$w1ext_sm;

                    $w1Blogimge="cart/product/web/product/".$w1time_sm;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['logo']['tmp_name'],$w1add)) 
                    {
                        $images[] = $w1Blogimge;
                        DB::table('tbl_companies')->where('regid', $pstid)->update(['logo' => $w1Blogimge]);
                        if(File::exists($prelogo)) 
                        {
                            File::delete($prelogo);
                        }
                    }
                }
            }
        }
    }
    
     // Gallery 1
    
        if($gallery1 != "")
        {
            if($_FILES["gallery1"]["tmp_name"] != "")
            {
                $wpcheck11 = getimagesize($_FILES["gallery1"]["tmp_name"]);
                if($wpcheck11 !== false) 
                {
                    if($_FILES['gallery1']['size'] < 1048576) 
                    {
                        $w1filename_sm = $_FILES['gallery1']['name'];
                        $w1ext_sm = \File::extension($w1filename_sm);
    
                        $w1time_sm = 'c1'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery1']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_companies')->where('regid', $pstid)->update(['gallery1' => $w1Blogimge]);
    
                            if(File::exists($pregallery1)) 
                            {
                                File::delete($pregallery1);
                            }
                        }
                    }
                }
            }
        }
    
        // Gallery 2
        if($gallery2!="")
        {
            if($_FILES["gallery2"]["tmp_name"] != "")
            {
                $wpcheck = getimagesize($_FILES["gallery2"]["tmp_name"]);
                if($wpcheck !== false) 
                {
                    if($_FILES['gallery2']['size'] < 1048576) 
                    {
                        $w1filename_sm = $_FILES['gallery2']['name'];
                        $w1ext_sm = \File::extension($w1filename_sm);
    
                        $w1time_sm =  'c2'.$pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery2']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_companies')->where('regid', $pstid)->update(['gallery2' => $w1Blogimge]);
    
                            if(File::exists($pregallery2)) 
                            {
                                File::delete($pregallery2);
                            }
                        }
                    }
                }
            }
        }
    
        // Gallery 3
        if($gallery3!="")
        {
            if($_FILES["gallery3"]["tmp_name"] != "")
            {
                $wpcheck = getimagesize($_FILES["gallery3"]["tmp_name"]);
                if($wpcheck !== false) 
                {
                    if($_FILES['gallery3']['size'] < 1048576) 
                    {
                        $w1filename_sm = $_FILES['gallery3']['name'];
                        $w1ext_sm = \File::extension($w1filename_sm);
    
                        $w1time_sm =  'c3'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery3']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_companies')->where('regid', $pstid)->update(['gallery3' => $w1Blogimge]);
    
                            if(File::exists($pregallery3)) 
                            {
                                File::delete($pregallery3);
                            }
                        }
                    }
                }
            }
        }
        
        // Gallery 4
        if($gallery4!="")
        {
            if($_FILES["gallery4"]["tmp_name"] != "")
            {
                $wpcheck = getimagesize($_FILES["gallery4"]["tmp_name"]);
                if($wpcheck !== false) 
                {
                    if($_FILES['gallery4']['size'] < 1048576) 
                    {
                        $w1filename_sm = $_FILES['gallery4']['name'];
                        $w1ext_sm = \File::extension($w1filename_sm);
    
                        $w1time_sm =  'c4'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery4']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_companies')->where('regid', $pstid)->update(['gallery4' => $w1Blogimge]);
    
                            if(File::exists($pregallery4)) 
                            {
                                File::delete($pregallery4);
                            }
                        }
                    }
                }
            }
        }
        return redirect()->back()->withErrors(['Updated Successfully!!']);
    }
}
