<?php


namespace App\Entity\Site;


class DefaultType implements SiteTypeInterface
{
    public function isVisible() :bool
    {
        return false;
    }

    public function isDefault() :bool
    {
        return true;
    }

    public function getHumanReadableName(string $regularName) :string
    {
        return 'default site';
    }
}