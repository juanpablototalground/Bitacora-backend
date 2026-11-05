<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    use HasFactory;

    /**
     * Exact table name in database.
     */
    protected $table = 'reportes';

    /**
     * Primary key.
     */
    protected $primaryKey = 'idReportes';

    /**
     * Disable default created_at / updated_at timestamps.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'idUser',
        'trabajo',
        'fechaInicio',
        'fechaTermino',
        'revisado',
        'observaciones',
        'comentarios',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fechaInicio' => 'datetime:Y-m-d H:i:s',
            'fechaTermino' => 'datetime:Y-m-d H:i:s',
            'revisado' => 'boolean',
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
