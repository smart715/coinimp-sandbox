<?php

namespace App\Tests\Response;

use App\Entity\Site;
use App\Response\ApiResponseCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class ApiResponseCreatorTest extends TestCase
{
    /** @var ApiResponseCreator */
    private $apiResponseCreator;

    /** @var SerializerInterface */
    private $serializer;

    /** @var mixed[] */
    private $siteEntry = [
        'name' => 'testSite',
        'hashesRate' => 10,
        'hashesTotal' => 10,
        'reward' => 10,
        'words' => 'testWords',
        'siteKey' => 'testKey',
        'isVisible' => true,
        'editUrl' => 'coinimp_test_url.com/site_edit/testWords',
        'deleteUrl' => 'coinimp_test_url.com/site_delete/testWords',
    ];

    public function setup(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->serializer->method('serialize')->willReturn(json_encode($this->siteEntry));
        $this->apiResponseCreator = new ApiResponseCreator($this->serializer);
    }

    public function testCreateSuccessfulSiteDeletion(): void
    {
        $expect = [
            'action' => 'delete-site',
            'site' => json_decode($this->serializer->serialize($this->siteEntry, 'json')),
            'message'=>'Site "'.htmlspecialchars($this->siteEntry['name']).'" was deleted successfully.',
        ];

        $this->assertEquals(
            $expect,
            $this->apiResponseCreator->createSuccessfulSiteDeletion($this->siteEntry)
        );
    }

    public function testCreateMaximumSitesNumberReached(): void
    {
        $expect = [
            'action' => 'add-site',
            'site' => json_decode($this->serializer->serialize($this->siteEntry, 'json')),
            'message' => 'You have reached the maximum number of websites.'
                .' You can register one more profile to be able to create more.',
            'status' => 'failed',
        ];

        $this->assertEquals(
            $expect,
            $this->apiResponseCreator->createMaximumSitesNumberReached($this->siteEntry)
        );
    }

    public function testCreateSuccessfulSiteCreation(): void
    {
        $expect = [
            'action' => 'add-site',
            'site' => json_decode($this->serializer->serialize($this->siteEntry, 'json')),
            'message' => 'Site "' . htmlspecialchars($this->siteEntry['name']) . '" was created successfully.',
            'status' => 'success',
        ];

        $this->assertEquals(
            $expect,
            $this->apiResponseCreator->createSuccessfulSiteCreation($this->siteEntry)
        );
    }

    public function testCreateSuccessfulSiteEdition(): void
    {
        $expect = [
            'action' => 'edit-site',
            'site' => json_decode($this->serializer->serialize($this->siteEntry, 'json')),
            'message'=>'Site "'.htmlspecialchars($this->siteEntry['name']).'" was edited successfully.',
        ];
        $this->assertEquals(
            $expect,
            $this->apiResponseCreator->createSuccessfulSiteEdition($this->siteEntry)
        );
    }
}
