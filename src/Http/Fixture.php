<?php

namespace Sammyjo20\Saloon\Http;

use GuzzleHttp\Psr7\Response;
use Sammyjo20\Saloon\Exceptions\DirectoryNotFoundException;
use Sammyjo20\Saloon\Helpers\MockConfig;
use Sammyjo20\Saloon\Helpers\Storage;
use Sammyjo20\Saloon\Tests\Fixtures\Data\FixtureData;

class Fixture
{
    /**
     * The extension used by the fixture
     *
     * @var string
     */
    public static string $fixtureExtension = 'json';

    /**
     * The name of the fixture
     *
     * @var string
     */
    protected string $name;

    /**
     * The storage helper
     *
     * @var Storage
     */
    protected Storage $storage;

    /**
     * Constructor
     *
     * @param string $name
     * @param Storage|null $storage
     * @throws DirectoryNotFoundException
     */
    public function __construct(string $name, Storage $storage = null)
    {
        $this->name = $name;
        $this->storage = $storage ?? new Storage(MockConfig::getFixturePath());
    }

    /**
     * Get the mock response from the fixture
     *
     * @return MockResponse|null
     * @throws \JsonException
     */
    public function getMockResponse(): ?MockResponse
    {
        $storage = $this->storage;
        $fixturePath = $this->getFixturePath();

        if ($storage->missing($fixturePath)) {
            return null;
        }

        return FixtureData::fromFileContents($storage->get($fixturePath))->getMockResponse();
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

    /**
     * Store a fixture
     *
     * @param FixtureData $fixtureData
     * @return $this
     */
    public function store(FixtureData $fixtureData): static
    {
        $fixturePath = $this->getFixturePath();
        $contents = $fixtureData->toFile();

        $this->storage->set($fixturePath, $contents);

        return $this;
    }
}
