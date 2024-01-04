<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use View;
use Session;
use Illuminate\Support\Facades\Schema;
use Auth;
use Illuminate\Support\Facades\Redirect;
use File;
use App\SystemSettings;

class SettingsController extends Controller
{
    public function settings()
    {
        return view('admin.settings.settings');
    }
    public function updatepassword(Request $req)
    {
        $this->setiingvalidation($req);
        //$req['password'] = bcrypt($req->password);
       
        $email = $req->input('email');
        $password = $req->input('password');
        $user = $req->input('user');

        $new = bcrypt($password);

        $agents = DB::table('users')->where([
                ['id', '=', $user],
                ['email', '=', $email],
                ])->pluck( 'id');

        if($agents != '[]')
        {
            DB::table('users')
            ->where('id', $user)
            ->update(['password' => $new]);

            return redirect('/settings')->withErrors(['Password Updated Succesfully!!']);
        }
        else
        {
            return redirect('/settings')->withErrors(['Unable to Process!!']);
        }

    }
    
    public function changepassword(Request $req)
    {
        $this->setiingvalidation($req);
        //$req['password'] = bcrypt($req->password);
       
        $email = $req->input('email');
        $password = $req->input('password');
        $user = $req->input('user');

        $new = bcrypt($password);

        $agents = DB::table('users')->where([
                ['id', '=', $user],
                ['email', '=', $email],
                ])->pluck( 'id');

        if($agents != '[]')
        {
            DB::table('users')
            ->where('id', $user)
            ->update(['password' => $new, 'pass' => $password]);

            return redirect()->back()->withErrors(['Password Updated Succesfully!!']);
        }
        else
        {
            return redirect()->back()->withErrors(['Unable to Process!!']);
        }

    }
    public function setiingvalidation($request)
    {
        return $this->Validate($request, [
            'email' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }
    //   SYSTEM STTINGS STARTS FRO HERE

    public function systemsettings()
    {
        if (!Auth::check()) { return redirect('/system-manager'); } else {
            $data['data'] = DB::table('system_settings')->orderBy('id', 'desc')->get();
                 
            if(count($data) > 0)
            {
                return view('admin.systemsettings', $data);
            }
            else
            {
                return view('admin.systemsettings');
            }
        }
    }
    public function viewsystemsettings($id)
    {
        if (!Auth::check()) { return redirect('/system-manager'); } else {
            $data['data'] = DB::table('system_settings')->where([['id', '=', $id]])->get();

            if(count($data) > 0)
            {
                return view('admin.viewsystemsettings', $data);
            }
            else
            {
                return view('admin.viewsystemsettings');
            }
        }
    }
    public function updatesystemsettings(Request $req)
    {
        
        $id = $req->input('id');
        $webimage = $req->file('webimage');

        $userUpdate  = SystemSettings::where('id',$id)->first();
        if ($userUpdate) {
           $speak = $userUpdate->update($req->all());
        }
        $pstid = $id;
        if($webimage != "")
        {
            $wpcheck = getimagesize($_FILES["webimage"]["tmp_name"]);
            if($wpcheck !== false) 
            {
                if($_FILES['webimage']['size'] < 1048576) 
                {
                    $w1filename_sm = $_FILES['webimage']['name'];
                    $w1ext_sm = \File::extension($w1filename_sm);

                    $w1time_sm = $pstid.'.'.$w1ext_sm;

                    $w1Blogimge="system/".$w1time_sm;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $w1add="system/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['webimage']['tmp_name'],$w1add)) 
                    {
                        $images[] = $w1Blogimge;
                        DB::table('system_settings')->where('id', $pstid)->update(['image' => $w1Blogimge]);
                    }
                }
            }
        }
        return redirect('/systemsettings')->withErrors(['Updation completed Successfully!!']);
    }
    public function removepreviousimage($id)
    {
        if (!Auth::check()) { return redirect('/system-manager'); } else {
            if (!Auth::check()) { return redirect('/'); } else {
                if(Auth::user()->id == 1)
                {
                    $data = DB::table('system_settings')->where('id', '=', $id)->get();
                    $uploads = '';
                    foreach($data as $object)
                    {
                        $uploads = $object->image;
                    }

                    if($uploads != '')
                    {
                        File::delete($uploads);
                    }
                    
                    DB::table('system_settings')->where('id', $id)->update(['image' => '']);

                    return redirect()->back()->withErrors(['Image Removed Successfully!!']);
                } else { return redirect('/'); }
            }
        }
    }
}
