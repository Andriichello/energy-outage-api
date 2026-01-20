<?php

namespace App\Jobs;

use App\Models\UpdatedInformation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class FetchUpdatedInformationForZakarpattia implements ShouldQueue
{
    use Queueable;

    /**
     * Provider to fetch updated information for.
     *
     * @var string
     */
    protected string $provider = 'Zakarpattia';

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Fetch HTML content of the page
            $response = Http::withHeaders($headers = [
                'Accept' => 'application/json, text/plain, */*',
                'Origin' => 'https://outage.zakarpat.energy',
                'Referer' => 'https://outage.zakarpat.energy/',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
            ])->get(
                $url = 'https://api-outage-zakarpat-energy.inneti.net/api/options',
                $query = ['option_key' => 'pw_gpv_nek_comand']
            );

            $members = array_filter(
                $response->json('hydra:member'),
                fn($member) => $member['option_key'] === 'pw_gpv_nek_comand'
            );

            $html = data_get($members, '0.data');

            $crawler = new Crawler($html);
            // Get all paragraphs as an array of strings
            $paragraphs = $crawler->filter('p')
                ->each(fn (Crawler $node) => $node->text());

            // Log the parsed energy outage info
            Log::info('Parsed energy outage info', [
                'provider' => $this->provider,
                'paragraphs' => $paragraphs,
            ]);

            // Join all paragraphs into a single string with double newlines
            $text = implode("\n\n", $paragraphs);

            // Make a new instance of UpdateInformation with all the parameters set
            $info = UpdatedInformation::make($this->provider, $url, $text);
            // Raise an exception if save fails
            $info->saveOrFail();
        } catch (Throwable $e) {
            Log::error('Failed to fetch energy outage updated information', [
                'provider' => $this->provider,
                'url' => $url,
                'query' => $query,
                'headers' => $headers,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
