<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlSalida extends Model
{
    use HasFactory;

    /**
     * Exact table name in database.
     */
    protected $table = 'controlsalidas';

    /**
     * Primary key.
     */
    protected $primaryKey = 'id';

    /**
     * The table does not have created_at/updated_at timestamps.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'idUser',
        'Fecha',
        'Lugar',
        'FechaEntrada',
        'FechaSalida',
        'Revisado',
        'Comentarios',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'Fecha' => 'date:Y-m-d',
            'FechaEntrada' => 'datetime:Y-m-d H:i:s',
            'FechaSalida' => 'datetime:Y-m-d H:i:s',
            'Revisado' => 'boolean',
        ];
    }

    /**
     * Relationship with User.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }
}
