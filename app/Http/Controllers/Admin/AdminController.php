<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Afficher le dashboard d'administration
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats
        ]);
    }

    /**
     * Afficher la liste des utilisateurs
     */
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users
        ]);
    }

    /**
     * Activer/désactiver un utilisateur
     */
    public function toggleUserStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Statut de l\'utilisateur mis à jour avec succès.');
    }

    /**
     * Changer le rôle d'un utilisateur
     */
    public function changeUserRole(User $user, Request $request)
    {
        $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user->role = $request->role;
        $user->save();

        return back()->with('success', 'Rôle de l\'utilisateur mis à jour avec succès.');
    }
}
