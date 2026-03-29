@extends('layouts.app')

@section('title', 'Detalles del Usuario')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Detalles del Usuario</h4>
                    <div>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $user->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Apellidos:</strong></td>
                                    <td>{{ $user->apellidos }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Rol:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $user->isAdmin() ? 'danger' : ($user->isUsuario() ? 'primary' : 'secondary') }}">
                                            {{ $user->getRoleLabel() }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Identificador:</strong></td>
                                    <td>{{ $user->identificador }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>{{ $user->telefono ?? 'No registrado' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>ID Persona:</strong></td>
                                    <td>{{ $user->id_persona }}</td>
                                </tr>
                                <tr>
                                    <td><strong>ID Rol:</strong></td>
                                    <td>{{ $user->id_rol }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
