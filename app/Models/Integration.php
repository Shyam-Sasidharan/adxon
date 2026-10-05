<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['head_code', 'body_code'];

    protected function casts(): array
    {
        return ['head_code' => 'encrypted', 'body_code' => 'encrypted'];
    }
}
