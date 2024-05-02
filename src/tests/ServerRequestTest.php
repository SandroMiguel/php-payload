<?php

/**
 * ServerRequestTest
 *
 * @package PhpPayload
 * @license MIT https://github.com/SandroMiguel/php-payload/blob/main/LICENSE
 * @author Sandro Miguel Marques <sandromiguel@sandromiguel.com>
 * @link https://github.com/SandroMiguel/php-payload
 * @version 1.0.0 (2024-03-09)
 */

declare(strict_types=1);

namespace PhpPayload\Tests;

use PhpPayload\ServerRequest;
use PhpPayload\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Test for the `ServerRequest` class.
 */
final class ServerRequestTest extends TestCase
{
    /**
     * Test for retrieving query parameters.
     */
    public function testGetQueryParams(): void
    {
        $queryParams = [
            'age' => '30',
            'city' => 'New York',
            'name' => 'John Doe',
        ];

        $request = new ServerRequest(
            uri: $this->getUriMock(),
            queryParams: $queryParams
        );

        $this->assertEquals($queryParams, $request->getQueryParams());
    }

    /**
     * Test for retrieving server parameters.
     */
    public function testGetServerParams(): void
    {
        // Sample server parameters
        $serverParams = [
            'REQUEST_URI' => '/test',
            'SERVER_NAME' => 'example.com',
            'SERVER_PORT' => 80,
        ];

        // Creating a server request instance
        $serverRequest = new ServerRequest(
            uri: $this->getUriMock(),
            serverParams: $serverParams
        );

        // Asserting that getServerParams returns the expected server parameters
        $this->assertSame($serverParams, $serverRequest->getServerParams());
    }

    /**
     * Test for retrieving cookie parameters.
     */
    public function testGetCookieParams(): void
    {
        $cookieParams = [
            'cookie_name' => 'cookie_value',
            'other_cookie' => 'other_value',
        ];

        $request = new ServerRequest(
            uri: $this->getUriMock(),
            cookieParams: $cookieParams
        );

        $actualCookieParams = $request->getCookieParams();

        $this->assertEquals($cookieParams, $actualCookieParams);
    }

    /**
     * Test for setting cookie parameters.
     */
    public function testWithCookieParams(): void
    {
        $oldRequest = new ServerRequest($this->getUriMock());
        $oldCookieParams = $oldRequest->getCookieParams();

        $someCookieParams = [
            'cookie_a' => 'cookie_value',
            'cookie_b' => 'another_value',
        ];
        $newRequest = $oldRequest->withCookieParams($someCookieParams);
        $newCookieParams = $newRequest->getCookieParams();

        $this->assertEquals($someCookieParams, $newCookieParams);
        $this->assertNotEquals($oldCookieParams, $newCookieParams);
    }

    /**
     * Creates a new instance with the specified query parameters.
     */
    public function testWithQueryParams(): void
    {
        $oldRequest = new ServerRequest($this->getUriMock());
        $oldQueryParams = $oldRequest->getQueryParams();

        $someQueryParams = [
            'param_a' => 'param_value',
            'param_b' => 'another_value',
        ];
        $newRequest = $oldRequest->withQueryParams($someQueryParams);
        $newQueryParams = $newRequest->getQueryParams();

        $this->assertEquals([], $oldQueryParams);
        $this->assertEquals($someQueryParams, $newQueryParams);
        $this->assertNotEquals($oldQueryParams, $newQueryParams);
    }

