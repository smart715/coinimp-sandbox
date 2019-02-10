<?php


namespace App\Entity\Site;


class UserCreatedType implements SiteTypeInterface
{
    public function isVisible() :bool
    {
        return true;
    }

    public function isDefault() :bool
    {
        return false;
    }

    public function getHumanReadableName(string $regularName) :string
    {
        return $regularName;
    }
}