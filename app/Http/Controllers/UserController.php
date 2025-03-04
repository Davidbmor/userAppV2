<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    public function index()
    {
    // Inicializar $users
    $users = collect();  // Colección vacía por defecto

    if (Auth::user()->role === 'superadmin') {
        $users = User::all();
    } else if (Auth::user()->role === 'admin') {
        $users = User::where('role', '!=', 'superadmin')->get();
    } else {
        // Para usuarios normales, mostrar solo su propio usuario
        $users = User::where('id', Auth::id())->get();
    }

    return view('users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin,superadmin',
            'password' => 'nullable|min:8|confirmed'
        ]);
    
        // Si el correo electrónico ha cambiado, restablecer el estado de verificación
        if ($user->email !== $data['email']) {
            $data['email_verified_at'] = null;
        }
    
        // Permitir que el superadmin actualice sus propios valores
        if (Auth::user()->id === $user->id && Auth::user()->role === 'superadmin') {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
        } else {
            // Evitar cambiar el rol si el usuario objetivo es superadmin o si un admin se está editando a sí mismo
            if (($user->role === 'superadmin' && Auth::user()->id !== $user->id) || (Auth::user()->role === 'admin' && Auth::user()->id === $user->id)) {
                unset($data['role']);
            }
    
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
        }
    
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
    }
    

    public function destroy(User $user)
    {
        // Verificar si es superadmin
        if($user->id === 1 || $user->role === 'superadmin') {
            return back()->with('error', 'No se puede eliminar a un superadministrador del sistema');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado');
    }

    public function edit(User $user)
    {
        // Verificar si es superadmin por rol o por ID
        if ($user->role === 'superadmin' && Auth::user()->role !== 'superadmin' && Auth::user()->id !== $user->id) {
            return back()->with('error', 'No tienes permisos para editar al superadministrador');
        }
    
        return view('users.edit', compact('user'));
    }

    

    public function create()
    {
        // Permitir que tanto superadmin como admin creen usuarios
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            return back()->with('error', 'No tienes permisos para crear usuarios');
        }
    
        return view('users.create');
    }
    
        public function store(Request $request)
    {
        // Permitir que tanto superadmin como admin creen usuarios
        if (Auth::user()->role !== 'superadmin' && Auth::user()->role !== 'admin') {
            return back()->with('error', 'No tienes permisos para crear usuarios');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:user,admin',
        ]);

        // Hash the password
        $data['password'] = Hash::make($data['password']);

        // Set email_verified_at if superadmin
        if (Auth::user()->role === 'superadmin' && $request->has('email_verified') && $request->email_verified) {
            $data['email_verified_at'] = now();
        } else {
            $data['email_verified_at'] = null;
        }

        User::create($data);
        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente');
    }
    public function verify(User $user)
    {
        // Solo el superadmin puede cambiar el estado de verificación
        if (Auth::user()->role !== 'superadmin') {
            return back()->with('error', 'No tienes permisos para cambiar el estado de verificación');
        }

        // Cambiar el estado de verificación
        $user->email_verified_at = $user->email_verified_at ? null : now();
        $user->save();

        return back()->with('success', 'Estado de verificación cambiado correctamente');
    }
}