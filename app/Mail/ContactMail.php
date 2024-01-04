<?php
  
namespace App\Mail;
  
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
  
class ContactMail extends Mailable
{
    use Queueable, SerializesModels;
   
    public $data;
  
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
  
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       

                    
        $from_name = "Ex Connect";   
        $from_email = "uaepages1@gmail.com"; 
          
         return $this->from($from_email, $from_name)->subject('Digital Visiting Card Registration')->view('emails.qr_code')->with('data', $this->data);
    }
}