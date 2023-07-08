<?php

declare(strict_types=1);

use Saloon\Http\Senders\SoapClientSender;
use Saloon\Tests\Fixtures\Requests\SoapRequest;
use Saloon\Tests\Fixtures\Connectors\SoapClientConnector;

test('the soap sender will send to the right url using the correct method', function () {
    $connector = new SoapClientConnector();
    $request = new SoapRequest(fahrenheit: 1);

    /** @var SoapClientSender $sender */
    $sender = $connector->sender();

    $pendingRequest = $connector->createPendingRequest($request);

    $connector->send($request);

    $client = $sender->getSoapClient();

    expect($client->__getLastRequestHeaders())
        ->toContain($pendingRequest->getMethod()->value)
        ->toContain(parse_url(wsdlUrl(), PHP_URL_PATH))
        ->toContain($pendingRequest->getRequest()->resolveEndpoint());
});

test('the soap sender will send all headers, query parameters and config', function () {
    $connector = new SoapClientConnector();
    $request = new SoapRequest(fahrenheit: 1);

    /** @var SoapClientSender $sender */
    $sender = $connector->sender();

    $pendingRequest = $connector->createPendingRequest($request);

    $connector->send($request);

    $client = $sender->getSoapClient();

    $headerXmlString = '';
    $index = 2;
    foreach ($sender->getSoapHeaders() as $header) {
        $tag = "ns$index:{$header->name}";
        if ($header->data) {
            $headerXmlString .= "<$tag>{$header->data}</$tag>";
        } else {
            $headerXmlString .= "<$tag/>";
        }

        $index++;
    }

    $bodyXmlString = '';
    $index = 1;
    foreach ($pendingRequest->getRequest()->query()->all() as $name => $value) {
        $tag = "ns$index:$name";
        $bodyXmlString .= "<$tag>{$value}</$tag>";
        $index++;
    }

    expect($client->__getLastRequest())
        ->toContain($headerXmlString)
        ->toContain($bodyXmlString);

    expect($client->__getLastRequestHeaders())
        ->toContain('Test');
});
