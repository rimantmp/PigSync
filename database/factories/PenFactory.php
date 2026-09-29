<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Pen;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenFactory extends Factory
{
    protected $model = Pen::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'area_id' => null,
            'code' => fake()->unique()->lexify('PEN-???'),
            'name' => fake()->words(2, true),
            'type' => 'fattening',
            'capacity' => 100,
            'status' => 'aktif',
        ];
    }

    public function full(): self
    {
        return $this->state(fn () => ['capacity' => 0]);
    }
}
