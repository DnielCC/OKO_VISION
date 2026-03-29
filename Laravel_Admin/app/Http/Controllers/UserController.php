<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
                ->select('usuarios.*', 'personas.nombre', 'personas.apellidos', 'personas.mail as email', 'personas.telefono', 'personas.foto');
            
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
            'nombre' => 'required|string|max:100|min:2',
            'apellidos' => 'required|string|max:100|min:2',
            'email' => 'required|string|email|max:150',
            'telefono' => 'nullable|string|max:10',
            'sexo' => 'nullable|in:H,M',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_rol' => 'required|integer|in:1,2,3',
            'identificador' => 'required|string|min:3|max:15',
            'password' => 'required|string|min:8|max:64|regex:/[A-Za-z]/|regex:/[0-9]/',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Por favor, introduce un correo electrónico válido.',
            'telefono.max' => 'El teléfono no debe exceder los 10 dígitos.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'identificador.required' => 'La matrícula/ID es obligatoria.',
            'identificador.min' => 'La matrícula debe tener al menos 3 caracteres.',
            'identificador.max' => 'La matrícula no debe exceder los 15 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede exceder los 64 caracteres.',
            'password.regex' => 'La contraseña debe incluir al menos una letra y un número.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif.',
            'foto.max' => 'La imagen no debe pesar más de 2MB.',
        ]);

        try {
            // Manejar la foto en Base64 para la API
            $fotoBase64 = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $fotoBase64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }

            // Primero crear la persona en la API
            $personData = [
                'nombre' => $validated['nombre'],
                'apellidos' => $validated['apellidos'],
                'mail' => $validated['email'],
                'telefono' => $validated['telefono'],
                'sexo' => $validated['sexo'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            ];
            
            if ($fotoBase64) {
                $personData['foto'] = $fotoBase64;
            }

            $persona = $this->apiService->createPerson($personData);
            
            // Luego crear el usuario en la API con el id de la persona
            $userData = [
                'id_persona' => $persona->id,
                'id_rol' => (int)$validated['id_rol'],
                'identificador' => $validated['identificador'],
                'password' => $validated['password'],
            ];

            $this->apiService->createUser($userData);
            
            return redirect()->route('users.index')
                ->with('success', "Usuario creado exitosamente.");
        } catch (\Exception $e) {
            Log::error("Error creando usuario: " . $e->getMessage());
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
            'nombre' => 'required|string|max:100|min:2',
            'apellidos' => 'required|string|max:100|min:2',
            'email' => 'required|email|max:150',
            'telefono' => 'nullable|string|max:10',
            'sexo' => 'nullable|in:H,M',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'identificador' => 'required|string|max:15|min:3',
            'id_rol' => 'required|integer|in:1,2,3',
            'password' => 'nullable|string|min:8|max:64|regex:/[A-Za-z]/|regex:/[0-9]/',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Por favor, introduce un correo electrónico válido.',
            'telefono.max' => 'El teléfono no debe exceder los 10 dígitos.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'identificador.required' => 'La matrícula/ID es obligatoria.',
            'identificador.min' => 'La matrícula debe tener al menos 3 caracteres.',
            'identificador.max' => 'La matrícula no debe exceder los 15 caracteres.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La nueva contraseña no puede exceder los 64 caracteres.',
            'password.regex' => 'La contraseña debe incluir al menos una letra y un número.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif.',
            'foto.max' => 'La imagen no debe pesar más de 2MB.',
        ]);

        try {
            // 1. Asegurar el ID de la persona (Prioridad: Buscar por email si no es válido)
            $personaId = $user->id_persona;
            if (!$personaId || $personaId <= 0) {
                $people = $this->apiService->getPeople();
                $existing = collect($people)->firstWhere('mail', $validated['email']);
                if ($existing) {
                    $personaId = $existing->id;
                }
            }

            // 2. Manejar la foto
            $fotoBase64 = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $fotoBase64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }

            // 3. Actualizar Persona (API)
            $personData = [
                'nombre' => $validated['nombre'],
                'apellidos' => $validated['apellidos'],
                'mail' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
                'sexo' => $validated['sexo'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            ];
            if ($fotoBase64) $personData['foto'] = $fotoBase64;

            if ($personaId > 0) {
                $this->apiService->patchPerson($personaId, $personData);
            } else {
                $newPerson = $this->apiService->createPerson($personData);
                $personaId = $newPerson->id;
            }

            // 4. Actualizar Usuario (API y Localmente a través del trait)
            $userData = [
                'identificador' => $validated['identificador'],
                'id_rol' => (int)$validated['id_rol'],
                'id_persona' => $personaId
            ];
            
            if (!empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            // El trait ApiUserTrait ya llama a la API en el método update()
            $user->update($userData);

            return redirect()->route('users.index')->with('success', '¡Perfil actualizado con éxito!');
        } catch (\Exception $e) {
            Log::error("Error actualizando usuario: " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
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
