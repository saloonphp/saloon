<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Exceptions\DirectoryNotFoundException;
use Sammyjo20\Saloon\Exceptions\UnableToCreateFileException;
use Sammyjo20\Saloon\Exceptions\UnableToCreateDirectoryException;

class Storage
{
    /**
     * The base directory to access the files.
     *
     * @var string
     */
    public string $baseDirectory;

    /**
     * Constructor
     *
     * @param string $baseDirectory
     * @throws DirectoryNotFoundException
     */
    public function __construct(string $baseDirectory)
    {
        if (! is_dir($baseDirectory)) {
            throw new DirectoryNotFoundException($baseDirectory);
        }

        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Get the base directory
     *
     * @return string
     */
    public function getBaseDirectory(): string
    {
        return $this->baseDirectory;
    }

    /**
     * Combine the base directory with a path.
     *
     * @param string $path
     * @return string
     */
    protected function buildPath(string $path): string
    {
        $trimRules = DIRECTORY_SEPARATOR . ' ';

        return rtrim($this->baseDirectory, $trimRules) . DIRECTORY_SEPARATOR . ltrim($path, $trimRules);
    }

    /**
     * Check if the file exists
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return file_exists($this->buildPath($path));
    }

    /**
     * Check if the file is missing
     *
     * @param string $path
     * @return bool
     */
    public function missing(string $path): bool
    {
        return ! $this->exists($path);
    }

    /**
     * Retrieve an item from storage
     *
     * @param string $path
     * @return bool|string
     */
    public function get(string $path): bool|string
    {
        return file_get_contents($this->buildPath($path));
    }

    /**
     * Put an item in storage
     *
     * @param string $path
     * @param string $contents
     * @return $this
     * @throws UnableToCreateDirectoryException
     * @throws UnableToCreateFileException
     */
    public function put(string $path, string $contents): static
    {
        $fullPath = $this->buildPath($path);

        $directoryWithoutFilename = implode(DIRECTORY_SEPARATOR, explode(DIRECTORY_SEPARATOR, $fullPath, -1));

        if (empty($directoryWithoutFilename) === false && is_dir($directoryWithoutFilename) === false) {
            $createdDirectory = mkdir($directoryWithoutFilename, 0777, true);

            if ($createdDirectory === false && is_dir($directoryWithoutFilename) === false) {
                throw new UnableToCreateDirectoryException($directoryWithoutFilename);
            }
        }

        $createdFile = file_put_contents($fullPath, $contents);

        if ($createdFile === false) {
            throw new UnableToCreateFileException($fullPath);
        }

        return $this;
    }
}
