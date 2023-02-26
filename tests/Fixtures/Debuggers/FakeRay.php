<?php

namespace Saloon\Tests\Fixtures\Debuggers;

use Spatie\Ray\Client;
use Spatie\Ray\Request;

class FakeRay extends Client
{
    public function serverIsAvailable(): bool
    {
        return true;
    }

    public function changePortAndReturnOriginal(int $newPortNumber): int
    {
        $result = $this->portNumber;

        $this->portNumber = $newPortNumber;

        return $result;
    }

    public function send(Request $request): void
    {
        $requestProperties = $request->toArray();

        $this->sentRequests[] = $requestProperties;

        foreach ($requestProperties['payloads'] as &$payload) {
            $payload['origin']['file'] = $this->convertToRelativeFilename($payload['origin']['file']);

            if (isset($payload['content']['values']) && isset($payload['content']['values'][0])) {
                if (! is_bool($payload['content']['values'][0])) {
                    $payload['content']['values'] = preg_replace('/sf-dump-[0-9]{1,10}/', 'sf-dump-xxxxxxxxxx', $payload['content']['values']);
                }
            }

            if (isset($payload['content']['frames'])) {
                foreach ($payload['content']['frames'] as &$frame) {
                    $frame['file_name'] = $this->convertToUnixPath($this->convertToRelativeFilename($frame['file_name']));
                    $frame['line_number'] = 'xxx';
                    $frame['snippet'] = [];
                }
            }

            $payload['origin']['file'] = $this->convertToUnixPath($payload['origin']['file']);
            $payload['origin']['line_number'] = 'xxx';
        }

        $requestProperties['meta'] = [];

        $this->sentPayloads[] = $requestProperties;
    }

    public function sentPayloads(): array
    {
        return $this->sentPayloads;
    }

    public function sentRequests(): array
    {
        return $this->sentRequests;
    }

    public function reset(): self
    {
        $this->sentPayloads = [];

        return $this;
    }

    protected function baseDirectory(): string
    {
        return str_replace("/tests/TestClasses", '', __DIR__);
    }

    protected function convertToUnixPath(string $path): string
    {
        $path = str_replace('D:\a\ray\ray', '', $path);

        return str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }

    protected function convertToRelativeFilename(string $filename): string
    {
        return str_replace($this->baseDirectory(), '', $filename);
    }
}
