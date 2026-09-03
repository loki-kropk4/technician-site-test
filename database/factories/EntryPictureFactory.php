<?php

namespace Database\Factories;

use App\Models\Entry;
use App\Models\EntryPicture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntryPicture>
 */
class EntryPictureFactory extends Factory
{
    protected $model = EntryPicture::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory(),
            'file_name' => fake()->uuid().'.jpg',
        ];
    }
}
