<?php

namespace App\Http\Controllers;

use App\Jobs\FetchUpdatedInformationForZakarpattia;
use Illuminate\Http\JsonResponse;
use Throwable;

class UpdatedInformationController extends Controller
{
    /**
     * Fetch updated information for all energy providers.
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function fetch(): JsonResponse
    {
        (new FetchUpdatedInformationForZakarpattia())->handle();

        return response()->json([
            'providers' => [
                'Zakarpattia',
            ],
            'success' => true,
            'message' => 'OK'
        ]);
    }
}
