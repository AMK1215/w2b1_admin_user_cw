<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    use HttpResponses;

    public function get()
    {
        $player = Auth::user();
        
        // Handle both authenticated and guest users
        if ($player && $player->agent_id) {
            // Authenticated player - get their owner's contact info
            $contact = Contact::where('agent_id', $player->agent_id)->get();
        } else {
            // Guest user - return all contacts or default contact
            // You can modify this to return specific contacts for guests
            $contact = Contact::all(); // or Contact::where('agent_id', 1)->get() for system default
        }

        return $this->success(ContactResource::collection($contact));
    }
}
