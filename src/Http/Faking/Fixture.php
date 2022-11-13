<?php declare(strict_types=1);

namespace Saloon\Http\Faking;

use Saloon\Helpers\Storage;
use Saloon\Data\FixtureData;
use Saloon\Helpers\MockConfig;
use Saloon\Exceptions\FixtureMissingException;
use Saloon\Exceptions\DirectoryNotFoundException;

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
     * Attempt to get the mock response from the fixture.
     *
     * @return MockResponse|null
     * @throws FixtureMissingException
     * @throws \JsonException
     */
    public function getMockResponse(): ?MockResponse
    {
        $storage = $this->storage;
        $fixturePath = $this->getFixturePath();

        if ($storage->exists($fixturePath)) {
            return FixtureData::fromFile($storage->get($fixturePath))->toMockResponse();
        }

        if (MockConfig::isThrowingOnMissingFixtures() === true) {
            throw new FixtureMissingException($fixturePath);
        }

        return null;
    }

    /**
     * Store data as the fixture.
     *
     * @param FixtureData $fixtureData
     * @return $this
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\UnableToCreateDirectoryException
     * @throws \Sammyjo20\Saloon\Exceptions\UnableToCreateFileException
     */
    public function store(FixtureData $fixtureData): static
    {
        $fixturePath = $this->getFixturePath();
        $contents = $fixtureData->toFile();

        $this->storage->put($fixturePath, $contents);

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
