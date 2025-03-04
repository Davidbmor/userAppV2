@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Gestión de Usuarios</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (Auth::user()->role === 'superadmin' || Auth::user()->role === 'admin')
        <a href="{{ route('users.create') }}" class="btn btn-success mb-3">Crear Nuevo Usuario</a>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Verificado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>{{ $user->email_verified_at ? 'Sí' : 'No' }}</td>
                    <td>
                        @if (Auth::user()->role === 'superadmin' || (Auth::user()->role === 'admin' && $user->role !== 'superadmin'))
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Editar</a>
                            @if (Auth::user()->role === 'superadmin')
                                <form method="POST" action="{{ route('users.verify', $user->id) }}" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-warning">Cambiar Verificación</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection