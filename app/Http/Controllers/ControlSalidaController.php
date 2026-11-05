<?php

namespace App\Http\Controllers;

use App\Models\ControlSalida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ControlSalidaController extends Controller
{
    /**
     * Display a listing of control de salidas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ControlSalida::with(['usuario:id,nombre,correo,area']);

        // Optional filter by month/year (e.g., ?month=11&year=2026)
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('Fecha', $request->month)
                  ->whereYear('Fecha', $request->year);
        }

        // Optional filter by area or search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Lugar', 'like', "%{$search}%")
                  ->orWhere('Comentarios', 'like', "%{$search}%")
                  ->orWhereHas('usuario', function ($uq) use ($search) {
                      $uq->where('nombre', 'like', "%{$search}%")
                         ->orWhere('area', 'like', "%{$search}%");
                  });
            });
        }

        $salidas = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $salidas,
        ]);
    }

    /**
     * Store a newly created control de salida.
     * Always creates with Revisado = false (Pendiente por defecto).
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'Fecha' => 'required',
            'Lugar' => 'required|string|max:50',
            'FechaEntrada' => 'required',
            'FechaSalida' => 'required',
            'Comentarios' => 'nullable|string|max:256',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Standardize datetime formatting
        $fecha = date('Y-m-d', strtotime($request->Fecha));
        $fechaEntrada = date('Y-m-d H:i:s', strtotime($request->FechaEntrada));
        $fechaSalida = date('Y-m-d H:i:s', strtotime($request->FechaSalida));

        $salida = ControlSalida::create([
            'idUser' => $user->id,
            'Fecha' => $fecha,
            'Lugar' => $request->Lugar,
            'FechaEntrada' => $fechaEntrada,
            'FechaSalida' => $fechaSalida,
            'Revisado' => false, // Por defecto siempre PENDIENTE (0)
            'Comentarios' => $request->input('Comentarios', ''),
        ]);

        $salida->load(['usuario:id,nombre,correo,area']);

        return response()->json([
            'success' => true,
            'message' => 'Registro de salida guardado exitosamente',
            'data' => $salida,
        ], 201);
    }

    /**
     * Toggle or update the "Revisado" status (Visto).
     * ONLY administrator can perform this action.
     */
    public function toggleRevisado(int $id): JsonResponse
    {
        $user = auth('api')->user();

        if (! $user->isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el administrador tiene autorización para marcar el visto.',
            ], 403);
        }

        $salida = ControlSalida::findOrFail($id);
        $salida->Revisado = ! $salida->Revisado;
        $salida->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado de revisión actualizado por el administrador',
            'data' => $salida,
        ]);
    }

    /**
     * Remove the specified salida.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = auth('api')->user();
        $salida = ControlSalida::findOrFail($id);

        if (! $user->isAdmin && $salida->idUser !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este registro.',
            ], 403);
        }

        $salida->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado exitosamente',
        ]);
    }
}
