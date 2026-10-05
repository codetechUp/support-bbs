<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportController extends Controller
{
    /**
     * Affiche l'espace agent support.
     */
    public function dashboard(): View
    {
        $user = Auth::user();

        return view('support.dashboard', compact('user'));
    }
}
