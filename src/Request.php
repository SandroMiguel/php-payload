<?php

/**
 * Request
 *
 * @package PhpPayload
 * @license MIT https://github.com/SandroMiguel/php-payload/blob/main/LICENSE
 * @author Sandro Miguel Marques <sandromiguel@sandromiguel.com>
 * @link https://github.com/SandroMiguel/php-payload
 * @version 1.0.0 (2024-03-07)
 */

declare(strict_types=1);

namespace PhpPayload;

/**
 * Parsing HTTP request parameters from different sources like
 *  `php://input`, `$_FILES`, `$_GET`, and `$_POST`.
 */
final readonly class Request
{
    /**
     * Retrieve uploaded files securely.
     *
     * This method retrieves uploaded files using the $_FILES superglobal.
     * If no files are uploaded, it returns an empty array.
     *
     * @return list<array{
     *  name: string|false,
     *  size: string|false,
     *  tmp_name:string|false,
     *  type: string|false
     * }> The uploaded files as an array.
     */
    public function getFiles(): array
    {
        $filteredFiles = [];

        foreach ($_FILES as $fileInfo) {
            $filteredFile = [
                'name' => \filter_var(
                    $fileInfo['name']
                ),
                'size' => \filter_var(
                    $fileInfo['size'],
                    \FILTER_SANITIZE_NUMBER_INT
                ),
                'tmp_name' => \filter_var(
                    $fileInfo['tmp_name']
                ),
                'type' => \filter_var(
                    $fileInfo['type']
                ),
            ];

            $filteredFiles[] = $filteredFile;
        }

        return $filteredFiles;
    }

    /**
     * Retrieve GET parameters securely.
     *
     * This method retrieves query parameters using a secure function with the
     * `FILTER_REQUIRE_ARRAY` flag to ensure that the result is always an array.
     * If no query parameters are present, it returns an empty array.
     *
     * @return array<array-key,string> The query parameters as an array.
     */
    public function getQueryParams(): array
    {
        return \is_array(\filter_input_array(\INPUT_GET))
            ? \filter_input_array(\INPUT_GET)
            : [];
    }

    /**
     * Retrieve POST parameters securely.
     *
     * This method retrieves POST parameters using a secure function with the
     * `FILTER_REQUIRE_ARRAY` flag to ensure that the result is always an array.
     * If no POST parameters are present, it returns an empty array.
     *
     * @return array<array-key,string> The POST parameters as an array.
     */
    public function getPostParams(): array
    {
        return \is_array(\filter_input_array(\INPUT_POST))
            ? \filter_input_array(\INPUT_POST)
            : [];
    }

    /**
     * Retrieve request body.
     *
     * This method retrieves the raw request body from the php://input stream.
     * If no data is available, it returns an empty array.
     * If the data is JSON, it is decoded and returned as an array.
     *
     * @return array<array-key,mixed> The raw request body or decoded JSON as
     *  an array.
     */
    public function getRequestBody(): array
    {
        $rawData = \file_get_contents('php://input');
        $decodedData = (array) \json_decode((string) $rawData, true);

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            return [];
        }

        return $decodedData;
    }

    /**
     * Retrieve all request parameters securely.
     *
     * This method retrieves all request parameters, including query parameters,
     * POST parameters, uploaded files, and request body data, and merges them
     * into a single array.
     *
     * @return array<array-key,mixed> All request parameters merged into a
     *  single array.
     */
    public function getAllParams(): array
    {
        return \array_merge(
            $this->getQueryParams(),
            $this->getPostParams(),
            $this->getFiles(),
            $this->getRequestBody()
        );
    }
}
