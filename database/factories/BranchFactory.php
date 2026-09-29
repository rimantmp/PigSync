<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('???'),
            'name' => fake()->city(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'pic' => fake()->name(),
            'status' => 'aktif',
        ];
    }
}
