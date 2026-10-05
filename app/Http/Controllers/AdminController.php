<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Affiche le tableau de bord administrateur avec la liste des utilisateurs.
     */
    public function dashboard(): View
    {
        $users = User::with('roles')
            ->latest()
            ->paginate(10);

        $totalUsers = User::count();
        $totalAdmins = User::role('admin')->count();
        $totalSupports = User::role('support')->count();

        return view('admin.dashboard', compact('users', 'totalUsers', 'totalAdmins', 'totalSupports'));
    }

    /**
     * Affiche le formulaire de création d'un utilisateur Support.
     */
    public function createSupport(): View
    {
        return view('admin.supports.create');
    }

    /**
     * Enregistre un nouvel utilisateur Support.
     */
    public function storeSupport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà associée à un compte.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assigner strictement le rôle support
        $user->assignRole('support');

        return redirect()->route('admin.dashboard')->with('success', "L'agent Support « {$user->name} » ({$user->email}) a été créé avec succès.");
    }
}
