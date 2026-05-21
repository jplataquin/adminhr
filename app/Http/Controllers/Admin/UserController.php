<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'is_admin' => ['boolean'],
        ]);

        $password = Str::password(12);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()->back()->with('status', 'User created successfully. Temporary Password: ' . $password);
    }

    public function resetPassword(User $user)
    {
        $password = Str::password(12);

        $user->update([
            'password' => Hash::make($password),
        ]);

        return redirect()->back()->with('status', 'Password reset successfully for ' . $user->name . '. New Password: ' . $password);
    }
}
