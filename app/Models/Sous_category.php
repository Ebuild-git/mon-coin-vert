<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sous_category extends Model
{
    use HasFactory;use SoftDeletes;
    protected $fillable = [
      'titre',
        'icone',
        'image',
        'photos',
    'categorie_id',
       'description',
    ];

    public function produits()
    {
        return $this->hasMany(produits::class, 'sous_category_id');
    }


    public function categories()
    {
        return $this->belongsTo(Category::class,  'categorie_id', 'id');
    }
}