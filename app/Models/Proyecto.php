<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';

    protected $primaryKey = 'idProyectos';

    public $timestamps = false;

    protected $fillable = [
        'Nombreproyecto',
        'descripcion',
        'estadoProyecto',
        'enlaceRepositorio',
        'observaciones',
    ];
}
