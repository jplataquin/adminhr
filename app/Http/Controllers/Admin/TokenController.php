<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    /**
     * Display a listing of the tokens.
     */
    public function index()
    {
        // Get all tokens with their associated tokenable (user)
        $tokens = PersonalAccessToken::with('tokenable')->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.tokens.index', compact('tokens'));
    }

    /**
     * Show the form for creating a new token.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('admin.tokens.create', compact('users'));
    }

    /**
     * Store a newly created token in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        // Generate the token
        $token = $user->createToken($request->name);

        return redirect()->route('admin.tokens.index')->with([
            'success' => 'Token generated successfully!',
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
        $token->delete();

        return redirect()->route('admin.tokens.index')->with('success', 'Token revoked successfully.');
    }
}
