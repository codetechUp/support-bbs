<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('support')) {
                return redirect()->route('support.dashboard');
            }
        }

        return view('auth.login');
    }

    /**
     * Traite la tentative de connexion de l'utilisateur.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Veuillez saisir votre adresse email.',
            'email.email' => 'L\'adresse email saisie est invalide.',
            'password.required' => 'Veuillez saisir votre mot de passe.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->hasRole('admin')) {
                return redirect()->intended(route('admin.dashboard'));
            }

            if ($user->hasRole('support')) {
                return redirect()->intended(route('support.dashboard'));
            }

            Auth::logout();
            return back()->withErrors([
                'email' => 'Accès refusé : aucun rôle attribué à ce compte.',
            ]);
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent à aucun compte enregistré.',
        ])->onlyInput('email');
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Vous avez été déconnecté.');
    }
}
