<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unique_id' => fake()->unique()->numberBetween(100000, 999999999),
            'user_id' => User::factory(),
            'username' => fake()->userName(),
            'type' => fake()->randomElement(['private', 'group', 'supergroup', 'channel']),
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the chat is a private chat.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'private',
        ]);
    }

    /**
     * Indicate that the chat is a group chat.
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'group',
        ]);
    }

    /**
     * Indicate that the chat has metadata.
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }
}
