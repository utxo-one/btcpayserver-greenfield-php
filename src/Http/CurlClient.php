<?php

declare(strict_types=1);

namespace BTCPayServer\Http;

use BTCPayServer\Exception\ConnectException;

/**
 * HTTP Client using cURL to communicate.
 */
class CurlClient implements ClientInterface
{
    /**
     * Override this method if you need to set any special parameters like disable CURLOPT_SSL_VERIFYHOST and CURLOPT_SSL_VERIFYPEER.
     * @return false|resource
     */
    protected function createCurl()
    {
        // We cannot set a return type here as it is "resource" for PHP < 8 and CurlHandle for PHP >= 8.
        return curl_init();
    }


    /**
     * @inheritdoc
     */
    public function request(
        string $method,
        string $url,
        array  $headers = [],
        string $body = ''
    ): ResponseInterface {
        $flatHeaders = [];
        foreach ($headers as $key => $value) {
            $flatHeaders[] = $key . ': ' . $value;
        }

        $ch = $this->createCurl();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($body !== '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $flatHeaders);

        $response = curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $responseHeaders = [];
        $responseBody = '';

        if ($response) {
            $responseString = is_string($response) ? $response : '';
            if ($responseString && $headerSize) {
                $responseBody = substr($responseString, $headerSize);
                $headerPart = substr($responseString, 0, $headerSize);
                $headerParts = explode("\n", $headerPart);
                foreach ($headerParts as $headerLine) {
                    $headerLine = trim($headerLine);
                    if ($headerLine) {
                        $parts = explode(':', $headerLine);
                        if (count($parts) === 2) {
                            $key = $parts[0];
                            $value = $parts[1];
                            $responseHeaders[$key] = $value;
                        }
                    }
                }
            }
        } else {
            $errorMessage = curl_error($ch);
            $errorCode = curl_errno($ch);
            throw new ConnectException($errorMessage, $errorCode);
        }

        return new Response($status, $responseBody, $responseHeaders);
    }
}
