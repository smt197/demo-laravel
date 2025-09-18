<?php

declare(strict_types=1);

namespace App\Infrastructure\User\ReadModels;

use Illuminate\Database\Eloquent\Model;

class UserReadModel extends Model
{
    protected $table = 'user_read_models';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'email',
        'email_verified',
        'registration_date',
        'last_login',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'registration_date' => 'datetime',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}