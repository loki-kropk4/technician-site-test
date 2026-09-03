<?php

namespace Database\Factories;

use App\Models\Entry;
use App\Models\EntryStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entry>
 */
class EntryFactory extends Factory
{
    protected $model = Entry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'name_unit' => fake()->words(2, true),
            'problem' => fake()->sentence(),
            'entry_date' => now()->toDateString(),
            'entry_time' => now()->toTimeString(),
            'status' => EntryStatus::PENDING,
        ];
    }
}
