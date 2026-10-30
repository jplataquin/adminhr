<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceAccount;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    /**
     * Display a listing of the tokens.
     */
    public function index()
    {
        // Get all tokens belonging to ServiceAccount instances
        $tokens = PersonalAccessToken::where('tokenable_type', ServiceAccount::class)
            ->with(['tokenable' => function ($query) {
                $query->withTrashed()->with('creator');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.tokens.index', compact('tokens'));
    }

    /**
     * Show the form for creating a new token.
     */
    public function create()
    {
        return view('admin.tokens.create');
    }

    /**
     * Store a newly created token in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Create independent service account
        $serviceAccount = ServiceAccount::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        // Generate Sanctum token bound to the service account
        $token = $serviceAccount->createToken($request->name);

        return redirect()->route('admin.tokens.index')->with([
            'success' => "Service account '{$serviceAccount->name}' created and token generated successfully!",
            'plainTextToken' => $token->plainTextToken,
            'tokenName' => $request->name,
        ]);
    }

    /**
     * Remove the specified token from storage.
     */
    public function destroy($id)
    {
        $token = PersonalAccessToken::findOrFail($id);

        if ($token->tokenable instanceof ServiceAccount) {
            $token->tokenable->delete();
        }

        $token->delete();

        return redirect()->route('admin.tokens.index')->with('success', 'Service account token revoked successfully.');
    }
}
