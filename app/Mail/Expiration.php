<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Expiration extends Mailable
{
    use Queueable, SerializesModels;

    protected $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $template)
    {
        $this->subject = $subject;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('mail.expiration', [
                        'content' => $this->template
                    ]);
    }
}
