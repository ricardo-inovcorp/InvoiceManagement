<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NifService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class NifController extends Controller
{
    protected $nifService;

    public function __construct(NifService $nifService)
    {
        $this->nifService = $nifService;
    }

    /**
     * Consulta informações de uma empresa pelo NIF
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function lookup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nif' => ['required', 'string', 'regex:/^[0-9]{9}$/'],
        ], [
            'nif.regex' => 'O NIF deve conter exatamente 9 dígitos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $validator->errors()
            ], 422);
        }

        $nif = $request->input('nif');
        $result = $this->nifService->getCompanyByNif($nif);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }
} 