<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameList;
use App\Models\GameType;
use App\Models\Product;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $gameTypes = GameType::where('status', 1)->get();
        $products = Product::where('status', 1)->get();

        $query = GameList::with(['product', 'gameType'])
            ->where('status', 1);

        if ($request->filled('game_type_id')) {
            $query->where('game_type_id', $request->game_type_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $games = $query->paginate(30);

        return view('admin.games.index', compact('games', 'gameTypes', 'products'));
    }
}

