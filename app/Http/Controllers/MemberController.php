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


class MemberController extends Controller
{
    public function directmembers()
    {
        if (!Auth::check()) { return redirect('/system-manager'); } else {
            $data['data'] = DB::table('tbl_shops')->where(['agentid' => '0'])->orderBy('id', 'desc')->get();
                 
            if(count($data) > 0)
            {
                return view('admin.members.members', $data);
            }
            else
            {
                return view('admin.members.members');
            }
        }
    }
    public function memberremove($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
              
               
               
              $data = DB::table('tbl_shops')->where('regid', '=', $id)->get();
                $webimage = '';
                $webimage1 = '';
                $gallery1 = '';
                $gallery2 = '';
                $gallery3 = '';
                $gallery4 = '';

                foreach($data as $object)
                {
                    $webimage = $object->logo;
                    $webimage1 = $object->profile_phto;
                    $gallery1 = $object->gallery1;
                    $gallery2 = $object->gallery2;
                    $gallery3 = $object->gallery3;
                    $gallery4 = $object->gallery4;
                }

                if($webimage != '')
                {
                    File::delete($webimage);
                }
                if($webimage1 != '')
                {
                    File::delete($webimage1);
                }
                if($gallery1 != '')
                {
                    File::delete($gallery1);
                }
                if($gallery2 != '')
                {
                    File::delete($gallery2);
                }
                if($gallery3 != '')
                {
                    File::delete($gallery3);
                }
                if($gallery4 != '')
                {
                    File::delete($gallery4);
                }
                
                DB::table('tbl_shops')->where('regid', $id)->delete();
                
                // Remove from user  table
                
                DB::table('users')->where('regid', $id)->delete();

                return redirect()->back()->withErrors(['Contents Deleted Successfully!!']);
            }
             
        

    }
    public function viewcompanymembers($id)
    {
        if (!Auth::check()) { return redirect('/system-manager'); } else {
            $data['data'] = DB::table('tbl_shops')->where(['agentid' => $id])->orderBy('id', 'desc')->get();
                 
            if(count($data) > 0)
            {
                return view('admin.company.members', $data);
            }
            else
            {
                return view('admin.company.members');
            }
        }
    }
    public function viewdamembers($id)
    {
        if (!Auth::check()) { return redirect('/system-manager'); } else {
            $data['data'] = DB::table('tbl_shops')->where(['agentid' => $id])->orderBy('id', 'desc')->get();
                 
            if(count($data) > 0)
            {
                return view('admin.deliveryagent.members', $data);
            }
            else
            {
                return view('admin.deliveryagent.members');
            }
        }
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