    /**
     * Test for retrieving uploaded files.
     */
    public function testGetUploadedFiles(): void
    {
        $uploadedFile1 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile2 = $this->createMock(UploadedFileInterface::class);

        // Configuring expected method calls and return values
        $uploadedFile1->expects(
            $this->once()
        )->method('getClientFilename')->willReturn('filename1.txt');
        $uploadedFile1->expects(
            $this->once()
        )->method('getSize')->willReturn(12345);
        $uploadedFile2->expects(
            $this->once()
        )->method('getClientFilename')->willReturn('filename2.txt');
        $uploadedFile2->expects(
            $this->once()
        )->method('getSize')->willReturn(67890);

        $uploadedFiles = [$uploadedFile1, $uploadedFile2];

        $request = new ServerRequest(
            uri: $this->getUriMock(),
            uploadedFiles: $uploadedFiles
        );

        $actualFiles = $request->getUploadedFiles();

        $this->assertCount(2, $actualFiles);

        // Assertions to verify configured methods and return values
        $this->assertEquals(
            'filename1.txt',
            $actualFiles[0]->getClientFilename()
        );
        $this->assertEquals(12345, $actualFiles[0]->getSize());

        $this->assertEquals(
            'filename2.txt',
            $actualFiles[1]->getClientFilename()
        );
        $this->assertEquals(67890, $actualFiles[1]->getSize());
    }

    /**
     * Creates a new instance with the specified uploaded files.
     */
    public function testWithUploadedFiles(): void
    {
        $uploadedFile1 = $this->createMock(UploadedFileInterface::class);
        $uploadedFile2 = $this->createMock(UploadedFileInterface::class);

        // Configuring expected method calls and return values
        $uploadedFile1->expects(
            $this->once()
        )->method('getClientFilename')->willReturn('filename1.txt');
        $uploadedFile1->expects(
            $this->once()
        )->method('getSize')->willReturn(12345);
        $uploadedFile2->expects(
            $this->once()
        )->method('getClientFilename')->willReturn('filename2.txt');
        $uploadedFile2->expects(
            $this->once()
        )->method('getSize')->willReturn(67890);

        $uploadedFiles = [$uploadedFile1, $uploadedFile2];

        $originalRequest = new ServerRequest($this->getUriMock());

        $updatedRequest = $originalRequest->withUploadedFiles($uploadedFiles);

        $this->assertNotSame($originalRequest, $updatedRequest);

        $actualFiles = $updatedRequest->getUploadedFiles();

        $this->assertCount(2, $actualFiles);

        // Assertions to verify updated files
        $this->assertEquals(
            'filename1.txt',
            $actualFiles[0]->getClientFilename()
        );
        $this->assertEquals(12345, $actualFiles[0]->getSize());

        $this->assertEquals(
            'filename2.txt',
            $actualFiles[1]->getClientFilename()
        );
        $this->assertEquals(67890, $actualFiles[1]->getSize());
    }

    /**
     * Test for retrieving parsed body.
     */
    public function testGetParsedBody(): void
    {
        $bodyString = '{"name": "John Doe", "age": 30}';
        $parsedBody = ['name' => 'John Doe', 'age' => 30];
        $bodyStream = $this->createMock(StreamInterface::class);
        $bodyStream->expects(
            $this->once()
        )->method('__toString')->willReturn($bodyString);

        $request = new ServerRequest(
            uri: $this->getUriMock(),
            body: $bodyStream
        );

        $this->assertEquals($parsedBody, $request->getParsedBody());
    }

    /**
     * Creates a new instance with the specified parsed body.
     */
    public function testWithParsedBody(): void
    {
        // Arrange
        $request = new ServerRequest($this->getUriMock());
        $parsedBody = ['name' => 'John Doe', 'age' => 30];

        // Act
        $newRequest = $request->withParsedBody($parsedBody);

        // Assert
        $this->assertEquals($parsedBody, $newRequest->getParsedBody());
        $this->assertNotSame($request, $newRequest);
    }

    /**
     * Test for retrieving attributes.
     */
    public function testGetAttributes(): void
    {
        // Arrange
        $attributes = ['route' => 'test', 'locale' => 'en'];

        // Act (Assign attributes to the request in your implementation)
        $request = new ServerRequest(
            uri: $this->getUriMock(),
            attributes: $attributes
        );

        // Assert
        $this->assertEquals($attributes, $request->getAttributes());
    }

