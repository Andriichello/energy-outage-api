<?php

namespace App\Observers;

use App\Models\UpdatedInformation;
use Illuminate\Support\Facades\Log;

class UpdatedInformationObserver
{
    /**
     * Handle the UpdatedInformation "created" event.
     */
    public function created(UpdatedInformation $info): void
    {
        if ($this->wasChanged($info)) {
            // Log that information has been changed
            Log::info('Information changed for provider', [
                'provider' => $info->provider,
                'fetched_at' => $info->fetched_at,
                'content_hash' => $info->content_hash,
            ]);

            // notify users that are subscribed to this provider
        }
    }

    /**
     * Returns true if the information has changed since the last update.
     *
     * @param UpdatedInformation $info
     *
     * @return bool
     */
    protected function wasChanged(UpdatedInformation $info): bool
    {
        /** @var UpdatedInformation|null $existing */
        $existing = UpdatedInformation::query()
            ->where('provider', $info->provider)
            ->where('fetched_at', '<', $info->fetched_at)
            ->orderByDesc('fetched_at')
            ->first();

        return empty($existing) || $info->content_hash !== $existing->content_hash;
    }
}
