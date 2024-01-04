<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route; 
use Illuminate\Support\Facades\Mail;
use DB;
use View;
use Session;
use Auth;
use File;
use App\Models\tblShop;
use App\Models\tblService;
use App\Models\tbl_salespartner;
use App\Models\tbl_payment;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;
use App\Mail\SendQRCode;
use App\Models\tblSubcategory;
use App\Mail\ContactMail;
use App\Mail\DirectUser;

 

class ContactController extends Controller
{

    public function users()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            $wl_id = Auth::user()->id;
            $data['data'] = DB::table('tbl_shops')->where('agentid',$wl_id,)->orderBy('id', 'desc')->get();
            $data['subcategory'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();
            $data['salespartner'] = DB::table('tbl_salespartners')->orderBy('id', 'asc')->get();
            //$data['data'] = DB::table('tbl_cart_products')->where(['subscription' => '0'])->orderBy('id', 'desc')->paginate(20);
                 
            if(count($data) > 0)
            {
                return view('delivery_agent.member_management', $data);
            }
            else
            {
                return view('delivery_agent.member_management');
            }
        }
    }
    public function directusers()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            $wl_id = Auth::user()->regid;
            $data['data'] = DB::table('tbl_shops')->where('regid',$wl_id)->orderBy('id', 'desc')->get();
            $data['subcategory'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();
            //$data['data'] = DB::table('tbl_cart_products')->where(['subscription' => '0'])->orderBy('id', 'desc')->paginate(20);
                 
            if(count($data) > 0)
            {
                return view('delivery_agent.direct_members', $data);
            }
            else
            {
                return view('delivery_agent.direct_members');
            }
        }
    } 
           
    public function addcontact()
    {
            $data['cat'] = DB::table('tbl_categories')->orderBy('id', 'asc')->get();
            $data['category'] = DB::table('tbl_categories')->orderBy('id', 'desc')->get();
            $data['subcategory'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();
            $data['location'] = DB::table('tbl_locations')->orderBy('id', 'asc')->get();
            $data['salespartner'] = DB::table('tbl_salespartners')->orderBy('id', 'asc')->get();
            
            if(count($data) > 0)
            {
                return view('delivery_agent.add_contact', $data);
            }
            else
            {
                return view('delivery_agent.add_contact');
            }

        }
         public function add_contact()
    {
            $data['cat'] = DB::table('tbl_categories')->orderBy('id', 'asc')->get();
            $data['category'] = DB::table('tbl_categories')->orderBy('id', 'desc')->get();
            $data['subcategory'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();
            $data['location'] = DB::table('tbl_locations')->orderBy('id', 'asc')->get();
               
            if(count($data) > 0)
            {
                return view('delivery_agent.addcontact', $data);
            }
            else
            {
                return view('delivery_agent.addcontact');
            }

        }

        public function agentcontact(request $req, $id)
    {

            $count = 0;
            $count = DB::table('users')->where('direct_link', $id)->count();
            if($count == 0)
            {
                return redirect('/')->withErrors(['Associates details not found!!']); 
                exit();
            }

            $data['cat'] = DB::table('tbl_categories')->orderBy('id', 'asc')->get();
            $data['category'] = DB::table('tbl_categories')->orderBy('id', 'desc')->get();
            $data['subcategory'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();
            $data['location'] = DB::table('tbl_locations')->orderBy('id', 'asc')->get();
            $data['user'] = DB::table('users')->where('id', $id)->get();
            
            if(count($data) > 0)
            {
                return view('delivery_agent.agentcontact', $data);
            }
            else
            {
                return view('delivery_agent.agentcontact');
            }

        }
        public function postshop(Request $req)
        {
            $this->cartproductvalidation($req);
            
          
            $shopname = $req->input('org_name');
            $email  = $req->input('email');
            $logo = $req->file('logo');
            $profile_phto = $req->file('profile_phto');
            $gallery1 = $req->file('gallery1');
            $gallery2 = $req->file('gallery2');
            $gallery3 = $req->file('gallery3');
            $gallery4 = $req->file('gallery4');
            $req['profile_id'] = 'DVC'.rand(100, 999);
            $regid = time().rand(10,99);
            $req['regid'] = $regid;
                 
          //  return $req->all();
            tblShop::create($req->all());
    
            $pstid = DB::getPdo()->lastInsertId();
            // $servcount = count($req['services']);
        
            // for($i=0;$i<$servcount;$i++)
            // {
            //     $shop_id = $pstid;
               
            //     $tblService = new tblService;
            //     $tblService ->services=$req['services'][$i];
            //     $tblService ->shop_id=$pstid;
            //     $tblService->save();
            // }
               
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
            
            $data = DB::table('tbl_shops')->where('id', '=', $pstid)->get();
          

            foreach($data as $object)
            {
                // $shop = $object->slug;
                $shop = $object->regid; 
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
            if($profile_phto!="")
            {
            if($_FILES["profile_phto"]["tmp_name"] != "")
            {
                $wpcheck = getimagesize($_FILES["profile_phto"]["tmp_name"]);
                if($wpcheck !== false) 
                {
                    if($_FILES['profile_phto']['size'] < 1048576) 
                    {
                        $w1filename_sm = $_FILES['profile_phto']['name'];
                        $w1ext_sm = \File::extension($w1filename_sm);
    
                        $w1time_sm =   'w1'. $pstid.'.'.$w1ext_sm;

                      
    
                        $w1Blogimge1="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['profile_phto']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge1;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['profile_phto' => $w1Blogimge1]);
                        }
                    }
                }
            }
        }
            if($gallery1!="")
            {
            if($_FILES["gallery1"]["tmp_name"] != "")
            {
                $wpcheck = getimagesize($_FILES["gallery1"]["tmp_name"]);
                if($wpcheck !== false) 
                {
                    if($_FILES['gallery1']['size'] < 1048576) 
                    {
                        $w1filename_sm = $_FILES['gallery1']['name'];
                        $w1ext_sm = \File::extension($w1filename_sm);
    
                        $w1time_sm = 'w2'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery1']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery1' => $w1Blogimge]);
                        }
                    }
                }
            }
        }
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
    
                        $w1time_sm =  'w3'.$pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery2']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery2' => $w1Blogimge]);
                        }
                    }
                }
            }
        }
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
    
                        $w1time_sm =  'w4'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery3']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery3' => $w1Blogimge]);
                        }
                    }
                }
            }
        }
        
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
    
                        $w1time_sm =  'w5'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery4']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery4' => $w1Blogimge]);
                        }
                    }
                }
            }
        }
            

             $email  = $req->input('personal_mail');
            
            $qrCode = QrCode::format('png')->size(300)->generate('https://exconnectnfc.com/dc/'.$shop ,public_path('img/qr-'.$pstid.'.png'));

                $image = 'https://exconnectnfc.com/public/img/qr-'.$pstid.'.png';
                $link =  'https://exconnectnfc.com/dc/'.$shop;

                    $data = array(
                        'qr' => $qrCode,
                        'image'=>$image,
                        'link'=>$link,
                       
                    );
                    Mail::to($email)->bcc(['vipin@extrememedia.in'])->send(new ContactMail($data));
                
        
        return redirect()->back()->withErrors(['User Details Added Successfully!!']);    
    }
    public function userpostcontact(Request $req)
        {
            $this->cartproductvalidation($req);
            
          
            $shopname = $req->input('org_name');
            $email  = $req->input('email');
            $logo = $req->file('logo');
            $regid = time().rand(10,99);
            $profile_phto = $req->file('profile_phto');
            $gallery1 = $req->file('gallery1');
            $gallery2 = $req->file('gallery2');
            $gallery3 = $req->file('gallery3');
            $gallery4 = $req->file('gallery4');
            $req['profile_id'] = 'DVC'.rand(100, 999);
            $req['regid'] = $regid;
            $personal_mail  = $req->input('personal_mail');
            
            $cnt = DB::table('users')->where('email', $personal_mail)->count();
            if($cnt>0){
                return redirect()->back()->withErrors(['Sorry!! Email already registered!! Please try with another one!!']);
                exit();
            }
                 
          //  return $req->all();
            tblShop::create($req->all());
    
            $pstid = DB::getPdo()->lastInsertId();
            // $servcount = count($req['services']);
        
            // for($i=0;$i<$servcount;$i++)
            // {
            //     $shop_id = $pstid;
               
            //     $tblService = new tblService;
            //     $tblService ->services=$req['services'][$i];
            //     $tblService ->shop_id=$pstid;
            //     $tblService->save();
            // }
               
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
            
            $data = DB::table('tbl_shops')->where('id', '=', $pstid)->get();
          

            foreach($data as $object)
            {
                // $shop = $object->slug;
                $shop = $object->regid; 
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
            if($profile_phto!="")
            {
            if($_FILES["profile_phto"]["tmp_name"] != "")
            {
                $wpcheck = getimagesize($_FILES["profile_phto"]["tmp_name"]);
                if($wpcheck !== false) 
                {
                    if($_FILES['profile_phto']['size'] < 1048576) 
                    {
                        $w1filename_sm = $_FILES['profile_phto']['name'];
                        $w1ext_sm = \File::extension($w1filename_sm);
    
                        $w1time_sm =   'w1'. $pstid.'.'.$w1ext_sm;

                      
    
                        $w1Blogimge1="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['profile_phto']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge1;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['profile_phto' => $w1Blogimge1]);
                        }
                    }
                }
            }
        }
            if($gallery1!="")
            {
            if($_FILES["gallery1"]["tmp_name"] != "")
            {
                $wpcheck = getimagesize($_FILES["gallery1"]["tmp_name"]);
                if($wpcheck !== false) 
                {
                    if($_FILES['gallery1']['size'] < 1048576) 
                    {
                        $w1filename_sm = $_FILES['gallery1']['name'];
                        $w1ext_sm = \File::extension($w1filename_sm);
    
                        $w1time_sm = 'w2'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery1']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery1' => $w1Blogimge]);
                        }
                    }
                }
            }
        }
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
    
                        $w1time_sm =  'w3'.$pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery2']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery2' => $w1Blogimge]);
                        }
                    }
                }
            }
        }
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
    
                        $w1time_sm =  'w4'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery3']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery3' => $w1Blogimge]);
                        }
                    }
                }
            }
        }
        
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
    
                        $w1time_sm =  'w5'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery4']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery4' => $w1Blogimge]);
                        }
                    }
                }
            }
        }
            

            $email  = $req->input('personal_mail');
            $p_new = rand(100000, 999999);
            $password_new = bcrypt($p_new); 
            $created = date('Y-m-d H:i:s');
            
            $userinsert = DB::table('users')->insert(['name' => $shopname, 'email' => $email, 'password' => $password_new, 'pass' => $p_new, 'type' => '4', 'regid' => $regid, 'created_at' => $created, 'updated_at' => $created]);
            
            $qrCode = '';//QrCode::format('png')->size(300)->generate('https://exconnectnfc.com/dc/'.$shop ,public_path('img/qr-'.$pstid.'.png'));

                $image = 'https://exconnectnfc.com/public/img/qr-'.$pstid.'.png';
                $link =  'https://exconnectnfc.com/dc/'.$shop;

                    $data = array(
                        'qr' => $qrCode,
                        'image'=>$image,
                        'link'=>$link,
                        'email'=>$email,
                        'password'=>$p_new,
                    );
                    //Mail::to($email)->bcc(['vipin@extrememedia.in'])->send(new DirectUser($data));
                
        
        return redirect()->back()->withErrors(['User Details Added Successfully!!']);    
    }
    public function cartproductvalidation($request)
    {
        return $this->Validate($request, [
             
            'first_name' => 'required',
            'last_name' => 'required',
            'org_name' => 'required',
            'designation' => 'required',
            'profile_phto' => 'required',
            'profile_phto' => 'mimes:jpeg,jpg,png',
            'profile_phto' => 'max:1024',
            'primary_contact' => 'required',
            'office_mail' => 'required',
            'address_line1' => 'required',
            'pincode' => 'required',

        ]);
    }
    protected function getproductRelatedSlugs($slug)
    {
        $slugcount = DB::table('tbl_shops')->where(['slug' => $slug])->count();
        return $slugcount;
    }
    public function digitalcard($id)
    {
        

        $data['data'] = DB::table('tbl_shops')
       // ->where('tbl_shops.slug','=',$id)
        ->where('tbl_shops.regid','=',$id)
        ->select('tbl_shops.*')
        ->get();

          $check = DB::table('tbl_shops')->where('slug',$id)->get();
      
       
        // DB::table('tbl_profile_completions')->insert(['regtime' => $regtime, 'user' => $pstid]);
        foreach ($check as $checks) {
            $sid = $checks->id;

        $data['services'] = DB::table('tbl_services')->where(['shop_id' => $sid])->get();
           
        }
            if(count($data) > 0)
            {
                return view('digitalcard.digitalcard', $data);
            }
            else
            {
                return view('digitalcard.digitalcard');
            }
       
    }

    public function getSubcat(Request $request)
    {
        $data['subcat'] = tblSubcategory::where("category",$request->cat_id)
                    ->get(["subcategory","id"]);
        return response()->json($data);
    }
    public function viewcontact($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {

            $data['data'] = DB::table('tbl_shops')->where(['id' => $id])->get();

              $data['salespartner'] = DB::table('tbl_salespartners')->orderBy('id', 'asc')->get();
            
                  
            if(count($data) > 0)
            {
                return view('delivery_agent.edit_contact', $data);
            }
            else
            {
                return view('delivery_agent.edit_contact');
            }
        }
    }
    public function viewdirectcontact($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {

            $data['data'] = DB::table('tbl_shops')->where(['regid' => $id])->get();
            
            if(count($data) > 0)
            {
                return view('delivery_agent.edit_direct_contact', $data);
            }
            else
            {
                return view('delivery_agent.edit_direct_contact');
            }
        }
    }
    public function memberedit($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {

            $data['data'] = DB::table('tbl_shops')->where(['regid' => $id])->get();
            
            if(count($data) > 0)
            {
                return view('admin.edit_contact', $data);
            }
            else
            {
                return view('admin.edit_contact');
            }
        }
    }

    public function updatecontact(Request $req)
    {
        // $this->cartproductvalidation($req);

        $id = $req->input('id'); 

        // Previous Images
        $prelogo = $req->input('prelogo');
        $preprofilepic = $req->input('preprofilepic');
       

        $logo = $req->file('logo');
        $profile_phto = $req->file('profile_phto');
        
        
        // Gallery Updation starts here
        $gallery1 = $req->file('gallery1');
        $gallery2 = $req->file('gallery2');
        $gallery3 = $req->file('gallery3');
        $gallery4 = $req->file('gallery4');

        $pregallery1 = $req->input('pregallery1');
        $pregallery2 = $req->input('pregallery2');
        $pregallery3 = $req->input('pregallery3');
        $pregallery4 = $req->input('pregallery4');
       
        $userUpdate  = tblShop::where('id',$id)->first();
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
                        DB::table('tbl_shops')->where('id', $pstid)->update(['logo' => $w1Blogimge]);
                        if(File::exists($prelogo)) 
                        {
                            File::delete($prelogo);
                        }
                    }
                }
            }
        }
    }
    if($profile_phto!="")
        {
        if($_FILES["profile_phto"]["tmp_name"] != "")
        {
            $wpcheck11 = getimagesize($_FILES["profile_phto"]["tmp_name"]);
            if($wpcheck11 !== false) 
            {
                if($_FILES['profile_phto']['size'] < 1048576) 
                {
                    $w1filename_sm11 = $_FILES['profile_phto']['name'];
                    $w1ext_sm11 = \File::extension($w1filename_sm11);

                    $w1time_sm11 = 'w1'.$imgid.'.'.$w1ext_sm11;

                    $w1Blogimge11="cart/product/web/product/".$w1time_sm11;
                    //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];

                    $w1add11="cart/product/web/product/".$w1time_sm11; // the path with the file name where the file will be stored, upload is the directory name. 

                    if(move_uploaded_file ($_FILES['profile_phto']['tmp_name'],$w1add11)) 
                    {
                        $images[] = $w1Blogimge11;
                        DB::table('tbl_shops')->where('id', $pstid)->update(['profile_phto' => $w1Blogimge11]);
                        if(File::exists($preprofilepic)) 
                        {
                            File::delete($preprofilepic);
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
    
                        $w1time_sm = 'w2'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery1']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery1' => $w1Blogimge]);
    
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
    
                        $w1time_sm =  'w3'.$pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery2']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery2' => $w1Blogimge]);
    
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
    
                        $w1time_sm =  'w4'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery3']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery3' => $w1Blogimge]);
    
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
    
                        $w1time_sm =  'w5'. $pstid.'.'.$w1ext_sm;
    
                        $w1Blogimge="cart/product/web/product/".$w1time_sm;
                        //$Blogthgumimge="program/thumb/".$time.$_FILES['image1']['name'];
    
                        $w1add="cart/product/web/product/".$w1time_sm; // the path with the file name where the file will be stored, upload is the directory name. 
    
                        if(move_uploaded_file ($_FILES['gallery4']['tmp_name'],$w1add)) 
                        {
                            $images[] = $w1Blogimge;
                            DB::table('tbl_shops')->where('id', $pstid)->update(['gallery4' => $w1Blogimge]);
    
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
    public function contactremove($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
              
               
               
              $data = DB::table('tbl_shops')->where('id', '=', $id)->get();
                $webimage = '';
                $webimage1 = '';
               

                foreach($data as $object)
                {
                    $webimage = $object->logo;
                    $webimage1 = $object->profile_phto;
                   
                }

                if($webimage != '')
                {
                    File::delete($webimage);
                }
                if($webimage1 != '')
                {
                    File::delete($webimage1);
                    }
                
                
                DB::table('tbl_shops')->where('id', $id)->delete();

              
                // Remove from user  table
                
                DB::table('users')->where('regid', $id)->delete();


                return redirect('/users')->withErrors(['Contents Deleted Successfully!!']);
            }
             
           }
          /////// salespartners///////
    public function salespartner()
    {
        if (!Auth::check()) { return redirect('/'); } else {
            $wl_id = Auth::user()->id;
            $data['data'] = DB::table('tbl_salespartners')->where(['associateid'=> $wl_id])->orderBy('id', 'desc')->get();
           
            //$data['data'] = DB::table('tbl_cart_products')->where(['subscription' => '0'])->orderBy('id', 'desc')->paginate(20);
                 
            if(count($data) > 0)
            {
                return view('delivery_agent.salespartners', $data);
            }
            else
            {
                return view('delivery_agent.salespartners');
            }
        }
    }
     public function addsalespartner()
    {
            $data['cat'] = DB::table('tbl_categories')->orderBy('id', 'asc')->get();
            $data['category'] = DB::table('tbl_categories')->orderBy('id', 'desc')->get();
            $data['subcategory'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();
            $data['location'] = DB::table('tbl_locations')->orderBy('id', 'asc')->get();
            
            if(count($data) > 0)
            {
                return view('delivery_agent.add_salespartners', $data);
            }
            else
            {
                return view('delivery_agent.add_salespartners');
            }
        }

   
    public function postsalespartner(Request $req)
    {
       
       
        $email = $req->input('email');
        $password = $req->input('password');

        // $req['password'] = bcrypt($password);
        // $req['password1'] = $password; 
        
        tbl_salespartner::create($req->all());
         $pstid = DB::getPdo()->lastInsertId();

       // DB::table('tbl_salespartners')->where('id', $pstid)->update(['password' =>$req['password']]);
        
        $data = array(
            'email'=>$email,
            'password'=>$password,
            );
        if(count($data) > 0)
            {
                return view('delivery_agent.edit_salespartner', $data);
            }
            else
            {
                return view('delivery_agent.edit_salespartner');
            }
        ///Mail::to($email)->bcc(['vipin@extrememedia.in'])->send(new Company($data));
        

        return redirect()->back()->withErrors(['Sales partner Added Successfully!!']);
    }
    public function Salespartnervalidation($request)
    {
        return $this->Validate($request, [
            'name' => 'required',
            'contact' => 'required',
            'email' => 'required|unique:users|max:255',
            'password' => 'required',
            'marginamount'  => 'required',
           
        ]);
    }
  public function editsalespartner($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {

            $data['data'] = DB::table('tbl_salespartners')->where(['id' => $id])->get();
            
            if(count($data) > 0)
            {
                return view('delivery_agent.edit_salespartner', $data);
            }
            else
            {
                return view('delivery_agent.edit_salespartner');
            }
        }
    }

     public function updatesalespartner(Request $req)
    {
        
        $id = $req->input('id');
        
        $Update  = tbl_salespartner::where('id',$id)->first();
        if ($Update) {
           $speak = $Update->update($req->all());
        }
        
        return redirect()->back()->withErrors(['Updated Successfully!!']);
    
    } 
    public function removesalespartner($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {
            
               
                DB::table('tbl_salespartners')->where(['id' => $id])->delete();

                return redirect('/view-salespartner')->withErrors(['Deleted Successfully!!']);
             
        }
    }
    public function margins(Request $request)
    {
        $margin=0;
        $margin = DB::table('tbl_salespartners')->where(['id' => $request->id,])->value('margin');
        
       return $margin;
    }
    public function report()
    {
        $data['salespartner'] = DB::table('tbl_salespartners')->orderBy('id', 'asc')->get();
            
            if(count($data) > 0)
            {
                return view('delivery_agent.report', $data);
            }
            else
            {
                return view('delivery_agent.report');
            }
    }
    public function viewreport()
    {
            $data['cat'] = DB::table('tbl_categories')->orderBy('id', 'asc')->get();
            $data['category'] = DB::table('tbl_categories')->orderBy('id', 'desc')->get();
            $data['subcategory'] = DB::table('tbl_subcategories')->orderBy('id', 'asc')->get();
            $data['location'] = DB::table('tbl_locations')->orderBy('id', 'asc')->get();
            $data['salespartner'] = DB::table('tbl_salespartners')->orderBy('id', 'asc')->get();
            if(count($data) > 0)
            {
                return view('delivery_agent.viewreport', $data);
            }
            else
            {
                return view('delivery_agent.viewreport');
            }
    }
    public function postvalue(Request $req)
    {
        
        $id = $req->input('id');
        $soldamount  = $req->input('soldamount');
        $salpartid=$req->input('salespartnerid');
        // print($salpartid);
        // exit();
        DB::table('tbl_shops')->where('id', $id)->update(['soldamount' => $soldamount,'salespartnerid' => $salpartid]);
       
         return redirect('/users')->withErrors(['added Successfully!!']);
      
    
    } 
    public function viewsalesReport($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {

            $data['data'] = DB::table('tbl_shops')->where(['salespartnerid' => $id])->get();
             $data['salespartner'] = DB::table('tbl_salespartners')->where('id', $id)->get();
             $data['payment'] = DB::table('tbl_payments')->where(['salespartnerid' => $id])->get();
            
            if(count($data) > 0)
            {
                return view('delivery_agent.view_salesreport', $data);
            }
            else
            {
                return view('delivery_agent.view_salesreport');
            }
        }
    }
    
     public function paymentsalespartner($id,$k)
    {
        if (!Auth::check()) { return redirect('/'); } else {

            $data['data'] = DB::table('tbl_shops')->where(['salespartnerid' => $id])->get();
             $data['salespartner'] = DB::table('tbl_salespartners')->where('id', $id)->get();
          
            // print($k);
            // exit();
            if(count($data) > 0)
            {
                return view('delivery_agent.payment_salespartnr', $data);
            }
            else
            {
                return view('delivery_agent.payment_salespartnr');
            }
        }
    }
    public function postpayment(Request $req)
    {
        $date=date('Y-m-d');
        $time=date('H:i:s');
        $req['date']=$date;
        $req['time']=$time;
        $associateid = $req->input('associateid');
        $salespartnerid  = $req->input('salespartnerid');
        tbl_payment::create($req->all());
        // print($salpartid);
        // exit();
        return redirect('/view-salesReport/'.$salespartnerid)->withErrors(['Payment done Successfully!!']);
   
    } 
    
    public function viewpaymentReport($id)
    {
        if (!Auth::check()) { return redirect('/'); } else {

           

            $data['data'] = DB::table('tbl_shops')->where(['salespartnerid' => $id])->get();
             $data['salespartner'] = DB::table('tbl_salespartners')->where('id', $id)->get();
             $data['payment'] = DB::table('tbl_payments')->where(['salespartnerid' => $id])->get();
            
            if(count($data) > 0)
            {
                return view('delivery_agent.view_paymentreport', $data);
            }
            else
            {
                return view('delivery_agent.view_paymentreport');
            }
        }
    }
     
}
