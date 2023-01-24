<?php

declare(strict_types=1);

namespace Saloon\Http\Faking;

use Saloon\Helpers\Storage;
use Saloon\Helpers\MockConfig;
use Saloon\Data\RecordedResponse;
use Saloon\Exceptions\FixtureMissingException;

class Fixture
{
    /**
     * The extension used by the fixture
     *
     * @var string
     */
    protected static string $fixtureExtension = 'json';

    /**
     * The name of the fixture
     *
     * @var string
     */
    protected string $name;

    /**
     * The storage helper
     *
     * @var \Saloon\Helpers\Storage
     */
    protected Storage $storage;

    /**
     * Constructor
     *
     * @param string $name
     * @param \Saloon\Helpers\Storage|null $storage
     * @throws \Saloon\Exceptions\DirectoryNotFoundException
     * @throws \Saloon\Exceptions\UnableToCreateDirectoryException
     */
    public function __construct(string $name, Storage $storage = null)
    {
        $this->name = $name;
        $this->storage = $storage ?? new Storage(MockConfig::getFixturePath(), true);
    }

    /**
     * Attempt to get the mock response from the fixture.
     *
     * @return \Saloon\Http\Faking\MockResponse|null
     * @throws \Saloon\Exceptions\FixtureMissingException
     * @throws \JsonException
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
     * @param \Saloon\Data\RecordedResponse $recordedResponse
     * @return $this
     * @throws \JsonException
     * @throws \Saloon\Exceptions\UnableToCreateDirectoryException
     * @throws \Saloon\Exceptions\UnableToCreateFileException
     */
    public function store(RecordedResponse $recordedResponse): static
    {
        $this->storage->put($this->getFixturePath(), $recordedResponse->toFile());

        return $this;
    }

    /**
     * Get the fixture path
     *
     * @return string
     */
    public function getFixturePath(): string
    {
        return sprintf('%s.%s', $this->name, $this::$fixtureExtension);
    }
}
