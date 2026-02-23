<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'type',
        'title',
        'message',
        'related_letter_id',
        'related_reply_id',
        'is_read',
        'created_at',
        'read_at',
    ];
    protected $useTimestamps    = false;
}


