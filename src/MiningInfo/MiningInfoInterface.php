<?php

namespace App\MiningInfo;

use App\DataSource\FetchException;
use App\Entity\Crypto;

/**
 * Interface declaring object which composes data from mining pool into objects used
 * elsewhere the application
 *
 * @package App\MiningInfo
 */
interface MiningInfoInterface
{
    /**
     * Returns an object containing data such as block reward or payout per 1M
     * hashes, including fees.
     *
     * @return GlobalPoolDataInterface
     * @throws FetchException
     */
    public function getGlobalPoolData(): GlobalPoolDataInterface;

    /**
     * Returns an object containing current hashes count and hash rate of the site
     *
     * @param string $siteKey
     * @return SitePoolDataContainer
     */
    public function getSitePoolData(string $siteKey): SitePoolDataContainer;

    /**
     * Returns value of hidden fee applied to total hashes count and hash rate. E.g. 0.02 for 2% fee.
     *
     * @return float
     */
    public function getHiddenFee(): float;

    public function getCrypto(): Crypto;
}
