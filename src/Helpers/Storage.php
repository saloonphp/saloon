<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Exceptions\DirectoryNotFoundException;
use Saloon\Exceptions\UnableToCreateFileException;
use Saloon\Exceptions\UnableToCreateDirectoryException;

/**
 * @internal
 */
class Storage
{
    /**
     * The base directory to access the files.
     */
    protected string $baseDirectory;

    /**
     * Constructor
     *
     * @throws \Saloon\Exceptions\DirectoryNotFoundException
     * @throws \Saloon\Exceptions\UnableToCreateDirectoryException
     */
    public function __construct(string $baseDirectory, bool $createMissingBaseDirectory = false)
    {
        if (! is_dir($baseDirectory)) {
            $createMissingBaseDirectory ? $this->createDirectory($baseDirectory) : throw new DirectoryNotFoundException($baseDirectory);
        }

        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Get the base directory
     */
    public function getBaseDirectory(): string
    {
        return $this->baseDirectory;
    }

    /**
     * Combine the base directory with a path.
     */
    protected function buildPath(string $path): string
    {
        $trimRules = DIRECTORY_SEPARATOR . ' ';

        return rtrim($this->baseDirectory, $trimRules) . DIRECTORY_SEPARATOR . ltrim($path, $trimRules);
    }

    /**
     * Check if the file exists
     */
    public function exists(string $path): bool
    {
        return file_exists($this->buildPath($path));
    }

    /**
     * Check if the file is missing
     */
    public function missing(string $path): bool
    {
        return ! $this->exists($path);
    }

    /**
     * Retrieve an item from storage
     */
    public function get(string $path): bool|string
    {
        return file_get_contents($this->buildPath($path));
    }

    /**
     * Put an item in storage
     *
     * @return $this
     * @throws \Saloon\Exceptions\UnableToCreateDirectoryException
     * @throws \Saloon\Exceptions\UnableToCreateFileException
     */
    public function put(string $path, string $contents): static
    {
        $fullPath = $this->buildPath($path);

        $directoryWithoutFilename = implode(DIRECTORY_SEPARATOR, explode(DIRECTORY_SEPARATOR, $fullPath, -1));

        if (empty($directoryWithoutFilename) === false && is_dir($directoryWithoutFilename) === false) {
            $this->createDirectory($directoryWithoutFilename);
        }

        $createdFile = file_put_contents($fullPath, $contents);

        if ($createdFile === false) {
            throw new UnableToCreateFileException($fullPath);
        }

        return $this;
    }

    /**
     * Create a directory
     *
     * @throws \Saloon\Exceptions\UnableToCreateDirectoryException
     */
    public function createDirectory(string $directory): bool
    {
        $createdDirectory = mkdir($directory, 0777, true);

        if ($createdDirectory === false && is_dir($directory) === false) {
            throw new UnableToCreateDirectoryException($directory);
        }

        return true;
    }
}
