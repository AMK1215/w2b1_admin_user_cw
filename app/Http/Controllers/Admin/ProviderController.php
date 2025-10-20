<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Product::paginate(20);
        
        return view('admin.providers.index', compact('providers'));
    }

    public function create()
    {
        return view('admin.providers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products',
            'status' => 'required|boolean',
        ]);

        Product::create($request->all());

        return redirect()->route('admin.providers.index')->with('success', 'Provider created successfully!');
    }

    public function edit(Product $provider)
    {
        return view('admin.providers.edit', compact('provider'));
    }

    public function update(Request $request, Product $provider)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code,' . $provider->id,
            'status' => 'required|boolean',
        ]);

        $provider->update($request->all());

        return redirect()->route('admin.providers.index')->with('success', 'Provider updated successfully!');
    }

    public function destroy(Product $provider)
    {
        $provider->delete();

        return redirect()->route('admin.providers.index')->with('success', 'Provider deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $provider = Product::findOrFail($id);
        $provider->status = $provider->status == 'ACTIVATED' ? 'DEACTIVATED' : 'ACTIVATED';
        $provider->save();

        return redirect()->route('admin.providers.index')->with('success', 'Provider status updated successfully.');
    }
}

