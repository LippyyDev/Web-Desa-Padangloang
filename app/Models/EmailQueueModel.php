<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailQueueModel extends Model
{
    protected $table            = 'email_queue';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'recipient',
        'subject',
        'body',
        'is_sent',
        'processing_token',
        'processing_at',
        'fail_count',
        'last_error',
        'sent_at',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = false;
    
    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 2;
    const STATUS_SENT = 1;
}

