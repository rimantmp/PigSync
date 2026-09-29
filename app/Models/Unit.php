<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'conversion'])]
class Unit extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'conversion' => 'decimal:3',
        ];
    }
}
