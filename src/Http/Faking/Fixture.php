<?php

declare(strict_types=1);

namespace Saloon\Http\Faking;

use Saloon\MockConfig;
use Saloon\Helpers\Storage;
use Saloon\Data\RecordedResponse;
use Saloon\Helpers\FixtureHelper;
use Saloon\Exceptions\FixtureException;
use Saloon\Exceptions\FixtureMissingException;

class Fixture
{
    /**
     * The extension used by the fixture
     */
    protected static string $fixtureExtension = 'json';

    /**
     * The name of the fixture
     */
    protected string $name = '';

    /**
     * The storage helper
     */
    protected Storage $storage;

    /**
     * Constructor
     */
    public function __construct(string $name = '', Storage $storage = null)
    {
        $this->name = $name;
        $this->storage = $storage ?? new Storage(MockConfig::getFixturePath(), true);
    }

    /**
     * Attempt to get the mock response from the fixture.
     */
    public function getMockResponse(): ?MockResponse
    {
        $storage = $this->storage;
        $fixturePath = $this->getFixturePath();

        if ($storage->exists($fixturePath)) {
            return RecordedResponse::fromFile($storage->get($fixturePath))->toMockResponse();
        }

        if (MockConfig::isThrowingOnMissingFixtures() === true) {
            throw new FixtureMissingException($fixturePath);
        }

        return null;
    }

    /**
     * Store data as the fixture.
     *
     * @return $this
     */
    public function store(RecordedResponse $recordedResponse): static
    {
        $recordedResponse = $this->swapSensitiveHeaders($recordedResponse);
        $recordedResponse = $this->swapSensitiveJson($recordedResponse);
        $recordedResponse = $this->swapSensitiveBodyWithRegex($recordedResponse);
        $recordedResponse = $this->beforeSave($recordedResponse);

        $this->storage->put($this->getFixturePath(), $recordedResponse->toFile());

        return $this;
    }

    /**
     * Get the fixture path
     *
     * @throws \Saloon\Exceptions\FixtureException
     */
    public function getFixturePath(): string
    {
        $name = $this->name;

        if (empty($name)) {
            $name = $this->defineName();
        }

        if (empty($name)) {
            throw new FixtureException('The fixture must have a name');
        }

        return sprintf('%s.%s', $name, $this::$fixtureExtension);
    }

    /**
     * Define the fixture name
     */
    protected function defineName(): string
    {
        return '';
    }

    /**
     * Swap any sensitive headers
     */
    protected function swapSensitiveHeaders(RecordedResponse $recordedResponse): RecordedResponse
    {
        $sensitiveHeaders = $this->defineSensitiveHeaders();

        if (empty($sensitiveHeaders)) {
            return $recordedResponse;
        }

        $recordedResponse->headers = FixtureHelper::recursivelyReplaceAttributes($recordedResponse->headers, $sensitiveHeaders, false);

        return $recordedResponse;
    }

    /**
     * Swap any sensitive JSON data
     *
     * @throws \JsonException
     */
    protected function swapSensitiveJson(RecordedResponse $recordedResponse): RecordedResponse
    {
        $body = json_decode($recordedResponse->data, true);

        if (empty($body) || json_last_error() !== JSON_ERROR_NONE) {
            return $recordedResponse;
        }

        $sensitiveJsonParameters = $this->defineSensitiveJsonParameters();

        if (empty($sensitiveJsonParameters)) {
            return $recordedResponse;
        }

        $redactedData = FixtureHelper::recursivelyReplaceAttributes($body, $sensitiveJsonParameters);

        $recordedResponse->data = json_encode($redactedData, JSON_THROW_ON_ERROR);

        return $recordedResponse;
    }

    /**
     * Swap sensitive body with regex patterns
     */
    protected function swapSensitiveBodyWithRegex(RecordedResponse $recordedResponse): RecordedResponse
    {
        $sensitiveRegexPatterns = $this->defineSensitiveRegexPatterns();

        if (empty($sensitiveRegexPatterns)) {
            return $recordedResponse;
        }

        $redactedData = FixtureHelper::replaceSensitiveRegexPatterns($recordedResponse->data, $sensitiveRegexPatterns);

        $recordedResponse->data = $redactedData;

        return $recordedResponse;
    }

    /**
     * Swap any sensitive headers
     *
     * @return array<string, string|callable>
     */
    protected function defineSensitiveHeaders(): array
    {
        return [];
    }

    /**
     * Swap any sensitive JSON parameters
     *
     * @return array<string, string|callable>
     */
    protected function defineSensitiveJsonParameters(): array
    {
        return [];
    }

    /**
     * Define regex patterns that should be replaced
     *
     * @return array<string, string>
     */
    protected function defineSensitiveRegexPatterns(): array
    {
        return [];
    }

    /**
     * Hook to use before saving
     */
    protected function beforeSave(RecordedResponse $recordedResponse): RecordedResponse
    {
        return $recordedResponse;
    }
}
