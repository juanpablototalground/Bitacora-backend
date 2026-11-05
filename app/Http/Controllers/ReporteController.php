<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReporteController extends Controller
{
    /**
     * Display a listing of reports.
     * Non-admins only see their own reports.
     * Admins can view all reports and filter by user/search.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        $query = Reporte::with(['usuario:id,nombre,correo,area,isAdmin']);

        // Si no es admin, solo puede ver sus propios registros
        if (! $user->isAdmin) {
            $query->where('idUser', $user->id);
        } else {
            // El admin puede filtrar por usuario específico si lo desea
            if ($request->filled('idUser')) {
                $query->where('idUser', $request->idUser);
            }
        }

        // Buscador
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('trabajo', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%")
                  ->orWhere('comentarios', 'like', "%{$search}%")
                  ->orWhereHas('usuario', function ($uq) use ($search) {
                      $uq->where('nombre', 'like', "%{$search}%")
                         ->orWhere('area', 'like', "%{$search}%");
                  });
            });
        }

        $reportes = $query->orderBy('idReportes', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $reportes,
            'isAdmin' => (bool) $user->isAdmin,
        ]);
    }

    /**
     * Store a newly created report.
     * Default revisado is false (0 - Pendiente).
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'trabajo' => 'required|string|max:250',
            'fechaInicio' => 'required',
            'fechaTermino' => 'required',
            'observaciones' => 'nullable|string|max:256',
            'comentarios' => 'nullable|string|max:256',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $fechaInicio = date('Y-m-d H:i:s', strtotime($request->fechaInicio));
        $fechaTermino = date('Y-m-d H:i:s', strtotime($request->fechaTermino));

        $reporte = Reporte::create([
            'idUser' => $user->id,
            'trabajo' => $request->trabajo,
            'fechaInicio' => $fechaInicio,
            'fechaTermino' => $fechaTermino,
            'revisado' => false, // Por defecto siempre PENDIENTE (0)
            'observaciones' => $request->input('observaciones', ''),
            'comentarios' => $request->input('comentarios', ''),
        ]);

        $reporte->load(['usuario:id,nombre,correo,area,isAdmin']);

        return response()->json([
            'success' => true,
            'message' => 'Reporte diario registrado exitosamente',
            'data' => $reporte,
        ], 201);
    }

    /**
     * Toggle or update the "revisado" status (Visto).
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

        $reporte = Reporte::findOrFail($id);
        $reporte->revisado = ! $reporte->revisado;
        $reporte->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado de visto actualizado por el administrador',
            'data' => $reporte,
        ]);
    }

    /**
     * Remove the specified report.
     * Users can delete their own; admins can delete any.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = auth('api')->user();
        $reporte = Reporte::findOrFail($id);

        if (! $user->isAdmin && $reporte->idUser !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este reporte.',
            ], 403);
        }

        $reporte->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reporte eliminado correctamente',
        ]);
    }
}
