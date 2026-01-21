<?php

namespace App\Http\Controllers;

use App\Jobs\FetchUpdatedInformationForZakarpattia;
use App\Models\UpdatedInformation;
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
        $latest = (new FetchUpdatedInformationForZakarpattia())->handle();
        // Remove duplicates from the database
        $this->cleanup($latest);

        return response()->json([
            'providers' => [
                'Zakarpattia',
            ],
            'success' => true,
            'message' => 'OK'
        ]);
    }

    protected function cleanup(UpdatedInformation $latest): void
    {
        $duplicates = UpdatedInformation::query()
            ->where('provider', $latest->provider)
            ->where('content_hash', $latest->content_hash)
            ->count();

        if ($duplicates > 5) {
            $oldest = UpdatedInformation::query()
                ->where('provider', $latest->provider)
                ->where('content_hash', $latest->content_hash)
                ->orderBy('fetched_at')
                ->first('id');

            UpdatedInformation::query()
                ->where('content_hash', $latest->content_hash)
                ->whereNotIn('id', [$oldest->id, $latest->id])
                ->delete();
        }
    }
}
