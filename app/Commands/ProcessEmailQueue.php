<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\EmailQueueProcessor;

class ProcessEmailQueue extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:process';
    protected $description = 'Process email queue manually';

    public function run(array $params)
    {
        CLI::write('Processing email queue...', 'yellow');
        
        $processor = new EmailQueueProcessor();
        $processed = $processor->process(10);
        
        if ($processed > 0) {
            CLI::write("Successfully processed {$processed} emails", 'green');
        } else {
            CLI::write('No emails to process', 'yellow');
        }
    }
}

