<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JeroenDesloovere\VCard\VCard;
use DB;

class VisitController extends Controller
{
    //
    public function savecard($id)
    {
       
        $contact = DB::table('tbl_shops')->where(['id' => $id])->get();
        foreach ($contact as $contacts) {
            if($contacts->id != '')
            {
               $firstname          = $contacts->first_name;
               $lastname           = $contacts->last_name;
               $designation        = $contacts->designation;
               $email              = $contacts->personal_mail;
               $primary_contact        = $contacts->primary_contact;
               $secondary_contact  = $contacts->secondary_contact;
               $address1           = $contacts->address_line1;
               $address2           = $contacts->address_line2;
               $website            = $contacts->website;
               $org_name            = $contacts->org_name;
            }
        }
        // define vcard
            $vcard = new VCard();

            // define variables
            
            $additional = '';
            $prefix = '';
            $suffix = '';

            // add personal data
            $vcard->addName($lastname, $firstname, $additional, $prefix, $suffix);

            // add work data
            $vcard->addCompany($org_name);
            $vcard->addJobtitle($designation);
            $vcard->addEmail($email );
            $vcard->addPhoneNumber($primary_contact, 'PREF;WORK');
            $vcard->addPhoneNumber($secondary_contact, 'WORK');
            $vcard->addAddress(null, null, $address1, null, null, null, null);
            $vcard->addURL($website);


            // return vcard as a string
            //return $vcard->getOutput();

            // return vcard as a download
            return $vcard->download();

            // save vcard on disk
            //$vcard->setSavePath('/path/to/directory');
            //$vcard->save();
                }
}
