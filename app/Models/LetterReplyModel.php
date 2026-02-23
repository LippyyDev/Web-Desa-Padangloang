<?php

namespace App\Models;

use CodeIgniter\Model;

class LetterReplyModel extends Model
{
    protected $table            = 'letter_replies';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'letter_id',
        'staff_id',
        'reply_text',
        'created_at',
    ];
    protected $useTimestamps    = false;
}


