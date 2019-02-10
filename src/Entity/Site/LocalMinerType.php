<?php


namespace App\Entity\Site;


class LocalMinerType implements SiteTypeInterface
{
    public function isVisible() :bool
    {
        return false;
    }

    public function isDefault() :bool
    {
        return false;
    }

    public function getHumanReadableName(string $regularName) :string
    {
        return 'local miner';
    }
}