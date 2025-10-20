<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Admin\Banner;
use App\Models\Admin\BannerAds;
use App\Models\Admin\BannerText;
use App\Models\Admin\Promotion;
use App\Models\Contact;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use HttpResponses;

    public function home()
    {
        $user = Auth::user();
        // Note: agent_id is actually owner_id (Owner->Player relationship only)
        // In 3-role system: Owner -> Player (no multi-level hierarchy)

        // Fetch all the required data
        // Contacts are specific to owner, others are shared across all owners
        $contacts = Contact::where('agent_id', $user->agent_id)->get();
        $banners = Banner::all(); // Shared resource
        $bannerTexts = BannerText::all(); // Shared resource
        $adsBanners = BannerAds::all(); // Shared resource
        $promotions = Promotion::all(); // Shared resource

        // Return the data in a structured response
        return $this->success([
            'contacts' => ContactResource::collection($contacts),
            'banners' => $banners,
            'banner_texts' => $bannerTexts,
            'ads_banners' => $adsBanners,
            'promotions' => $promotions,
        ], 'Home data retrieved successfully.');
    }
}
