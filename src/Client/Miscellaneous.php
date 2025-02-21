<?php

declare(strict_types=1);

namespace BTCPayServer\Client;

use BTCPayServer\Result\InvoiceCheckoutHtml;
use BTCPayServer\Result\LanguageCodeList;
use BTCPayServer\Result\PermissionMetadataList;

class Miscellaneous extends AbstractClient
{
    public function getPermissionMetadata(): PermissionMetadataList
    {
        $url = $this->getBaseUrl() . '/misc/permissions';
        $headers = $this->getRequestHeaders();
        $method = 'GET';

        $response = $this->getHttpClient()->request($method, $url, $headers);

        if ($response->getStatus() === 200) {
            return new PermissionMetadataList(
                json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR)
            );
        } else {
            throw $this->getExceptionByStatusCode($method, $url, $response);
        }
    }

    public function getLanguageCodes(): LanguageCodeList
    {
        $url = $this->getBaseUrl() . '/misc/lang';
        $headers = $this->getRequestHeaders();
        $method = 'GET';

        $response = $this->getHttpClient()->request($method, $url, $headers);

        if ($response->getStatus() === 200) {
            return new LanguageCodeList(
                json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR)
            );
        } else {
            throw $this->getExceptionByStatusCode($method, $url, $response);
        }
    }

    public function getInvoiceCheckout(
        string $invoiceId,
        ?string $lang
    ): InvoiceCheckoutHtml {
        $url = $this->getBaseUrl() . '/i/' . urlencode($invoiceId);

        //set language query parameter if passed
        if (isset($lang)) {
            $url .= '?lang=' . $lang;
        }

        $headers = $this->getRequestHeaders();
        $method = 'GET';

        $response = $this->getHttpClient()->request($method, $url, $headers);

        if ($response->getStatus() === 200) {
            return new InvoiceCheckoutHtml($response->getBody());
        } else {
            throw $this->getExceptionByStatusCode($method, $url, $response);
        }
    }
}
