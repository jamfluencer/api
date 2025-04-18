<?php

namespace Tests\Slack;

use App\Slack\Services\Views\Service as Views;
use Mockery\MockInterface;
use Tests\TestCase;

trait SlackStub
{
    private MockInterface $slack;

    public function setupSlackStub(): void
    {
        /** @var TestCase $this */
        $this->slack = $this->mock(Views::class);
    }
}
