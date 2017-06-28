<?php
namespace Rzeka\DataHandlerBundle\Api;

use Rzeka\DataHandler\DataHandler;
use Rzeka\DataHandler\DataHandlerResult;
use Rzeka\DataHandlerBundle\Exception\InvalidJsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiHandler
{
    /**
     * @param DataHandler $dataHandler
     */
    public function __construct(DataHandler $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * @param Request $request
     * @param $data
     * @param array $options
     *
     * @return DataHandlerResult
     * @throws BadRequestHttpException
     */
    public function handle(Request $request, $data, array $options = []): DataHandlerResult
    {
        try {
            $requestData = $this->parseJsonData($request->getContent());

            return $this->dataHandler->handle($requestData, $data, $options);
        } catch (\OutOfBoundsException $e) {
            throw new BadRequestHttpException('Data hydration failed', $e);
        } catch (InvalidJsonException $e) {
            throw new BadRequestHttpException('JSON parsing failed', $e);
        }
    }

    /**
     * @param DataHandlerResult $result
     *
     * @return Response
     */
    public function getResponseFromResult(DataHandlerResult $result): Response
    {
        if ($result->isValid()) {
            return new Response('', Response::HTTP_NO_CONTENT);
        } else {
            return new JsonResponse(
                $result->getErrors(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * @param string $jsonData
     *
     * @return array
     * @throws InvalidJsonException
     */
    public function parseJsonData(string $jsonData): array
    {
        $data = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidJsonException(sprintf(
                'Could not decode JSON: %s',
                json_last_error_msg()
            ));
        }

        return (array) $data;
    }
}
