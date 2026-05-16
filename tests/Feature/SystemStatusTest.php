<?php

namespace Tests\Feature;

use Tests\TestCase;

class SystemStatusTest extends TestCase
{
    public function test_it_returns_the_public_api_status(): void
    {
        $this->getJson('/api/v1/status')
            ->assertOk()
            ->assertJsonPath('data.name', 'LaunchBill')
            ->assertJsonPath('data.status', 'ready');
    }
}
