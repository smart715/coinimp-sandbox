<?php

namespace App\Fetcher;

use App\Entity\Profile;

class StaticProfileFetcher implements ProfileFetcherInterface
{
    /** @var Profile */
    private $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    public function fetchProfile(): Profile
    {
        return $this->profile;
    }
}
