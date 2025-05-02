<?php

declare(strict_types=1);

namespace Saloon\Http\Faking;

use Saloon\MockConfig;
use Saloon\Helpers\Storage;
use Saloon\Helpers\ArrayHelpers;
use Saloon\Data\RecordedResponse;
use Saloon\Helpers\FixtureHelper;
use Saloon\Repositories\ArrayStore;
use Saloon\Exceptions\FixtureException;
use Saloon\Exceptions\FixtureMissingException;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

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
     * The context of the fixture
     */
    protected ArrayStoreContract $context;

    /**
     * Data to merge in the mocked response.
     *
     * @var array<array-key, mixed>|null
     */
    protected ?array $merge = null;

    /**
     * Closure to modify the returned data with.
     */
    protected ?\Closure $through = null;

    /**
     * Constructor
     */
    public function __construct(string $name = '', ?Storage $storage = null, ?ArrayStoreContract $context = null)
    {
        $this->name = $name;
        $this->storage = $storage ?? new Storage(MockConfig::getFixturePath(), true);
        $this->context = $context ?? new ArrayStore();
    }

    /**
     * Specify data to merge with the mock response data.
     *
     * @param array<array-key, mixed> $merge
     */
    public function merge(array $merge = []): static
    {
        $this->merge = $merge;

        return $this;
    }

    /**
     * Specify a closure to modify the mock response data with.
     */
    public function through(\Closure $through): static
    {
        $this->through = $through;

        return $this;
    }

    /**
     * Attempt to get the mock response from the fixture.
     */
    public function getMockResponse(): ?MockResponse
    {
        $storage = $this->storage;
        $fixturePath = $this->getFixturePath();

        if ($storage->exists($fixturePath)) {
            $response = RecordedResponse::fromFile($storage->get($fixturePath))->toMockResponse();

            if (is_null($this->merge) && is_null($this->through)) {
                return $response;
            }

            // First, we get the body as an array. If we're dealing with
            // a `StringBodyRepository`, we have to encode it first.
            if (! is_array($body = $response->body()->all())) {
                $body = json_decode($body ?: '[]', associative: true, flags: \JSON_THROW_ON_ERROR);
            }

            // We can then merge the data in the body usingthrough
            // the ArrayHelpers for dot-notation support.
            if (is_array($this->merge)) {
                foreach ($this->merge as $key => $value) {
                    ArrayHelpers::set($body, $key, $value);
                }
            }

            // If specified, we pass the body through a function that
            // may modify the mock response data.
            if (! is_null($this->through)) {
                $body = call_user_func($this->through, $body);
            }

            // We then set the mutated data back in the repository. If we're dealing
            // with a `StringBodyRepository`, we need to encode it back to string.
            $response->body()->set(
                $response->body() instanceof StringBodyRepository
                    ? json_encode($body)
                    : $body
            );

            return $response;
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
        $recordedResponse->context = $this->context->merge($recordedResponse->context)->all();

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

    /**
     * Get a specific context value or return the entire context
     *
     * @return ($key is null ? ArrayStoreContract : mixed)
     */
    public function getContext(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->context;
        }

        return $this->context->get($key);
    }

    /**
     * Set a specific context value
     */
    public function setContext(string $key, mixed $value): static
    {
        $this->context->add($key, $value);

        return $this;
    }

    /**
     * Merge context values into the fixture
     * @param array<string, mixed> $context
     */
    public function withContext(array $context): static
    {
        $this->context->merge($context);

        return $this;
    }
}
