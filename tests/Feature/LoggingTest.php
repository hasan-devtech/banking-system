<?php

namespace Tests\Feature;

use App\Services\AuditLogger;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LoggingTest extends TestCase
{
    /**
     * Test that the audit logger writes to the correct channel.
     */
    public function test_audit_logger_writes_to_audit_channel()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with('audit')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('test.action', \Mockery::on(function ($data) {
                return $data['action'] === 'test.action'
                    && $data['user_id'] === 'system';
            }));

        $logger = new AuditLogger();
        $logger->log('test.action');
    }
}
