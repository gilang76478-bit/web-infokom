<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;

class ContactMessageController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        ContactMessage::create($request->safe()->except('company_website'));

        return back()->with('contact_message_sent', true);
    }
}