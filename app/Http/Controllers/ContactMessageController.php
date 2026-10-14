<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactMessageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'whatsapp' => ['required', 'string', 'regex:/^[0-9+()\-\s]{9,30}$/'],
            'subject' => ['required', Rule::in(config('school.contact.inquiry_types'))],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'company_website' => ['prohibited'],
        ]);
        unset($data['company_website']);

        ContactMessage::create($data);

        return back()->with('contact_message_sent', true);
    }
}