<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KontaktMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dane;

    public function __construct($dane)
    {
        $this->dane = $dane;
    }

    public function build()
    {
        return $this->subject('Nowa wiadomość z formularza kontatkowego')
            ->view('emails.kontakt');

    }
}
