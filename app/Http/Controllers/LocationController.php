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
use App\Models\tblLocation;

class LocationController extends Controller
{
    public function locationmanagement()
    {
        if(!Auth::check()) { return redirect('/'); } else {
                $data['data'] = DB::table('tbl_locations')->orderBy('id', 'asc')->get();
                if(count($data) > 0)
                {
                    return view('admin.location.locationmanagement', $data);
                }
                else
                {
                    return view('admin.location.locationmanagement');
                }
        }
    }
    
    public function newlocation()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            return view('admin.location.newlocation');
        } 
    }
    public function postlocation(Request $req)
    {
        $this->locationvalidation($req);
        tblLocation::create($req->all());
        $pstid = DB::getPdo()->lastInsertId();
        
        return redirect()->back()->withErrors(['Location Added Successfully!!']);
        
    }
    public function locationvalidation($request)
    {
        return $this->Validate($request, [
            'location' => 'required',
            
        ]);
    }
    public function viewlocation($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data['data'] = DB::table('tbl_locations')->where('id', '=', $id)->get();
                if(count($data) > 0)
                {
                    return view('admin.location.viewlocation', $data);
                }
                else
                {
                    return view('admin.location.viewlocation');
                }
        }
    }
    public function updatelocation(Request $req)
    {
        $this->locationvalidation($req);
        $id = $req->input('id'); 
        
        $userUpdate  = tblLocation::where('id',$id)->first(); 
        if ($userUpdate) {
           $speak = $userUpdate->update($req->all());
        }
        return redirect()->back()->withErrors(['Updated Successfully!!']);
    }
    public function locationremove($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
                $data = DB::table('tbl_locations')->where('id', '=', $id)->get();
                DB::table('tbl_locations')->where('id', $id)->delete();
                return redirect()->back()->withErrors(['Location Details Removed Successfully!!']);
        }
    }
}
