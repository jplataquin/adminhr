<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class IdGeneratorController extends Controller
{
    public function index()
    {
        $positions = Position::orderBy('name')->get();

        return view('admin.id-generator', [
            'positions' => $positions,
        ]);
    }
}
