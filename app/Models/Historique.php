<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historique extends Model
{
    use HasFactory;
    public function user()
	{
		return $this->belongsTo(User::class);
	}

    public function reglesSalaire()
	{
		return $this->belongsTo(ReglesSalaire::class);
	}
}
