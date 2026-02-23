<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'firebase_uid',
        'username',
        'email',
        'password_hash',
        'role',
        'status',
        'is_verified',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;
}


