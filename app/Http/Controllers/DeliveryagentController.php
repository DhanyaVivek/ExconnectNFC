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
use App\Mail\Associateuser;

class DeliveryagentController extends Controller
{
    public function deliveryagent()
    {
     
        
        if (!Auth::check()) { return redirect('/'); } else {
           

                $data['data'] = DB::table('users')->where(['type' => '2'])->orderBy('id', 'desc')->get();

                if(count($data) > 0)
                {
                    return view('admin.deliveryagent.deliveryagent', $data);
                }
                else
                {
                    return view('admin.deliveryagent.deliveryagent');
                }
            
        }
    }
    public function newdeliveryagent()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            
            $data['loc'] = DB::table('tbl_locations')->where(['status' => '1'])->orderBy('id', 'desc')->get();

            if(count($data) > 0)
            {
                return view('admin.deliveryagent.newdeliveryagent', $data);
            }
            else
            {
                return view('admin.deliveryagent.newdeliveryagent');
            }
        }
    }
    public function postdeliveryagent(Request $req)
    {
        $this->deliveryagentvalidation($req);

        $email = $req->input('email');
        $password = $req->input('password');

        $req['password'] = bcrypt($password);
        $req['password1'] = $password; 
        
        User::create($req->all());
        $pstid = DB::getPdo()->lastInsertId();

        $tnm = time().rand(10, 99);

        DB::table('users')->where('id', $pstid)->update(['status' => '1' ,'type' => '2','pass' => $password,'direct_link' => $tnm]);
        
        // Mail start here
        
        $data = array(
            'email'=>$email,
            'password'=>$password,
            );
        //Mail::to($email)->bcc(['vipin@extrememedia.in'])->send(new Associateuser($data));

        // if($image != "")
        // {
        //     $time = time();

        //     $check = getimagesize($_FILES["image1"]["tmp_name"]);
        //     if($check !== false) 
        //     {
        //         if ($_FILES['image1']['size'] > 1048576) {
        //             return redirect()->back()->withErrors(['Staff Added Successfully!! Unable to add Iamge due to large size!!']);
        //         }
                
        //         // Move Blog Image
        //         $Blogimge="instructor/".$time.$_FILES['image1']['name'];
        //         //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

        //         $add="instructor/".$time.$_FILES['image1']['name']; // the path with the file name where the file will be stored, upload is the directory name. 

        //         if(move_uploaded_file ($_FILES['image1']['tmp_name'],$add)) 
        //         {

        //             $images[] = $Blogimge;
        //             DB::table('users')->where('id', $pstid)->update(['image' => $Blogimge]);
        //         }
        //         else
        //         {
        //             return redirect()->back()->withErrors(['Staff Details Added Successfully!! Unable to add image due to large size!!']);
        //         }

        //         ///////// Start the thumbnail generation//////////////
        //         $n_width=200;          // Fix the width of the thumb nail images
        //         $n_height=150;         // Fix the height of the thumb nail imaage
        //         ////////////////////////////////////////////

        //         $tsrc="instructor/".$time.$_FILES['image1']['name'];   // Path where thumb nail image will be stored

        //         /////////////////////////////////////////////// Starting of GIF thumb nail creation///////////
                
        //         // Blog Image

        //         if (@$_FILES['image1']['type']=="image/gif")
        //         {
        //             $im=ImageCreateFromGIF($add);
        //             $width=ImageSx($im);              // Original picture width is stored
        //             $height=ImageSy($im);                  // Original picture height is stored
        //             $n_height=($n_width/$width) * $height; // Add this line to maintain aspect ratio
        //             $newimage=imagecreatetruecolor($n_width,$n_height);
        //             imageCopyResized($newimage,$im,0,0,0,0,$n_width,$n_height,$width,$height);
        //             if (function_exists("imagegif")) {
        //             Header("Content-type: image/gif");
        //             ImageGIF($newimage,$tsrc);
        //             }
        //             elseif (function_exists("imagejpeg")) {
        //             Header("Content-type: image/jpeg");
        //             ImageJPEG($newimage,$tsrc);
        //             }
        //             chmod("$tsrc",0777);
        //         }////////// end of gif file thumb nail creation//////////

        //         ////////////// starting of JPG thumb nail creation//////////
        //         if($_FILES['image1']['type']=="image/jpeg")
        //         {
        //             $im=ImageCreateFromJPEG($add); 
        //             $width=ImageSx($im);              // Original picture width is stored
        //             $height=ImageSy($im);             // Original picture height is stored
        //             $n_height=($n_width/$width) * $height; // Add this line to maintain aspect ratio
        //             $newimage=imagecreatetruecolor($n_width,$n_height);  
        //             $white = imagecolorallocate($newimage, 255, 255, 255); 
        //             imagefill($newimage, 0, 0, $white);                
        //             imageCopyResized($newimage,$im,0,0,0,0,$n_width,$n_height,$width,$height);
        //             ImageJpeg($newimage,$tsrc);
        //             chmod("$tsrc",0777);
        //         }
        //         if($_FILES['image1']['type']=="image/png")
        //         {
        //             $im=imagecreatefrompng($add); 
        //             $width=ImageSx($im);              // Original picture width is stored
        //             $height=ImageSy($im);             // Original picture height is stored
        //             $n_height=($n_width/$width) * $height; // Add this line to maintain aspect ratio
        //             $newimage=imagecreatetruecolor($n_width,$n_height); 
        //             $white = imagecolorallocate($newimage, 255, 255, 255); 
        //             imagefill($newimage, 0, 0, $white);                 
        //             imageCopyResized($newimage,$im,0,0,0,0,$n_width,$n_height,$width,$height);
        //             Imagepng($newimage,$tsrc);
        //         }
        //         return redirect()->back()->withErrors(['Staff Added Successfully!!']);
        //     }
        //     else
        //     {
        //         return redirect()->back()->withErrors(['Staff Added Successfully!! Unable to add image due to Not an Image!!']);
        //     }
        // }
        return redirect()->back()->withErrors(['Associate added successfully!!']);
    }
    public function deliveryagentvalidation($request)
    {
    	return $this->Validate($request, [
            'name' => 'required',
            'contact' => 'required',
            'email' => 'required|unique:users|max:255',
            'password' => 'required',
           
        ]);
    }
    
    public function viewdeliveryagent($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
           
                $data['data'] = DB::table('users')->where(['id' => $id])->get();
                $data['loc'] = DB::table('tbl_locations')->where(['status' => '1'])->orderBy('id', 'desc')->get();

                if(count($data) > 0)
                {
                    return view('admin.deliveryagent.viewdeliveryagent', $data);
                }
                else
                {
                    return view('admin.deliveryagent.viewdeliveryagent');
                }
            
        }
    }
    public function updatedeliveryagent(Request $req)
    {
        
        $id = $req->input('id');
        
        
        $password = $req->input('password');

        $req['password'] = bcrypt($password);
        $req['password1'] = $password; 

        $userUpdate  = User::where('id',$id)->first();
        if ($userUpdate) {
           $speak = $userUpdate->update($req->all());
        }
        DB::table('users')->where('id', $id)->update(['status' => '1' ,'type' => '2','pass' => $password]);

        // if($image != "")
        // {
        //     $time = time();

        //     $check = getimagesize($_FILES["image1"]["tmp_name"]);
        //     if($check !== false) 
        //     {
        //         if ($_FILES['image1']['size'] > 1048576) {
        //             return redirect()->back()->withErrors(['Updation completed Successfully!! Unable to add Iamge due to large size!!']);
        //         }
                
        //         // Move Blog Image
        //         $Blogimge="instructor/".$time.$_FILES['image1']['name'];
        //         //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

        //         $add="instructor/".$time.$_FILES['image1']['name']; // the path with the file name where the file will be stored, upload is the directory name. 

        //         if(move_uploaded_file ($_FILES['image1']['tmp_name'],$add)) 
        //         {

        //             $images[] = $Blogimge;
        //             DB::table('users')->where('id', $id)->update(['image' => $Blogimge]);

        //             File::delete($primage);

        //         }
        //         else
        //         {
        //             return redirect()->back()->withErrors(['Updation completed Successfully!! Unable to add image due to large size!!']);
        //         }

        //         ///////// Start the thumbnail generation//////////////
        //         $n_width=200;          // Fix the width of the thumb nail images
        //         $n_height=150;         // Fix the height of the thumb nail imaage
        //         ////////////////////////////////////////////

        //         $tsrc="instructor/".$time.$_FILES['image1']['name'];   // Path where thumb nail image will be stored

        //         /////////////////////////////////////////////// Starting of GIF thumb nail creation///////////
                
        //         // Blog Image

        //         if (@$_FILES['image1']['type']=="image/gif")
        //         {
        //             $im=ImageCreateFromGIF($add);
        //             $width=ImageSx($im);              // Original picture width is stored
        //             $height=ImageSy($im);                  // Original picture height is stored
        //             $n_height=($n_width/$width) * $height; // Add this line to maintain aspect ratio
        //             $newimage=imagecreatetruecolor($n_width,$n_height);
        //             imageCopyResized($newimage,$im,0,0,0,0,$n_width,$n_height,$width,$height);
        //             if (function_exists("imagegif")) {
        //             Header("Content-type: image/gif");
        //             ImageGIF($newimage,$tsrc);
        //             }
        //             elseif (function_exists("imagejpeg")) {
        //             Header("Content-type: image/jpeg");
        //             ImageJPEG($newimage,$tsrc);
        //             }
        //             chmod("$tsrc",0777);
        //         }////////// end of gif file thumb nail creation//////////

        //         ////////////// starting of JPG thumb nail creation//////////
        //         if($_FILES['image1']['type']=="image/jpeg")
        //         {
        //             $im=ImageCreateFromJPEG($add); 
        //             $width=ImageSx($im);              // Original picture width is stored
        //             $height=ImageSy($im);             // Original picture height is stored
        //             $n_height=($n_width/$width) * $height; // Add this line to maintain aspect ratio
        //             $newimage=imagecreatetruecolor($n_width,$n_height);  
        //             $white = imagecolorallocate($newimage, 255, 255, 255); 
        //             imagefill($newimage, 0, 0, $white);                
        //             imageCopyResized($newimage,$im,0,0,0,0,$n_width,$n_height,$width,$height);
        //             ImageJpeg($newimage,$tsrc);
        //             chmod("$tsrc",0777);
        //         }
        //         if($_FILES['image1']['type']=="image/png")
        //         {
        //             $im=imagecreatefrompng($add); 
        //             $width=ImageSx($im);              // Original picture width is stored
        //             $height=ImageSy($im);             // Original picture height is stored
        //             $n_height=($n_width/$width) * $height; // Add this line to maintain aspect ratio
        //             $newimage=imagecreatetruecolor($n_width,$n_height); 
        //             $white = imagecolorallocate($newimage, 255, 255, 255); 
        //             imagefill($newimage, 0, 0, $white);                 
        //             imageCopyResized($newimage,$im,0,0,0,0,$n_width,$n_height,$width,$height);
        //             Imagepng($newimage,$tsrc);
        //         }
        //         return redirect()->back()->withErrors(['Updation completed Successfully!!']);
        //     }
        //     else
        //     {
        //         return redirect()->back()->withErrors(['Updation completed Successfully!! Unable to add image due to Not an Image!!']);
        //     }
        // }
        return redirect()->back()->withErrors(['Updation completed Successfully!!']);
    } 
    public function removestaffimage($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
            if((Auth::user()->type == 2) || (Auth::user()->type == 4))
            {
            	$instid = Auth::user()->instid;
                $data = DB::table('users')->where(['id' => $id, 'instid' => $instid])->get();

                $uploads = '';
                foreach($data as $object)
                {
                    $uploads = $object->image;
                }

                if($uploads != '')
                {
                    File::delete($uploads);
                }

                DB::table('users')->where(['id' => $id, 'instid' => $instid])->update(['image' => '']);

                return redirect()->back()->withErrors(['Removed Successfully!!']);
            } else { return redirect('/'); }
        }

    }

    public function deliveryagentremove($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
            
              
 
                
                DB::table('users')->where(['id' => $id])->delete();

                return redirect('/deliveryagent')->withErrors(['Deleted Successfully!!']);
             
        }
    }

    // Staff Previlage starts here

    public function previlage($id)
    {
        
        if (!Auth::check()) { return redirect('/'); } else {
            
                $data['data'] = DB::table('users')->where(['id' => $id,'type' => '4'])->get();

                if(count($data) > 0)
                {
                    return view('admin.staff.staffprevilage', $data);
                }
                else
                {
                    return view('admin.staff.staffprevilage');
                }
           
        }
    }
    //code by asha
    // Program Assign to batch starts here

    public function programteacherassign()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            if((Auth::user()->type == 2) || (Auth::user()->type == 4))
            {
                $instid = Auth::user()->instid;

                $check = DB::table('tbl_academicyears')->where(['instid' => $instid, 'status' => '1'])->orderBy('id', 'desc')->get();
                $m = 0;
                $acdid = '';
                foreach($check as $object)
                {
                    if($m == 0)
                    {
                        $acdid = $object->id;
                    }
                    $m++;
                }

                $data['data'] = DB::table('users')->where(['instid' => $instid, 'position' => '6'])->orderBy('id', 'desc')->get();
                $data['acdyear'] = DB::table('tbl_academicyears')->where(['instid' => $instid, 'status' => '1'])->orderBy('id', 'desc')->get();

                if(count($data) > 0)
                {
                    return view('institution.programteacherassign', $data);
                }
                else
                {
                    return view('institution.programteacherassign');
                }
            } else { return redirect('/'); }
        }
    }
}

