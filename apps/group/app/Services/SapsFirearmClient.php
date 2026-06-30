<?php

namespace App\Services;

class SapsFirearmClient
{
    private const ENQUIRY_URL = 'https://www.saps.gov.za/services/firearm_status_enquiry.php';

    /**
     * @return array{
     *     success: bool,
     *     data_updated?: string|null,
     *     records?: list<array<string, string>>,
     *     message?: string,
     *     error?: string
     * }
     */
    public function enquiry(string $referenceNumber, ?string $serialNumber = null): array
    {
        $referenceNumber = trim($referenceNumber);
        $serialNumber = $serialNumber !== null ? trim($serialNumber) : null;

        if ($referenceNumber === '') {
            return [
                'success' => false,
                'error' => 'Reference number is required.',
            ];
        }

        $html = $this->fetchResults($referenceNumber, $serialNumber);

        if ($html === null) {
            return [
                'success' => false,
                'error' => 'Unable to reach the SAPS enquiry service. Please try again later.',
            ];
        }

        return $this->parseResponse($html);
    }

    private function fetchResults(string $referenceNumber, ?string $serialNumber): ?string
    {
        $cookieFile = tempnam(sys_get_temp_dir(), 'saps_cookies_');

        if ($cookieFile === false) {
            return null;
        }

        try {
            if ($this->request(self::ENQUIRY_URL, [
                CURLOPT_HTTPGET => true,
            ], $cookieFile) === null) {
                return null;
            }

            $csrfToken = $this->readCsrfToken($cookieFile) ?? '';

            $postFields = http_build_query([
                'csrf_token' => $csrfToken,
                'fsref' => $referenceNumber,
                'fserial' => $serialNumber ?? '',
                'submit' => 'Submit',
            ]);

            $postResponse = $this->request(self::ENQUIRY_URL, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Content-Length: '.strlen($postFields),
                    'Accept: text/html,application/xhtml+xml',
                    'Referer: '.self::ENQUIRY_URL,
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ],
            ], $cookieFile);

            if ($postResponse === null || $postResponse['code'] >= 400) {
                return null;
            }

            return $postResponse['body'];
        } finally {
            if (is_file($cookieFile)) {
                unlink($cookieFile);
            }
        }
    }

    /**
     * @param  array<int, mixed>  $options
     * @return array{code: int, headers: string, body: string}|null
     */
    private function request(string $url, array $options, string $cookieFile): ?array
    {
        if (! str_starts_with($url, 'http')) {
            $url = 'https://www.saps.gov.za'.$url;
        }

        $ch = curl_init($url);

        $followLocation = ($options[CURLOPT_FOLLOWLOCATION] ?? false) === true;

        $defaults = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER => ! $followLocation,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: text/html,application/xhtml+xml',
            ],
        ];

        curl_setopt_array($ch, $options + $defaults);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($response === false) {
            return null;
        }

        return [
            'code' => $httpCode,
            'headers' => $followLocation ? '' : substr($response, 0, $headerSize),
            'body' => $followLocation ? (string) $response : substr($response, $headerSize),
        ];
    }

    private function readCsrfToken(string $cookieFile): ?string
    {
        if (! is_file($cookieFile)) {
            return null;
        }

        $lines = file($cookieFile, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $line) {
            if (! str_contains($line, 'csrf_token')) {
                continue;
            }

            $line = ltrim($line, '#');
            $parts = explode("\t", $line);

            if (count($parts) >= 7 && $parts[5] === 'csrf_token') {
                return $parts[6];
            }
        }

        return null;
    }

    /**
     * @return array{
     *     success: bool,
     *     data_updated?: string|null,
     *     records?: list<array<string, string>>,
     *     message?: string,
     *     error?: string
     * }
     */
    private function parseResponse(string $html): array
    {
        $dataUpdated = null;

        if (preg_match('/updated on <b>(\d{4}-\d{2}-\d{2})<\/b>/i', $html, $matches)) {
            $dataUpdated = $matches[1];
        }

        libxml_use_internal_errors(true);
        $document = new \DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($document);
        $resultContainer = $xpath->query("//div[contains(@class, 'pagSelMed')]")->item(0);

        if (! $resultContainer instanceof \DOMElement) {
            return [
                'success' => false,
                'error' => 'Unexpected response from SAPS. Could not locate search results.',
            ];
        }

        $headers = [];
        $headerNodes = $xpath->query('.//thead//th', $resultContainer);

        if ($headerNodes !== false) {
            foreach ($headerNodes as $headerNode) {
                $headers[] = trim($headerNode->textContent);
            }
        }

        $records = [];
        $rowNodes = $xpath->query('.//tbody//tr', $resultContainer);

        if ($rowNodes === false || $rowNodes->length === 0) {
            $rowNodes = $xpath->query('.//tr[not(ancestor::thead)]', $resultContainer);
        }

        if ($rowNodes !== false) {
            foreach ($rowNodes as $rowNode) {
                $cells = [];
                $cellNodes = $xpath->query('.//td', $rowNode);

                if ($cellNodes === false || $cellNodes->length === 0) {
                    continue;
                }

                foreach ($cellNodes as $index => $cellNode) {
                    $key = $headers[$index] ?? 'Column '.($index + 1);
                    $cells[$this->normalizeKey($key)] = trim(preg_replace('/\s+/', ' ', $cellNode->textContent ?? ''));
                }

                if ($cells !== []) {
                    $records[] = $cells;
                }
            }
        }

        if ($records === []) {
            $message = trim($resultContainer->textContent ?? '');

            return [
                'success' => true,
                'data_updated' => $dataUpdated,
                'records' => [],
                'message' => $message !== '' ? $message : 'No matching application found.',
            ];
        }

        return [
            'success' => true,
            'data_updated' => $dataUpdated,
            'records' => $records,
        ];
    }

    private function normalizeKey(string $label): string
    {
        $key = strtolower(trim($label));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? $key;

        return trim($key, '_');
    }
}
