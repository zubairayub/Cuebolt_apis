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

    public $otp; // Holds the OTP for the email

    /**
     * Create a new message instance.
     *
     * @param string $otp The OTP code to be sent via email.
     */
    public function __construct($otp)
    {
        $this->otp = $otp; // Assign the passed OTP to the public property
    }

    /**
     * Get the message envelope.
     * 
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your OTP Code' // Subject of the email
        );
    }

    /**
     * Get the message content definition.
     * 
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp', // This is the view that will render the email body
            with: ['otp' => $this->otp] // Pass the OTP variable to the view
        );
    }

    /**
     * Get the attachments for the message.
     * 
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
