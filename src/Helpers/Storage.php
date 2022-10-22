<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Exceptions\DirectoryNotFoundException;

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
    public function has(string $path): bool
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
        return ! $this->has($path);
    }

    /**
     * Get a path
     *
     * @param string $path
     * @return bool|string
     */
    public function get(string $path): bool|string
    {
        return file_get_contents($this->buildPath($path));
    }

    public function set(string $path, string $contents)
    {
        // Todo 
    }
}
