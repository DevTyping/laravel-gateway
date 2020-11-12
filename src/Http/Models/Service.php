<?php

namespace DevTyping\Gateway\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Service
 * @package DevTyping\Gateway\Http\Models
 *
 * @property string id
 */
class Service extends Model
{
    protected $table = 'services';
    protected $keyType = 'string';
    protected $guarded = [];
    public $incrementing = false;

    protected $casts = [
        'routes' => 'array',
        'roles' => 'array',
        'defaults' => 'array'
    ];

    protected $attributes = [
        'defaults' => '{}'
    ];

    protected $fillable = [
        'id',
        'name',
        'description',
        'protocol',
        'host',
        'port',
        'path',
        'connect_timeout',
        'routes',
        'roles',
        'defaults'
    ];


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->id = Str::uuid()->toString();
            }
        });
    }
}
