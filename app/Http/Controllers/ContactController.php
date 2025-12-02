<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\KontaktMail;
class ContactController extends Controller
{
    public function wyslij(Request $request)
    {
        $dane = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'company' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        Mail::to('kacper.fluda5@gmail.com')
            ->send(new KontaktMail($dane));
        return back()->with('success', 'Wiadomość została wysłana');
    }
}
