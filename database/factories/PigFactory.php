<?php

namespace Database\Factories;

use App\Models\Pen;
use App\Models\Pig;
use Illuminate\Database\Eloquent\Factories\Factory;

class PigFactory extends Factory
{
    protected $model = Pig::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('SKM-PIG-????'),
            'tag_id' => null,
            'rfid' => null,
            'sex' => $this->faker->randomElement(['jantan', 'betina']),
            'breed_id' => null,
            'birth_date' => now()->subDays(60),
            'origin_type' => 'internal',
            'origin_ref' => null,
            'sire_id' => null,
            'dam_id' => null,
            'pen_id' => Pen::factory(),
            'phase_id' => null,
            'status' => 'aktif',
            'initial_weight' => 30,
            'photo' => null,
            'notes' => null,
            'sold_at' => null,
            'died_at' => null,
        ];
    }

    public function mati(): self
    {
        return $this->state(fn () => [
            'status' => 'mati',
            'died_at' => now(),
        ]);
    }

    public function dijual(): self
    {
        return $this->state(fn () => [
            'status' => 'dijual',
            'sold_at' => now(),
        ]);
    }
}
