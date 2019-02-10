<?php


namespace App\Entity\Site;


interface SiteTypeInterface
{
    public function isVisible() :bool;
    public function isDefault() :bool;
    public function getHumanReadableName(string $regularName) :string;
}