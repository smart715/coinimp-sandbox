<?php

namespace App\Tests\Fetcher;

use App\Entity\Profile;
use App\Fetcher\StaticProfileFetcher;
use PHPUnit\Framework\TestCase;

class StaticProfileFetcherTest extends TestCase
{
    public function testFetchProfile(): void
    {
        $profile = $this->createMock(Profile::class);

        $this->assertEquals(
            $profile,
            (new StaticProfileFetcher($profile))->fetchProfile()
        );
    }
}
