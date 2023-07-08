<?php

declare(strict_types=1);

namespace Saloon\Http\Senders;

use Exception;
use SoapFault;
use SoapClient;
use SoapHeader;
use Saloon\Contracts\Sender;
use Saloon\Contracts\Response;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Responses\SoapResponse;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Exceptions\Request\FatalRequestException;

class SoapClientSender implements Sender
{
    /**
     * The Soap client.
     */
    public SoapClient $client;

    public array $headers;

    /**
     * @throws FatalRequestException
     */
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): Response
    {
        try {
            $this->createRequestHeaders(pendingRequest: $pendingRequest);

            $this->client ??= new SoapClient(
                wsdl: $pendingRequest->getConnector()->resolveBaseUrl(),
                options: $pendingRequest->config()->add('trace', 1)->all()
            );

            $this->client->__setSoapHeaders($this->headers);

            $response = $this->client->__soapCall($pendingRequest->getRequest()->resolveEndpoint(), [$pendingRequest->query()->all()]);

            return $this->createResponse(pendingSaloonRequest: $pendingRequest, response: $response);
        } catch (SoapFault $exception) {
            // A SoapFault exception will be thrown if an error occurs and
            // the SoapClient was constructed with the exceptions option not set, or set to TRUE.
            throw new FatalRequestException(originalException: $exception, pendingRequest:  $pendingRequest);

        } catch (Exception $exception) {
            return $this->createResponse(
                pendingSaloonRequest: $pendingRequest,
                response: null,
                exception: $exception
            );
        }
    }

    /**
     * Build up all the request headers
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return array<SoapHeader>
     */
    protected function createRequestHeaders(PendingRequest $pendingRequest): void
    {
        $headers = [];
        foreach ($pendingRequest->headers()->all() as $namespace => $value) {
            if ($value instanceof SoapHeader) {
                $headers[] = $value;
            } elseif (is_array($value)) {
                $headers[] = new SoapHeader(
                    namespace: $namespace,
                    name: ! empty($value[0]) ? $value[0] : '',
                    data:! empty($value[1]) ? $value[1] : null,
                    mustUnderstand: ! empty($value[2]) ? $value[2] : false,
                    actor:! empty($value[3]) ? $value[3] : null
                );
            } else {
                $headers[] = new SoapHeader(namespace: $namespace, name: $value);
            }
        }

        $this->headers = $headers;
    }

    /**
     * Create a response.
     */
    protected function createResponse(PendingRequest $pendingSaloonRequest, mixed $response, Exception $exception = null): ResponseContract
    {
        return new SoapResponse(
            client: $this->client,
            response: $response,
            pendingRequest: $pendingSaloonRequest,
            senderException: $exception
        );
    }


    /**
     * Get the Soap client
     *
     * @return \SoapClient
     */
    public function getSoapClient(): SoapClient
    {
        return $this->client;
    }

    /**
     * Get the Soap headers
     *
     * @return array<SoapHeader>
     */
    public function getSoapHeaders(): array
    {
        return $this->headers;
    }
}
