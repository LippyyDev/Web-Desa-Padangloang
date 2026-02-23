<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthTokenModel extends Model
{
    protected $table            = 'auth_tokens';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'token_type',
        'token',
        'expires_at',
        'is_used',
        'created_at',
    ];
    protected $useTimestamps    = false;
}


