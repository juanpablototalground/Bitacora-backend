<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProyectoController extends Controller
{
    /**
     * Comprobar permisos de acceso: Solo administradores o usuarios de 'Sistemas / TI'
     */
    private function tienePermiso(): bool
    {
        $user = auth('api')->user();
        if (! $user) {
            return false;
        }

        if ($user->isAdmin) {
            return true;
        }

        $area = mb_strtolower(trim($user->area ?? ''));
        return str_contains($area, 'sistema') || str_contains($area, 'ti') || str_contains($area, 'it');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        if (! $this->tienePermiso()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Este módulo es exclusivo para administradores y personal de Sistemas / TI.',
            ], 403);
        }

        $query = Proyecto::query();

        if ($request->filled('estado')) {
            $query->where('estadoProyecto', $request->estado);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Nombreproyecto', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhere('enlaceRepositorio', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%");
            });
        }

        $proyectos = $query->orderBy('idProyectos', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $proyectos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if (! $this->tienePermiso()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. No tiene permisos para registrar proyectos.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'Nombreproyecto' => 'required|string|max:100',
            'descripcion' => 'required|string|max:100',
            'estadoProyecto' => 'required|string|in:Pendiente,Completado',
            'enlaceRepositorio' => 'required|string|max:100',
            'observaciones' => 'nullable|string|max:256',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        if (empty($data['observaciones'])) {
            $data['observaciones'] = 'Sin observaciones';
        }

        $proyecto = Proyecto::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Proyecto creado exitosamente.',
            'data' => $proyecto,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string|int $id): JsonResponse
    {
        if (! $this->tienePermiso()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado.',
            ], 403);
        }

        $proyecto = Proyecto::find($id);

        if (! $proyecto) {
            return response()->json([
                'success' => false,
                'message' => 'Proyecto no encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $proyecto,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        if (! $this->tienePermiso()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado.',
            ], 403);
        }

        $proyecto = Proyecto::find($id);

        if (! $proyecto) {
            return response()->json([
                'success' => false,
                'message' => 'Proyecto no encontrado.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'Nombreproyecto' => 'sometimes|required|string|max:100',
            'descripcion' => 'sometimes|required|string|max:100',
            'estadoProyecto' => 'sometimes|required|string|in:Pendiente,Completado',
            'enlaceRepositorio' => 'sometimes|required|string|max:100',
            'observaciones' => 'nullable|string|max:256',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        if (array_key_exists('observaciones', $data) && empty($data['observaciones'])) {
            $data['observaciones'] = 'Sin observaciones';
        }

        $proyecto->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Proyecto actualizado exitosamente.',
            'data' => $proyecto,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string|int $id): JsonResponse
    {
        if (! $this->tienePermiso()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado.',
            ], 403);
        }

        $proyecto = Proyecto::find($id);

        if (! $proyecto) {
            return response()->json([
                'success' => false,
                'message' => 'Proyecto no encontrado.',
            ], 404);
        }

        $proyecto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto eliminado exitosamente.',
        ]);
    }
}
