<?php

namespace Tests\Feature;

use Tests\TestCase;

class RootEndpointTest extends TestCase
{
    public function test_it_returns_the_api_root_status(): void
    {
        $this->getJson('/')
            ->assertOk()
            ->assertJsonPath('name', 'LaunchBill')
            ->assertJsonPath('status', 'ready');
    }
}
