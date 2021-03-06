<?php


namespace Codehell\Psd2\Infrastructure\Redsys;


use GuzzleHttp\Client;
use Codehell\Psd2\Domain\PaymentRequester;
use Codehell\Psd2\Domain\DomainTraits\SetUrls;
use Codehell\Psd2\Infrastructure\Redsys\Requests\Payment;
use Codehell\Psd2\Domain\DomainException\Psd2UrlNotSetException;
use Codehell\Psd2\Infrastructure\Redsys\Helpers\GuzzleHandlerStackBuilder;

final class RedsysPaymentRequester implements PaymentRequester
{
    use SetUrls;

    private $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * The key is clientId
     * {@inheritDoc}
     */
    public function initPayment(): string
    {
        if (is_null($this->urls)) {
            throw new Psd2UrlNotSetException;
        }

        $stack = GuzzleHandlerStackBuilder::create();

        $client = new Client([
            'handler' => $stack,
            'base_uri' => $this->urls->baseUrl()
        ]);
        $certificate = $this->payment->getPlainCertificate();
        $headers = [
            'PSU-Accept' => 'application/json',
            'PSU-Accept-Language' => $this->payment->getPsuAcceptLanguage(),
            'PSU-Accept-Charset' => $this->payment->getPsuAcceptCharset(),
            'PSU-Accept-Encoding' => $this->payment->getPsuAcceptEncoding(),
            'content-type' => 'application/json',
            'TPP-Signature-Certificate' => $certificate,
            'PSU-IP-Address' => $this->payment->getPsuIp(),
            'Digest' => $this->payment->getSha256Digest(),
            'Signature' => $this->payment->getHeaderSignature(),
            'TPP-Redirect-URI' => $this->payment->getRedirectUri(),
            'Authorization' => 'Bearer ' . $this->payment->getToken(),
            'X-Request-ID' => $this->payment->getRequestId(),
            'X-IBM-Client-Id' => $this->payment->getClientId(),
            'PSU-Http-Method' => 'POST',
            'TPP-Redirect-Preferred' => 'true',
        ];
        $body = $this->payment->getPayload();
        $res = $client->request(
            'POST',
            $this->payment->getAspsp() . '/' . $this->payment->getVersion() . '/sva/payments/sepa-credit-transfers',
            [
                'headers' => $headers,
                'body' => $body,
                'debug' => false
            ]
        );
        $res->getBody()->rewind();
        return $res->getBody()->getContents();
    }
}
