<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use Illuminate\Http\Request;

class GameTypeController extends Controller
{
    public function index()
    {
        $gameTypes = GameType::orderBy('order', 'asc')->paginate(20);
        
        return view('admin.game_types.index', compact('gameTypes'));
    }

    public function toggleStatus($id)
    {
        $gameType = GameType::findOrFail($id);
        $gameType->status = $gameType->status == 1 ? 0 : 1;
        $gameType->save();

        return redirect()->route('admin.game-types.index')->with('success', 'Game type status updated successfully.');
    }
}

