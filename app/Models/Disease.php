<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'category', 'is_zoonosis', 'protocol'])]
class Disease extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_zoonosis' => 'boolean',
        ];
    }
}
