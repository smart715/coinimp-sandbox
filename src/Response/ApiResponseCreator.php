<?php

namespace App\Response;

use Symfony\Component\Serializer\SerializerInterface;

class ApiResponseCreator implements ApiResponseCreatorInterface
{
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function createSuccessfulSiteDeletion(array $siteEntry): array
    {
        return [
            'action' => 'delete-site',
            'site' => json_decode($this->serializer->serialize($siteEntry, 'json')),
            'message' => 'Site "'.htmlspecialchars($siteEntry['name']).'" was deleted successfully.',
        ];
    }

    public function createMaximumSitesNumberReached(array $siteEntry): array
    {
        return [
            'action' => 'add-site',
            'site' => json_decode($this->serializer->serialize($siteEntry, 'json')),
            'message' => 'You have reached the maximum number of websites.'
                .' You can register one more profile to be able to create more.',
            'status' => 'failed',
        ];
    }

    public function createSuccessfulSiteCreation(array $siteEntry): array
    {
        return [
            'action' => 'add-site',
            'site' => json_decode($this->serializer->serialize($siteEntry, 'json')),
            'message' => 'Site "' . htmlspecialchars($siteEntry['name']) . '" was created successfully.',
            'status' => 'success',
        ];
    }

    public function createSuccessfulSiteEdition(array $siteEntry): array
    {
        return [
            'action' => 'edit-site',
            'site' => json_decode($this->serializer->serialize($siteEntry, 'json')),
            'message' => 'Site "'.htmlspecialchars($siteEntry['name']).'" was edited successfully.',
        ];
    }
}