    /**
     * Test for retrieving an attribute.
     */
    public function testGetAttribute(): void
    {
        // Arrange
        $request = new ServerRequest(
            uri: $this->getUriMock(),
            attributes: ['route' => 'users.index', 'locale' => 'en']
        );
        $key = 'route';
        $expectedValue = 'users.index';

        // Act
        $value = $request->getAttribute($key);

        // Assert
        $this->assertEquals($expectedValue, $value);
    }

    /**
     * Creates a new instance with the specified attribute.
     */
    public function testWithAttribute(): void
    {
        // Arrange
        $request = new ServerRequest($this->getUriMock());
        $key = 'locale';
        $value = 'en-US';

        // Act
        $newRequest = $request->withAttribute($key, $value);

        // Assert
        $this->assertEquals($value, $newRequest->getAttribute($key));
        $this->assertNotSame($request, $newRequest);
    }

    /**
     * Test for removing an attribute.
     */
    public function testWithoutAttribute(): void
    {
        // Arrange
        $request = new ServerRequest(
            uri: $this->getUriMock(),
            attributes: ['locale' => 'en-US']
        );
        $key = 'locale';

        // Act
        $newRequest = $request->withoutAttribute($key);

        // Assert
        $this->assertNull($newRequest->getAttribute($key));
        $this->assertNotSame($request, $newRequest);
    }

    /**
     * Test for retrieving the request target.
     */
    public function testGetRequestTarget(): void
    {
        // Arrange
        $request = new ServerRequest($this->getUriMock());
        $expectedTarget = '/';

        // Act
        $requestTarget = $request->getRequestTarget();

        // Assert
        $this->assertEquals($expectedTarget, $requestTarget);
    }

    /**
     * Creates a new instance with the specified request target.
     */
    public function testWithRequestTarget(): void
    {
        // Arrange
        $request = new ServerRequest($this->getUriMock());
        $newTarget = '/new-target';

        // Act
        $newRequest = $request->withRequestTarget($newTarget);

        // Assert
        $this->assertEquals($newTarget, $newRequest->getRequestTarget());
        $this->assertNotSame($request, $newRequest);
    }

    /**
     * Test for retrieving the HTTP method.
     */
    public function testGetMethod(): void
    {
        // Arrange
        $request = new ServerRequest($this->getUriMock(), method: 'POST');
        $expectedMethod = 'POST';

        // Act
        $method = $request->getMethod();

        // Assert
        $this->assertEquals($expectedMethod, $method);
    }

    /**
     * Creates a new instance with the specified HTTP method.
     */
    public function testWithMethod(): void
    {
        // Arrange
        $request = new ServerRequest($this->getUriMock(), method: 'GET');
        $newMethod = 'PUT';

        // Act
        $newRequest = $request->withMethod($newMethod);

        // Assert
        $this->assertEquals($newMethod, $newRequest->getMethod());
        $this->assertNotSame($request, $newRequest);
    }

    /**
     * Test for retrieving the URI.
     */
    public function testGetUri(): void
    {
        // Arrange
        $expectedUri = $this->getUriMock();
        $request = new ServerRequest($expectedUri);

        // Act
        $uri = $request->getUri();

        // Assert
        $this->assertEquals($expectedUri, $uri);
    }

    /**
     * Creates a new instance with the specified URI.
     */
    public function testWithUri(): void
    {
        // Arrange
        $request = new ServerRequest($this->getUriMock());
        $newUri = $this->getUriMock('https://new-domain.com/path');

        // Act
        $newRequest = $request->withUri($newUri);

        // Assert
        $this->assertEquals($newUri, $newRequest->getUri());
        $this->assertNotSame($request, $newRequest);
    }

    /**
     * Test for retrieving the HTTP protocol version.
     */
    public function testGetProtocolVersion(): void
    {
        // Arrange
        $expectedVersion = '1.1';
        $request = new ServerRequest(
            uri: $this->getUriMock(),
            protocolVersion: $expectedVersion
        );

        // Act
        $protocolVersion = $request->getProtocolVersion();

        // Assert
        $this->assertEquals($expectedVersion, $protocolVersion);
    }

