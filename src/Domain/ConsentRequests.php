<?php


namespace Psd2\Domain;


use Psd2\Domain\DomainException\Psd2UrlNotSetException;

interface ConsentRequests extends SetUrls
{
    /**
     * @param string $payload
     * @param string $requestId
     * @param string $digest
     * @param string $signature
     * @param string $redirectUrl
     * @return string
     * @throws Psd2UrlNotSetException
     */
    public function initConsent(string $payload, string $requestId, string $digest, string $signature, string $redirectUrl): string;

    /**
     * @param string $requestId
     * @param string $digest
     * @param string $signature
     * @param string $consentId
     * @return string
     * @throws Psd2UrlNotSetException
     */
    public function getConsentInfo(string $requestId, string $digest, string $signature, string $consentId): string;
}
