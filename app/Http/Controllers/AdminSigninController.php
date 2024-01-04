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
use App\User;
use Cookie;


class AdminSigninController extends Controller
{
    public function adminsignin(Request $req)
    {
       
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");

        $this->Validate($req, [
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);
        
        if(Auth::attempt(['email' => $req->email, 'password' => $req->password, 'status' => '1'])){

            // For Record Log

            $wl_id = Auth::user()->id;
            $wl_name = Auth::user()->name;

            $wl_mesage = $wl_name ." was login into the system at ".$dt." ".$time;

            DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);

            // Record Log Ends here
            if(Auth::user()->type == 1)
            {
            return redirect('/home');
            }
            if((Auth::user()->type == 2) || (Auth::user()->type == 3))
            {
                return redirect('/users');
            }
            if(Auth::user()->type == 4)  
            {
                return redirect('/direct-users');
            }
        }
        else
        {
        	return redirect()->back()->withErrors(['Ooops Something Wrong Happens!! Please try agian or Contact Admin!!']);
        }
    }
    public function usersignin(Request $req)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");

       

        $this->Validate($req, [
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);
        $check = DB::table('users')->where(['email' => $req->email ])->get();
        
            if(count($check)==0)
            {
                
              //  DB::table('users')->where(['id' => $checks->id])->delete();
              Session::put('perror1', 1);
    
              return redirect()->back()->withSuccess(['Ooops Somethggging Wrong Happens!! Please try agian!!']);  
                      }        
         
        if(Auth::attempt(['email' => $req->email, 'password' => $req->password])){

            // For Record Log

             $wl_id = Auth::user()->id;
            $wl_name = Auth::user()->name;
            $custype =  Auth::user()->type;

            $wl_mesage = $wl_name ." was login into the system at ".$dt." ".$time;

            DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);
            Cookie::queue(Cookie::make('user_id', Auth::user()->id, time() + 60 * 60 * 24 * 365));
            
            $ss = Session::get('variableName');
         
            if($ss != '') 
            {
                
                $count_check = DB::table('carts')->where(['user_id' => $ss])->count(); 


                if($count_check>0)
                {
                    if($custype==3)
                    {
                        $cartcheck = DB::table('carts')->where(['user_id' => $ss])->get(); 
                        foreach ($cartcheck as $cartchecks) {
                            $spp = $cartchecks->selsingleprice;
                            $totalprice = $cartchecks->selsingleprice * $cartchecks->count;
                            DB::table('carts')->where(['user_id' => $ss])->update(['total_price' => $totalprice,'singleprice' => $spp]);
                        }
                    }
                  
                    DB::table('carts')->where(['user_id' => $ss])->update(['user_id' => Auth::user()->id]);
    
                    if (Session::has('variableName')){          
            
                
                        Session::forget('variableName');
                        }
    
                    return redirect('/check-out');
                }
                else
                {
                    return redirect('/');
                }
            }
            else
            {
                return redirect('/');
            }
        }
        else
        {
            
            Session::put('perror', 1);

        	return redirect()->back()->withErrors(['Ooops Something Wrong Happens!! Please try agian!!']);
        }
    }
    public function loginwishlist($id)
    {
                $data['data'] = DB::table('tbl_cart_products')->where('id', '=', $id)->get();
                return view('site.loginwishlist', $data);


    }
    public function usersigninwish(Request $req,$id,$slug)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");

        $this->Validate($req, [
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);
        
        if(Auth::attempt(['email' => $req->email, 'password' => $req->password])){

            // For Record Log
            Cookie::queue(Cookie::make('user_id', Auth::user()->id, time() + 60 * 60 * 24 * 365));

             $wl_id = Auth::user()->id;
            $wl_name = Auth::user()->name;

            $wl_mesage = $wl_name ." was login into the system at ".$dt." ".$time;

            DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);

            // Record Log Ends here

            return redirect('single-product/'.$id.'/'.$slug);
        }
        else
        {
        	return redirect('/login')->withErrors(['Ooops Something Wrong Happens!! Please try agian!!']);
        }
    }
    
    public function usersignincheck(Request $req)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");

        $this->Validate($req, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        $check = DB::table('users')->where(['email' => $req->email ])->get();
        
        if(count($check)==0)
        {
            
          //  DB::table('users')->where(['id' => $checks->id])->delete();
          Session::put('perror1', 1);

          return redirect()->back()->withSuccess(['Ooops Somethggging Wrong Happens!! Please try agian!!']);  
                  }  
        if(Auth::attempt(['email' => $req->email, 'password' => $req->password])){

            // For Record Log
            Cookie::queue(Cookie::make('user_id', Auth::user()->id, time() + 60 * 60 * 24 * 365));

             $wl_id = Auth::user()->id;
            $wl_name = Auth::user()->name;
            $custype  = Auth::user()->type;


            $wl_mesage = $wl_name ." was login into the system at ".$dt." ".$time;

            DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);

            // Record Log Ends here
            
              $ss = Session::get('variableName');
         
        if($ss != '') 
        {
            
            $count_check = DB::table('carts')->where(['user_id' => $ss])->count(); 
          


            if($count_check>0)
            {
               
               
                if($custype==3)
                {
                    $cartcheck = DB::table('carts')->where(['user_id' => $ss])->get(); 
                    foreach ($cartcheck as $cartchecks) {
                        $spp = $cartchecks->selsingleprice;
                        $totalprice = $cartchecks->selsingleprice * $cartchecks->count;
                        DB::table('carts')->where(['user_id' => $ss])->update(['total_price' => $totalprice,'singleprice' => $spp]);
                    }
                }
                DB::table('carts')->where(['user_id' => $ss])->update(['user_id' => Auth::user()->id]);
                
                if (Session::has('variableName')){          
        
            
                    Session::forget('variableName');
                    }

                return redirect('/check-out');
            }
            else
            {
                return redirect('/');
            }
        }
        else
        {
            return redirect('/');
        }
        }
        else
        {
            Session::put('perror', 1);
            return redirect('/logincheckout')->withSuccess(['Ooops Something Wrong Happens!! Please try agian!!']);
        }
    }
    public function postuserregistercheck(Request $req)
    {
        
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");
        $email = $req->input('email');
        $contact = $req->input('contact');

        $pass = $req->input('password');
        $password = bcrypt($pass);
        $regtime = time();
        $apend = $email.'#'.$pass;
        $req['password'] = $password;
        $req['regtime'] = $regtime;
        $type=2;
        $name = $req->input('name');
        
        $check = DB::table('users')->where(['contact' => $contact, 'status' => '1'])->get();

        foreach ($check as $checks) {
            if($checks->id != '')
            {
                
              //  DB::table('users')->where(['id' => $checks->id])->delete();
                return redirect()->back()->withErrors(['This Mobile Number already exists please verify and try to login !!']);
            }
        }
       
        $this->architectregistervalidation($req);
        User::create($req->all());

        $pstid = DB::getPdo()->lastInsertId();
            DB::table('users')->where('id', $pstid)->update(['pass' => $pass]);

      
        DB::table('users')->where('id', $pstid)->update(['type' => '2']);
       
        Auth::loginUsingId($pstid);
        Cookie::queue(Cookie::make('user_id', Auth::user()->id, time() + 60 * 60 * 24 * 365));

        $wl_id = Auth::user()->id;
       $wl_name = Auth::user()->name;
       $custype  = Auth::user()->type;


       $wl_mesage = $wl_name ." was login into the system at ".$dt." ".$time;

       DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);

       // Record Log Ends here
       
         $ss = Session::get('variableName');

         if($ss != '') 
         {
             
             $count_check = DB::table('carts')->where(['user_id' => $ss])->count(); 
           
 
 
             if($count_check>0)
             {
                
                
                 if($custype==3)
                 {
                     $cartcheck = DB::table('carts')->where(['user_id' => $ss])->get(); 
                     foreach ($cartcheck as $cartchecks) {
                         $spp = $cartchecks->selsingleprice;
                         $totalprice = $cartchecks->selsingleprice * $cartchecks->count;
                         DB::table('carts')->where(['user_id' => $ss])->update(['total_price' => $totalprice,'singleprice' => $spp]);
                     }
                 }
                 DB::table('carts')->where(['user_id' => $ss])->update(['user_id' => Auth::user()->id]);
                 
                 if (Session::has('variableName')){          
         
             
                     Session::forget('variableName');
                     }
 
                 return redirect('/check-out');
             }
             else
             {
                 return redirect('/');
             }
         }
         else
         {
             return redirect('/');
         }

    
 
                
    }
    public function logout() 
    {
       //return("dd");
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");

            // For Record Log

            // $wl_id = Auth::user()->id;
            // $wl_name = Auth::user()->name;

            // $wl_mesage = $wl_name ." was logged out from the system at ".$dt." ".$time;

            // DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);

            // Record Log Ends here
            if(isset($_COOKIE['user_id']))
            {
                Cookie::queue(Cookie::forget('user_id'));
            }

        Auth::logout(); // logout user
        Session::flush();
        Redirect::back();
        return redirect('/')->withErrors(['Logout Succesfully!!']);
    }
    public function agentlogout() 
    {
       //return("dd");
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");

            // For Record Log

            // $wl_id = Auth::user()->id;
            // $wl_name = Auth::user()->name;

            // $wl_mesage = $wl_name ." was logged out from the system at ".$dt." ".$time;

            // DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);

            // Record Log Ends here
            if(isset($_COOKIE['user_id']))
            {
                Cookie::queue(Cookie::forget('user_id'));
            }

        Auth::logout(); // logout user
        Session::flush();
        Redirect::back();
        return redirect('/userlogin');
    }
    public function postuserregister(Request $req)
    {
        
      
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");
        $email = $req->input('email');
        $contact = $req->input('contact');

        $pass = $req->input('password');
        $password = bcrypt($pass);
        $regtime = time();
        $apend = $email.'#'.$pass;
        $req['password'] = $password;
        $req['regtime'] = $regtime;
        $type=2;
        $name = $req->input('name');
        
        $check = DB::table('users')->where(['contact' => $contact, 'status' => '1'])->get();

        foreach ($check as $checks) {
            if($checks->id != '')
            {
                
              //  DB::table('users')->where(['id' => $checks->id])->delete();
                return redirect()->back()->withErrors(['This Mobile Number already exists please verify and try to login !!']);
            }
        }
       
        $this->architectregistervalidation($req);
        User::create($req->all());

        $pstid = DB::getPdo()->lastInsertId();
            DB::table('users')->where('id', $pstid)->update(['pass' => $pass]);

       // return $pstid;
        //DB::table('users')->where('id', $pstid)->update(['password' => $password, 'regtime' => $regtime]);
       // DB::table('tbl_profile_completions')->insert(['regtime' => $regtime, 'user' => $pstid]);

       //  $otp = random_int(100000, 999999);

       // DB::table('users')->where('id', $pstid)->update(['otp' => $otp]);
        DB::table('users')->where('id', $pstid)->update(['type' => '2']);
       
        Auth::loginUsingId($pstid);
        
        $wl_id = Auth::user()->id;
        $wl_name = Auth::user()->name;
        $custype =  Auth::user()->type;

        $wl_mesage = $wl_name ." was login into the system at ".$dt." ".$time;

        DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);
        Cookie::queue(Cookie::make('user_id', Auth::user()->id, time() + 60 * 60 * 24 * 365));
        
        $ss = Session::get('variableName');
     
        if($ss != '') 
        {
            
            $count_check = DB::table('carts')->where(['user_id' => $ss])->count(); 


            if($count_check>0)
            {
                if($custype==3)
                {
                    $cartcheck = DB::table('carts')->where(['user_id' => $ss])->get(); 
                    foreach ($cartcheck as $cartchecks) {
                        $spp = $cartchecks->selsingleprice;
                        $totalprice = $cartchecks->selsingleprice * $cartchecks->count;
                        DB::table('carts')->where(['user_id' => $ss])->update(['total_price' => $totalprice,'singleprice' => $spp]);
                    }
                }
              
                DB::table('carts')->where(['user_id' => $ss])->update(['user_id' => Auth::user()->id]);

                if (Session::has('variableName')){          
        
            
                    Session::forget('variableName');
                    }

                return redirect('/check-out');
            }
            else
            {
                return redirect('/');
            }
        }
        else
        {
            return redirect('/');
        }
       // return view('site.register');
        //return redirect('/otp')->with('message', $apend);
      // return redirect('/')->withErrors(['Your Registration has been successfully completed']);

        // if(Auth::attempt(['email' => $email, 'password' => $pass])){
        //     return redirect('/artist-profile');
        // }
        //return redirect('/artist-profile-completion/'.$regtime);//->withErrors(['Category Added Successfully!!']);        
    }
    public function postresellerregister(Request $req)
    {
        
      
        $email = $req->input('email');
        $contact = $req->input('contact');

        $pass = $req->input('password');
        $password = bcrypt($pass);
        $regtime = time();
        $apend = $email.'#'.$pass;
        $req['password'] = $password;
        $req['regtime'] = $regtime;
        $type=2;
        $name = $req->input('name');
        
        $check = DB::table('users')->where(['email' => $email, 'status' => '1'])->get();

        foreach ($check as $checks) {
            if($checks->id != '')
            {
                return("uuu");
              //  DB::table('users')->where(['id' => $checks->id])->delete();
                return redirect()->back()->withErrors(['This email id already exists please verify and try to login !!']);
            }
        }
       
        $this->architectregistervalidation($req);
        User::create($req->all());

        $pstid = DB::getPdo()->lastInsertId();
            DB::table('users')->where('id', $pstid)->update(['pass' => $pass]);

       // return $pstid;
        //DB::table('users')->where('id', $pstid)->update(['password' => $password, 'regtime' => $regtime]);
       // DB::table('tbl_profile_completions')->insert(['regtime' => $regtime, 'user' => $pstid]);

       //  $otp = random_int(100000, 999999);

       // DB::table('users')->where('id', $pstid)->update(['otp' => $otp]);
        DB::table('users')->where('id', $pstid)->update(['type' => '3']);
       
        
       // return view('site.register');
        //return redirect('/otp')->with('message', $apend);
       return redirect()->back()->withErrors(['Your Registration has been successfully completed']);

        // if(Auth::attempt(['email' => $email, 'password' => $pass])){
        //     return redirect('/artist-profile');
        // }
        //return redirect('/artist-profile-completion/'.$regtime);//->withErrors(['Category Added Successfully!!']);        
    }
    public function architectregistervalidation($request)
    {
        return $this->Validate($request, [
            'name' => 'required|max:255',
          
        ]);
    }
    public function login()
    {
        $data['pdttype'] = DB::table('tbl_producttypes')->where('status', '=', '1')->get();
        if (Auth::check()) { 
            $userid = Auth::user()->id;
        }
        else
        {
            $userid = Session::get('variableName');
        }
        $data['products'] = DB::table('carts')
        ->join('tbl_cart_products','carts.product_id','=','tbl_cart_products.id')
        ->join('tbl_sizes','carts.size','=','tbl_sizes.id')
        ->where('carts.user_id',$userid)
        ->select('tbl_cart_products.*','carts.id as cart_id','tbl_sizes.size as sizename','carts.size as cartsize','carts.count as cart_count','carts.singleprice as sp','carts.total_price as total_price',
        'carts.selsingleprice as ssp','carts.seltotalprice as seltotalprice')
        ->get();
        return view('site.login', $data);


    }
    public function loginotp()
    {
        $data['pdttype'] = DB::table('tbl_producttypes')->where('status', '=', '1')->get();
        if (Auth::check()) { 
            $userid = Auth::user()->id;
        }
        else
        {
            $userid = Session::get('variableName');
        }
        $data['products'] = DB::table('carts')
        ->join('tbl_cart_products','carts.product_id','=','tbl_cart_products.id')
        ->join('tbl_sizes','carts.size','=','tbl_sizes.id')
        ->where('carts.user_id',$userid)
        ->select('tbl_cart_products.*','carts.id as cart_id','tbl_sizes.size as sizename','carts.size as cartsize','carts.count as cart_count','carts.singleprice as sp','carts.total_price as total_price',
        'carts.selsingleprice as ssp','carts.seltotalprice as seltotalprice')
        ->get();
        return view('site.loginotp', $data);


    }
    public function loginotp1()
    {
        $data['pdttype'] = DB::table('tbl_producttypes')->where('status', '=', '1')->get();
        if (Auth::check()) { 
            $userid = Auth::user()->id;
        }
        else
        {
            $userid = Session::get('variableName');
        }
        $data['products'] = DB::table('carts')
        ->join('tbl_cart_products','carts.product_id','=','tbl_cart_products.id')
        ->join('tbl_sizes','carts.size','=','tbl_sizes.id')
        ->where('carts.user_id',$userid)
        ->select('tbl_cart_products.*','carts.id as cart_id','tbl_sizes.size as sizename','carts.size as cartsize','carts.count as cart_count','carts.singleprice as sp','carts.total_price as total_price',
        'carts.selsingleprice as ssp','carts.seltotalprice as seltotalprice')
        ->get();
        return view('site.loginotp1', $data);


    }
    public function logincheck()
    {
        $data['pdttype'] = DB::table('tbl_producttypes')->where('status', '=', '1')->get();
        if (Auth::check()) { 
            $userid = Auth::user()->id;
        }
        else
        {
            $userid = Session::get('variableName');
        }
        $data['products'] = DB::table('carts')
        ->join('tbl_cart_products','carts.product_id','=','tbl_cart_products.id')
        ->join('tbl_sizes','carts.size','=','tbl_sizes.id')
        ->where('carts.user_id',$userid)
        ->select('tbl_cart_products.*','carts.id as cart_id','tbl_sizes.size as sizename','carts.size as cartsize','carts.count as cart_count','carts.singleprice as sp','carts.total_price as total_price',
        'carts.selsingleprice as ssp','carts.seltotalprice as seltotalprice')
        ->get();
        return view('site.logincheck', $data);


    }
    public function loginwish()
    {
        $data['pdttype'] = DB::table('tbl_producttypes')->where('status', '=', '1')->get();
        if (Auth::check()) { 
            $userid = Auth::user()->id;
        }
        else
        {
            $userid = Session::get('variableName');
        }
        $data['products'] = DB::table('carts')
        ->join('tbl_cart_products','carts.product_id','=','tbl_cart_products.id')
        ->join('tbl_sizes','carts.size','=','tbl_sizes.id')
        ->where('carts.user_id',$userid)
        ->select('tbl_cart_products.*','carts.id as cart_id','tbl_sizes.size as sizename','carts.count as cart_count','carts.singleprice as sp','carts.total_price as total_price')
        ->get();
        return view('site.loginwish', $data);


    }
    public function userloginwish(Request $req)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");

        $this->Validate($req, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        
        if(Auth::attempt(['email' => $req->email, 'password' => $req->password])){

            // For Record Log

             $wl_id = Auth::user()->id;
            $wl_name = Auth::user()->name;

            $wl_mesage = $wl_name ." was login into the system at ".$dt." ".$time;

            DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);

            // Record Log Ends here
            
              $ss = Session::get('variableName');
         
        if($ss != '') 
        {
            
            $count_check = DB::table('carts')->where(['user_id' => $ss])->count(); 
            if($count_check>0)
            {
                DB::table('carts')->where(['user_id' => $ss])->update(['user_id' => Auth::user()->id]);

                if (Session::has('variableName')){          
        
            
                    Session::forget('variableName');
                    }

                return redirect('/productlist');
            }
            else
            {
                return redirect('/productlist');
            }
        }
        else
        {
            return redirect('/productlist');
        }
        }
        else
        {
        	return redirect('/loginwish')->withErrors(['Ooops Something Wrong Happens!! Please try agian!!']);
        }
    }
    public function resellerlogin()
    {
        $data['pdttype'] = DB::table('tbl_producttypes')->where('status', '=', '1')->get();
        return view('site.resellerlogin', $data);


    }
    public function resellersignin(Request $req)
    {
        date_default_timezone_set("Asia/Kolkata"); //India time (GMT+5:30) echo date('d-m-Y H:i:s');
        $rec_date = date('Y-m-d');
        $dt = date('d/m/Y');
        $time = date("H:i");

       

        $this->Validate($req, [
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);
        
        if(Auth::attempt(['email' => $req->email, 'password' => $req->password, 'type' => 3])){

            // For Record Log

             $wl_id = Auth::user()->id;
            $wl_name = Auth::user()->name;

            $wl_mesage = $wl_name ." was login into the system at ".$dt." ".$time;

            DB::table('tbl_worklog')->insert(['userid' => $wl_id, 'username' => $wl_name, 'message' => $wl_mesage, 'time' => $time, 'date' => $rec_date]);

            // Record Log Ends here

            return redirect('/');
        }
        else
        {
            Session::put('perror', 1);

        	return redirect('/login')->withSuccess(['Ooops Something Wrong Happens!! Please try agian!!']);
        }
    }
    public function uloginok(Request $request)
    { 
        $phone = $request->phone;
        $otp =   $request->otp;
        
        $check = DB::table('users')->where('contact',$phone)
       ->where('otp', $otp)->get();
      
       
        // DB::table('tbl_profile_completions')->insert(['regtime' => $regtime, 'user' => $pstid]);
        foreach ($check as $checks) {
          
            if($checks->id != '')
            {
              
                  $userid = $checks->id;
                 

               Auth::loginUsingId($userid);
              
                  $uname = $checks->name;
                $wl_id = Auth::user()->id;
                $wl_name = Auth::user()->name;
                $custype =  Auth::user()->type;
                 $ss = Session::get('variableName');
         
                if($ss != '') 
                {
                    
                    $count_check = DB::table('carts')->where(['user_id' => $ss])->count(); 
    
    
                    if($count_check>0)
                    {
                        
                      
                        DB::table('carts')->where(['user_id' => $ss])->update(['user_id' => Auth::user()->id]);
        
                        if (Session::has('variableName')){          
                
                    
                            Session::forget('variableName');
                            }
                        
                        return redirect('/check-out');
                    }
                    else
                    {
                        
                        return redirect('/');
                    }
                }
                else
                {
                   
                    return redirect('/');
                }
            }
            
            }
            if(count($check)==0)  
            {
                 
             
        
                    return redirect('/loginotp')->withErrors(['Otp is incorrect!! Please try agian!!']);
                }
        }
       
        
  
}
