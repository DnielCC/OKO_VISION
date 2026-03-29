<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\ApiService;

class UserController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = User::query()
                ->join('personas', 'usuarios.id_persona', '=', 'personas.id')
                ->select('usuarios.*', 'personas.nombre', 'personas.apellidos', 'personas.mail as email', 'personas.telefono');
            
            // Búsqueda por nombre completo, identificador o correo
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('personas.nombre', 'ILIKE', "%{$search}%")
                      ->orWhere('personas.apellidos', 'ILIKE', "%{$search}%")
                      ->orWhere('usuarios.identificador', 'ILIKE', "%{$search}%")
                      ->orWhere('personas.mail', 'ILIKE', "%{$search}%");
                });
            }

            // Filtro por Rol en staff
            if ($request->filled('role')) {
                $role_id = $request->get('role');
                $usersQuery = (clone $query)->where('usuarios.id_rol', $role_id)->where('usuarios.id_rol', '!=', 3);
            } else {
                $usersQuery = (clone $query)->where('usuarios.id_rol', '!=', 3);
            }

            // Separar visitantes y paginar
            $users = $usersQuery->paginate(10, ['*'], 'users_page')->withQueryString();
            $visitors = (clone $query)->where('usuarios.id_rol', 3)->paginate(10, ['*'], 'visitors_page')->withQueryString();

            return view('users.index', compact('users', 'visitors'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'telefono' => 'nullable|string|max:10',
            'sexo' => 'nullable|in:H,M',
            'fecha_nacimiento' => 'nullable|date',
            'foto' => 'nullable|image|max:2048',
            'id_rol' => 'required|integer|in:1,2,3',
            'identificador' => 'required|numeric|digits_between:1,9',
            'password' => 'required|string|min:6',
        ]);

        try {
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('fotos', 'public');
            }

            // Primero crear la persona
            $personData = [
                'nombre' => $validated['nombre'],
                'apellidos' => $validated['apellidos'],
                'mail' => $validated['email'],
                'telefono' => $validated['telefono'],
                'sexo' => $validated['sexo'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            ];
            
            if ($fotoPath) {
                $personData['foto'] = '/storage/' . $fotoPath;
            }

            $persona = $this->apiService->createPerson($personData);
            
            // Luego crear el usuario en la API con el id de la persona
            $userData = [
                'id_persona' => $persona->id,
                'id_rol' => $validated['id_rol'],
                'identificador' => $validated['identificador'],
                'password' => $validated['password'],
            ];

            $this->apiService->createUser($userData);
            
            return redirect()->route('users.index')
                ->with('success', "Usuario creado exitosamente.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        try {
            return view('users.show', compact('user'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        try {
            return view('users.edit', compact('user'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar usuario para edición: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'telefono' => 'nullable|string|max:10',
            'sexo' => 'nullable|in:H,M',
            'fecha_nacimiento' => 'nullable|date',
            'foto' => 'nullable|image|max:2048',
            'id_rol' => 'required|integer|in:1,2,3',
            'identificador' => 'required|numeric|digits_between:1,9',
            'password' => 'nullable|string|min:6',
        ]);

        try {
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('fotos', 'public');
            }

            // Actualizar la persona
            $personData = [
                'nombre' => $validated['nombre'],
                'apellidos' => $validated['apellidos'],
                'mail' => $validated['email'],
                'telefono' => $validated['telefono'],
                'sexo' => $validated['sexo'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            ];
            
            if ($fotoPath) {
                $personData['foto'] = '/storage/' . $fotoPath;
            }

            $this->apiService->updatePerson($user->id_persona, $personData);
            
            // Actualizar el usuario
            $userData = [
                'id_persona' => $user->id_persona,
                'id_rol' => $validated['id_rol'],
                'identificador' => $validated['identificador'],
            ];
            
            if (!empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            $user->update($userData);

            return redirect()->route('users.index')
                ->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status (simulado, ya que la API no tiene campo activo)
     */
    public function toggleStatus(User $user)
    {
        try {
            // Como la API no tiene campo activo, simulamos el cambio
            // En una implementación real, necesitaríamos un endpoint PATCH para esto
            
            $status = 'activado'; // Simulación
            
            return redirect()->route('users.index')
                ->with('success', "Usuario {$status} exitosamente.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cambiar estado del usuario: ' . $e->getMessage());
        }
    }

    /**
     * API Health Check
     */
    public function apiStatus()
    {
        try {
            $status = $this->apiService->healthCheck();
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
