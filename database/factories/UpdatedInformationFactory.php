<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UpdatedInformation>
 */
class UpdatedInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $content = fake()->paragraphs(fake()->numberBetween(1, 5), true);

        return [
            'provider' => fake()->randomElement(['zakarpattia', 'provider1', 'provider2']),
            'url' => fake()->url(),
            'content' => $content,
            'content_hash' => hash('sha256', $content),
            'metadata' => null,
            'fetched_at' => now(),
        ];
    }

    /**
     * Indicate a specific provider.
     */
    public function forProvider(string $provider): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => $provider,
        ]);
    }

    /**
     * Indicate specific content.
     */
    public function withContent(string $content): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $content,
            'content_hash' => hash('sha256', $content),
        ]);
    }

    /**
     * Indicate specific fetched_at time.
     */
    public function fetchedAt(\Illuminate\Support\Carbon $fetchedAt): static
    {
        return $this->state(fn (array $attributes) => [
            'fetched_at' => $fetchedAt,
        ]);
    }

    /**
     * Indicate that the information has metadata.
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }
}
