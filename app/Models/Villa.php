<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Villa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ownership_status',
        'price_idr',
        'price_usd',
        'description',
    ];

    public function media()
    {
        return $this->hasMany(VillaMedia::class);
    }
}
