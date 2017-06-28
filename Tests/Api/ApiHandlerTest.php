<?php
namespace Rzeka\DataHandlerBundle\Tests\Api;

use PHPUnit\Framework\TestCase;
use Rzeka\DataHandler\DataHandler;
use Rzeka\DataHandler\DataHandlerResult;
use Rzeka\DataHandlerBundle\Api\ApiHandler;
use Rzeka\DataHandlerBundle\Exception\InvalidJsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiHandlerTest extends TestCase
{
    /**
     * @var DataHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataHandler;

    public function setUp()
    {
        $this->dataHandler = $this->createMock(DataHandler::class);
    }

    public function tearDown()
    {
        $this->dataHandler = null;
    }

    public function testParseJsonData()
    {
        $data = ['test'];
        $jsonData = json_encode($data);

        $handler = new ApiHandler($this->dataHandler);
        $result = $handler->parseJsonData($jsonData);

        static::assertEquals($data, $result);
    }

    public function testParseJsonDataInvalid()
    {
        $jsonData = '}';

        $this->expectException(InvalidJsonException::class);

        $handler = new ApiHandler($this->dataHandler);
        $handler->parseJsonData($jsonData);
    }

    public function testHandle()
    {
        $requestData = ['test'];
        $jsonData = json_encode($requestData);

        $request = $this->createMock(Request::class);
        $data = new class {};
        $options = [];

        $dataResult = $this->createMock(DataHandlerResult::class);

        $this->dataHandler
            ->expects(static::once())
            ->method('handle')
            ->with($requestData, $data, $options)
            ->willReturn($dataResult);

        $request
            ->expects(static::once())
            ->method('getContent')
            ->willReturn($jsonData);

        $handler = new ApiHandler($this->dataHandler);
        $result = $handler->handle($request, $data, $options);

        static::assertEquals($dataResult, $result);
    }

    public function testHandleWithInvalidJson()
    {
        $request = $this->createMock(Request::class);
        $request
            ->method('getContent')
            ->willReturn('[');

        $this->dataHandler
            ->expects(static::never())
            ->method('handle');

        $this->expectException(BadRequestHttpException::class);

        $handler = new ApiHandler($this->dataHandler);
        $handler->handle($request, []);
    }

    public function testHandleWithInvalidData()
    {
        $request = $this->createMock(Request::class);
        $request
            ->method('getContent')
            ->willReturn('[]');

        $this->dataHandler
            ->expects(static::once())
            ->method('handle')
            ->willThrowException(new \OutOfBoundsException());

        $this->expectException(BadRequestHttpException::class);

        $handler = new ApiHandler($this->dataHandler);
        $handler->handle($request, []);
    }

    public function testGetRequest()
    {
        $dataResult = $this->createMock(DataHandlerResult::class);
        $dataResult
            ->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $handler = new ApiHandler($this->dataHandler);
        $result = $handler->getResponseFromResult($dataResult);

        static::assertInstanceOf(Response::class, $result);
        static::assertEquals(204, $result->getStatusCode());

    }

    public function testGetRequestWithError()
    {
        $responseData = ['error'];
        $responseDataJson = json_encode($responseData);

        $dataResult = $this->createMock(DataHandlerResult::class);
        $dataResult
            ->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $dataResult
            ->expects(static::once())
            ->method('getErrors')
            ->willReturn($responseData);

        $handler = new ApiHandler($this->dataHandler);
        $result = $handler->getResponseFromResult($dataResult);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(422, $result->getStatusCode());
        static::assertEquals($responseDataJson, $result->getContent());
    }
}
