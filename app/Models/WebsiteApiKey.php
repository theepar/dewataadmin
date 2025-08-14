<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteApiKey extends Model
{
    protected $fillable = [
        'website_name',
        'api_key',
    ];
}
