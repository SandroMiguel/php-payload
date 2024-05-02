<?php

/**
 * ServerRequest
 *
 * @package PhpPayload
 * @license MIT https://github.com/SandroMiguel/php-payload/blob/main/LICENSE
 * @author Sandro Miguel Marques <sandromiguel@sandromiguel.com>
 * @link https://github.com/SandroMiguel/php-payload
 * @version 1.0.0 (2024-03-07)
 */

declare(strict_types=1);

namespace PhpPayload;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Parsing HTTP request parameters from different sources like
 *  `php://input`, `$_FILES`, `$_GET`, and `$_POST`.
 */
final readonly class ServerRequest implements ServerRequestInterface
{

    /** @var UriInterface URI instance. */
    private UriInterface $uri;

    /** @var array<string,mixed> Attributes derived from the request. */
    private array $attributes;

    /** @var array<string,string> Cookie parameters. */
    private array $cookieParams;

    /** @var array<string,string> Query parameters. */
    private array $queryParams;

    /** @var array<array-key,mixed>|object|null Parsed body parameters. */
    private mixed $parsedBody;

    /** @var array<string,mixed> Server parameters. */
    private array $serverParams;

    /** @var array<array-key,UploadedFileInterface> Uploaded files. */
    private array $uploadedFiles;

    /** @var string|null Request target. */
    private ?string $requestTarget;

    /** @var string HTTP method. */
    private string $method;

    /** @var string Protocol version. */
    private string $protocolVersion;

    /** @var array<array<array-key,string>> Headers. */
    private array $headers;

    /** @var StreamInterface|null Request body. */
    private ?StreamInterface $body;

    /**
     * Constructor.
     *
     * @param UriInterface $uri URI instance.
     * @param array<string,mixed> $attributes Attributes derived from the
     *  request.
     * @param array<string,string> $cookieParams Cookie parameters.
     * @param array<string,string> $queryParams Query parameters.
     * @param array<array-key,mixed>|object|null $parsedBody Parsed body
     *  parameters.
     * @param array<string,mixed> $serverParams Server parameters.
     * @param array<array-key,UploadedFileInterface> $uploadedFiles Uploaded files.
     * @param string|null $requestTarget Request target.
     * @param string $method HTTP method.
     * @param string $protocolVersion Protocol version.
     * @param array<array<array-key,string>> $headers Headers.
     * @param StreamInterface|null $body Request body.
     */
    public function __construct(
        UriInterface $uri,
        array $attributes = [],
        array $cookieParams = [],
        array $queryParams = [],
        array|object|null $parsedBody = null,
        array $serverParams = [],
        array $uploadedFiles = [],
        ?string $requestTarget = null,
        string $method = 'GET',
        string $protocolVersion = '1.1',
        array $headers = [],
        ?StreamInterface $body = null,
    ) {
        $this->uri = $uri;
        $this->attributes = $attributes;
        $this->cookieParams = $cookieParams;
        $this->queryParams = $queryParams;
        $this->parsedBody = $parsedBody;
        $this->serverParams = $serverParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->requestTarget = $requestTarget;
        $this->method = $method;
        $this->protocolVersion = $protocolVersion;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Retrieve query string arguments.
     *
     * @return array<string,string> Query string arguments.
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Retrieve server parameters.
     *
     * @return array<string,mixed|false> Filtered server parameters.
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * Retrieves cookies sent by the client to the server.
     *
     * @return array<string,string|false> Filtered cookie parameters.
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * Returns a new instance with the specified cookies.
     *
     * @param array<string,string> $cookies Associative array of cookie
     *  parameters.
     *
     * @return static A new instance with the specified cookies.
     */
    public function withCookieParams(array $cookies): static
    {
        return new static(
            $this->uri,
            $this->attributes,
            $cookies,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $this->headers,
            $this->body
        );
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * @param array<string,string> $query Associative array of query parameters.
     *
     * @return static A new instance with the specified query parameters.
     */
    public function withQueryParams(array $query): static
    {
        return new static(
            $this->uri,
            $this->attributes,
            $this->cookieParams,
            $query,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $this->headers,
            $this->body
        );
    }

    /**
     * Retrieve normalized file upload data.
     *
     * @return array<array-key,UploadedFileInterface> File upload data.
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * Return an instance with the specified uploaded files.
     *
     * @param array<array-key,UploadedFileInterface> $uploadedFiles An array
     *  of uploaded file data.
     *
     * @return static A new instance with the specified uploaded files.
     */
    public function withUploadedFiles(array $uploadedFiles): static
    {
        return new static(
            $this->uri,
            $this->attributes,
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $this->headers,
            $this->body
        );
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * @return array<array-key,mixed>|object|null The deserialized body
     *  parameters, if any. These will typically be an array or object.
     */
    public function getParsedBody(): null|array|object|string|false
    {
        if (isset($this->parsedBody)) {
            return $this->parsedBody;
        }

        if (!$this->body instanceof StreamInterface) {
            return null;
        }

        $bodyContents = $this->body->__toString();

        return (array) \json_decode($bodyContents, true);
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * @param array<array-key,mixed>|object|null $data The deserialized body
     *  data. This will typically be in an array or object.
     *
     * @return static A new instance with the specified body parameters.
     */
    public function withParsedBody(mixed $data): static
    {
        return new static(
            $this->uri,
            $this->attributes,
            $this->cookieParams,
            $this->queryParams,
            $data,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $this->headers,
            $this->body
        );
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * @return array<string,mixed> Attributes derived from the request.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not
     *  exist.
     *
     * @see getAttributes()
     *
     * @return mixed The value of the attribute or the default value if not
     *   found.
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     *
     * @see getAttributes()
     *
     * @return static A new instance with the specified attribute.
     */
    public function withAttribute(string $name, mixed $value): static
    {
        return new static(
            $this->uri,
            \array_merge($this->attributes, [$name => $value]),
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $this->headers,
            $this->body
        );
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * @param string $name The attribute name.
     *
     * @see getAttributes()
     *
     * @return static A new instance with the specified attribute removed.
     */
    public function withoutAttribute(string $name): static
    {
        return new static(
            $this->uri,
            \array_diff_key($this->attributes, [$name => null]),
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $this->headers,
            $this->body
        );
    }

    /**
     * Retrieves the message's request target.
     *
     * @return string Returns the request target, or "/" if none.
     */
    public function getRequestTarget(): string
    {
        return $this->requestTarget ?? '/';
    }

    /**
     * Return an instance with the specific request-target.
     *
     * @param mixed $requestTarget The request-target, if different from the
     *   primary request-target.
     *
     * @see http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *     request-target forms allowed in request messages)
     *
     * @return static A new instance with the specified request target.
     */
    public function withRequestTarget(mixed $requestTarget): static
    {
        if (!\is_string($requestTarget)) {
            throw new \InvalidArgumentException(
                'Invalid request target provided; must be a string'
            );
        }

        return new static(
            $this->uri,
            $this->attributes,
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $requestTarget,
            $this->method,
            $this->protocolVersion,
            $this->headers,
            $this->body
        );
    }

    /**
     * Retrieves the HTTP method of the request.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * @param string $method Case-sensitive method.
     *
     * @return static
     *
     * @throws \InvalidArgumentException For invalid HTTP methods.
     */
    public function withMethod(string $method): static
    {
        if (!$this->isValidHttpMethod($method)) {
            throw new \InvalidArgumentException(
                \sprintf(
                    '%s expects a valid HTTP method; received %s',
                    __METHOD__,
                    $method
                )
            );
        }

        return new static(
            $this->uri,
            $this->attributes,
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $method,
            $this->protocolVersion,
            $this->headers,
            $this->body
        );
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the
     *   returned request.
     * - If the Host header is missing or empty, and the new URI does not
     *   contain a host component, this method MUST NOT update the Host header
     *   in the returned request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     *
     * @return static A new instance with the provided URI.
     */
    public function withUri(
        UriInterface $uri,
        bool $preserveHost = false,
    ): static {
        $new = new static(
            $uri,
            $this->attributes,
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $this->headers,
            $this->body
        );

        if ($preserveHost && $new->hasHeader('Host')) {
            return $new;
        }

        return $new->withHeader('Host', $uri->getHost());
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number
     *  E.g., "1.1", "1.0"
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * @param string $protocolVersion HTTP protocol version
     *
     * @return static An instance with the specified HTTP protocol version.
     */
    public function withProtocolVersion(string $protocolVersion): static
    {
        return new static(
            $this->uri,
            $this->attributes,
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $protocolVersion,
            $this->headers,
            $this->body
        );
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     - Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ': ' . implode(', ', $values);
     *     }
     *
     *     - Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * @return array<array<string,string>> Returns an associative array of the
     *  message's headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader(string $name): bool
    {
        return \array_key_exists($name, $this->headers);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return array<string,string> An array of string values as provided for
     *  the given header. If the header does not appear in the message, it
     *  returns an empty array.
     */
    public function getHeader(string $name): array
    {
        return $this->headers[$name] ?? [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header
     *  concatenated together using a comma. Returns an empty string if
     *  the header does not appear in the message.
     */
    public function getHeaderLine(string $name): string
    {
        return \implode(', ', $this->getHeader($name));
    }

    /**
     * Return an instance with the provided value replacing the specified
     *  header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * @param string $name Case-insensitive header field name.
     * @param string|array<array-key,string> $value Header value(s).
     *
     * @return static An instance with the specified header replaced.
     *
     * @throws \InvalidArgumentException For invalid header names or values.
     */
    public function withHeader(string $name, mixed $value): static
    {
        if (!\is_array($value)) {
            $value = [$value];
        }

        $newHeaders = $this->headers;
        $newHeaders[$name] = $value;

        return new static(
            $this->uri,
            $this->attributes,
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $newHeaders,
            $this->body
        );
    }

    /**
     * Return an instance with the specified header appended with the given
     *  value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|array<array-key,string> $value Header value(s).
     *
     * @return static An instance with the specified header appended with the
     *   given value.
     *
     * @throws \InvalidArgumentException For invalid header names or values.
     */
    public function withAddedHeader(string $name, mixed $value): static
    {
        if (!\is_array($value)) {
            $value = [$value];
        }

        $newHeaders = $this->headers;
        $newHeaders[$name] = \array_merge($newHeaders[$name] ?? [], $value);

        return new static(
            $this->uri,
            $this->attributes,
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $newHeaders,
            $this->body
        );
    }

    /**
     * Return an instance without the specified header.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return static An instance without the specified header.
     */
    public function withoutHeader(string $name): static
    {
        return $this->withHeader($name, []);
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     *
     * @throws \RuntimeException If the body stream is no longer available.
     */
    public function getBody(): StreamInterface
    {
        if (!$this->body) {
            throw new \RuntimeException(
                'The body stream is missing.'
            );
        }

        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * @param StreamInterface $body Body.
     *
     * @return static An instance with the specified message body.
     *
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body): static
    {
        return new static(
            $this->uri,
            $this->attributes,
            $this->cookieParams,
            $this->queryParams,
            $this->parsedBody,
            $this->serverParams,
            $this->uploadedFiles,
            $this->requestTarget,
            $this->method,
            $this->protocolVersion,
            $this->headers,
            $body
        );
    }

    /**
     * Is the request a valid HTTP method
     *
     * @param string $method Case-sensitive method.
     *
     * @return bool True if the method is valid, false otherwise.
     */
    private function isValidHttpMethod(string $method): bool
    {
        foreach (HttpMethod::cases() as $httpMethod) {
            if ($httpMethod->value === $method) {
                return true;
            }
        }

        return false;
    }
}
