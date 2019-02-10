<?php

namespace App\Response;

use Symfony\Component\Serializer\SerializerInterface;

interface ApiResponseCreatorInterface
{
    public function createSuccessfulSiteDeletion(array $siteEntry): array;
    public function createMaximumSitesNumberReached(array $siteEntry): array;
    public function createSuccessfulSiteCreation(array $siteEntry): array;
    public function createSuccessfulSiteEdition(array $siteEntry): array;
}
