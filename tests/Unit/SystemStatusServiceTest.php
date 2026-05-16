<?php

namespace Tests\Unit;

use App\Services\System\SystemStatusService;
use Tests\TestCase;

class SystemStatusServiceTest extends TestCase
{
    public function test_it_builds_a_ready_status_payload(): void
    {
        $status = (new SystemStatusService)->status();

        $this->assertSame('ready', $status['status']);
        $this->assertArrayHasKey('name', $status);
        $this->assertArrayHasKey('environment', $status);
        $this->assertArrayHasKey('version', $status);
    }
}