    /**
     * Creates a new instance with the specified HTTP protocol version.
     */
    public function testWithProtocolVersion(): void
    {
        // Arrange
        $request = new ServerRequest(
            uri: $this->getUriMock(),
            protocolVersion: '1.0'
        );
        $newVersion = '1.1';

        // Act
        $newRequest = $request->withProtocolVersion($newVersion);

        // Assert
        $this->assertEquals($newVersion, $newRequest->getProtocolVersion());
        $this->assertNotSame($request, $newRequest);
    }

    /**
     * Test for retrieving the headers.
     */
    public function testGetHeaders(): void
    {
        // Arrange
        $expectedHeaders = [
            'Authorization' => ['Bearer some-token'],
            'Content-Type' => ['application/json'],
        ];
        $request = new ServerRequest(
            uri: $this->getUriMock(),
            headers: $expectedHeaders
        );

        // Act
        $headers = $request->getHeaders();

        // Assert
        $this->assertIsArray($headers);
        foreach ($expectedHeaders as $headerName => $expectedValues) {
            $this->assertArrayHasKey($headerName, $headers);
            // Check inner array existence
            $this->assertIsArray($headers[$headerName]);
            // Compare entire inner array
            $this->assertEquals($expectedValues, $headers[$headerName]);
        }
    }

    /**
     * Test for checking if a header exists.
     */
    public function testHasHeader(): void
    {
        // Arrange
        $expectedHeaders = [
            'Authorization' => ['Bearer some-token'],
            'Content-Type' => ['application/json'],
        ];
        $request = new ServerRequest(
            uri: $this->getUriMock(),
            headers: $expectedHeaders
        );

        // Act
        $hasAuthorizationHeader = $request->hasHeader('Authorization');
        $hasNonExistentHeader = $request->hasHeader('Non-Existent-Header');

        // Assert
        $this->assertTrue($hasAuthorizationHeader);
        $this->assertFalse($hasNonExistentHeader);
    }

    /**
     * Test for retrieving a header.
     */
    public function testGetHeader(): void
    {
        // Arrange
        $expectedHeaders = [
            'Authorization' => ['Bearer some-token'],
            'Content-Type' => ['application/json; charset=utf-8'],
        ];
        $request = new ServerRequest(
            uri: $this->getUriMock(),
            headers: $expectedHeaders
        );

        // Act
        $authorizationHeader = $request->getHeader('Authorization');
        $contentTypeHeader = $request->getHeader('Content-Type');
        $nonExistentHeader = $request->getHeader('Non-Existent-Header');

        // Assert
        $this->assertEquals(['Bearer some-token'], $authorizationHeader);
        $this->assertEquals(
            ['application/json; charset=utf-8'],
            $contentTypeHeader
        );
        $this->assertEquals([], $nonExistentHeader);
    }

    /**
     * Test for retrieving a header line.
     */
    public function testGetHeaderLine(): void
    {
        // Arrange
        $expectedHeaders = [
            'Authorization' => ['Bearer some-token'],
            'Content-Type' => ['application/json; charset=utf-8'],
        ];
        $request = new ServerRequest(
            uri: $this->getUriMock(),
            headers: $expectedHeaders
        );

        // Act
        $authorizationHeader = $request->getHeaderLine('Authorization');
        $contentTypeHeader = $request->getHeaderLine('Content-Type');
        $nonExistentHeader = $request->getHeaderLine('Non-Existent-Header');

        // Assert
        $this->assertEquals('Bearer some-token', $authorizationHeader);
        // Only the first line
        $this->assertEquals(
            'application/json; charset=utf-8',
            $contentTypeHeader
        );
        // No header
        $this->assertEquals('', $nonExistentHeader);
    }

