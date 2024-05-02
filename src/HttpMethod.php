<?php

/**
 * HttpMethod
 *
 * @package PhpPayload
 * @license MIT https://github.com/SandroMiguel/php-api-router/blob/main/LICENSE
 * @author Sandro Miguel Marques <sandromiguel@sandromiguel.com>
 * @link https://github.com/SandroMiguel/php-api-router
 * @version 1.0.0 (2024-03-08)
 */

declare(strict_types=1);

namespace PhpPayload;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}
