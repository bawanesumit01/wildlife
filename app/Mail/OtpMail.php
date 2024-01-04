<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $context;
    protected $user;

    

    /**
     * Create a new message instance.
     *
     * @param  string  $otp
     */
    public function __construct($otp, $context , $user)
    {
        $this->otp = $otp;
        $this->context = $context;
        $this->user = $user;
    }


    /**
     * Get the message content definition.
     */
    public function build()
    {
        $view = 'emails.otp'; // Default view for OTP
        $subject = 'Verify your email and complete your registration!'; // Default subject

        // Check the context (registration or forgot password)
        if ($this->context === 'forgot-password') {
            $view = 'emails.forgotpassword';
            $subject = 'Reset your password';
        }

        return $this->view($view)
            ->with([
                'otp' => $this->otp,
                'user' => $this->user,
            ])
            ->subject($subject);
    }
}