    /**
     * Creates a new instance with the specified header.
     */
    public function testWithHeader(): void
    {
        // Arrange
        $originalRequest = new ServerRequest($this->getUriMock());
        $expectedHeaderName = 'Content-Type';
        $expectedHeaderValue = ['application/xml'];

        // Act
        $newRequest = $originalRequest->withHeader(
            $expectedHeaderName,
            $expectedHeaderValue
        );

        // Assert
        $this->assertNotSame($originalRequest, $newRequest);
        $this->assertEquals(
            $expectedHeaderValue,
            $newRequest->getHeader($expectedHeaderName)
        );
    }

    /**
     * Creates a new instance with a header added.
     */
    public function testWithAddedHeader(): void
    {
        // Arrange
        $originalRequest = new ServerRequest(
            uri: $this->getUriMock(),
            headers: [
                'Content-Type' => ['application/json'],
            ]
        );
        $expectedHeaderName = 'Content-Type';
        $expectedHeaderValue = 'application/xml';

        // Act
        $newRequest = $originalRequest->withAddedHeader(
            $expectedHeaderName,
            $expectedHeaderValue
        );

        // Assert
        $this->assertNotSame($originalRequest, $newRequest);
        $this->assertEquals(
            ['application/json', $expectedHeaderValue],
            $newRequest->getHeader($expectedHeaderName)
        );
    }

    /**
     * Creates a new instance without the specified header.
     */
    public function testWithoutHeader(): void
    {
        // Arrange
        $originalRequest = new ServerRequest(
            uri: $this->getUriMock(),
            headers: [
                'Authorization' => ['Bearer some-token'],
                'Content-Type' => ['application/json'],
            ]
        );
        $expectedHeaderName = 'Content-Type';

        // Act
        $newRequest = $originalRequest->withoutHeader($expectedHeaderName);

        // Assert
        $this->assertNotSame($originalRequest, $newRequest);
        // Header removed
        $this->assertEquals([], $newRequest->getHeader($expectedHeaderName));
        // Existing header preserved
        $this->assertArrayHasKey('Authorization', $newRequest->getHeaders());
    }

    /**
     * Test for returning the body as a stream.
     */
    public function testBodyIsReturnedAsStream(): void
    {
        $resource = \fopen('php://temp', 'wb+');
        $body = new Stream($resource);
        $body->write('This is the request body');

        $request = new ServerRequest(
            uri: $this->getUriMock(),
            method: 'POST',
            body: $body
        );

        $stream = $request->getBody();

        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    /**
     * Test for returning the body as a stream and contains content.
     */
    public function testBodyIsReturnedAsStreamAndContainsContent(): void
    {
        $resource = \fopen('php://temp', 'wb+');
        $expectedContent = 'This is the request body';
        $body = new Stream($resource);
        $body->write($expectedContent);

        $request = new ServerRequest(
            uri: $this->getUriMock(),
            method: 'POST',
            body: $body
        );

        $stream = $request->getBody();

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertEquals($expectedContent, $stream);
    }

    /**
     * Creates a new instance with the specified body.
     */
    public function testWithBodyUpdatesBody(): void
    {
        $uri = $this->getUriMock();
        $content = 'This is the new request body';
        $resource = \fopen('php://temp', 'wb+');
        $stream = new Stream($resource);
        $stream->write($content);

        $originalRequest = new ServerRequest(
            uri: $uri,
            method: 'GET'
        );

        $newRequest = $originalRequest->withBody($stream);

        $this->assertInstanceOf(ServerRequest::class, $newRequest);
        $this->assertEquals($content, (string) $newRequest->getBody());
    }

    /**
     * Creates a mock object of the `UriInterface` class.
     *
     * @param string $uri The URI of the request.
     *
     * @return UriInterface A mock object of the `UriInterface` class.
     */
    private function getUriMock(string $uri = ''): UriInterface
    {
        $mock = $this->createMock(UriInterface::class);
        $mock->method('__toString')->willReturn($uri);

        return $mock;
    }
}
