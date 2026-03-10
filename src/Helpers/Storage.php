<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use InvalidArgumentException;
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
     * Normalize a path by resolving . and .. segments (no filesystem access).
     */
    protected function normalizePath(string $path): string
    {
        $leadingSlash = $path !== '' && $path[0] === DIRECTORY_SEPARATOR;
        $leadingDrive = mb_strlen($path) >= 2 && $path[1] === ':';

        $segments = [];
        foreach (preg_split('#[/\\\\]+#', $path, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $segment) {
            if ($segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        $result = implode(DIRECTORY_SEPARATOR, $segments);
        if ($leadingSlash && $result !== '') {
            $result = DIRECTORY_SEPARATOR . $result;
        }
        if ($leadingDrive && $result !== '' && ! preg_match('#^[a-zA-Z]:#', $result)) {
            $result = $path[0] . ':' . $result;
        }

        return $result;
    }

    /**
     * Ensure the resolved path is under the base directory to prevent path traversal.
     *
     * @throws InvalidArgumentException
     */
    protected function ensurePathUnderBase(string $fullPath): void
    {
        $baseReal = realpath($this->baseDirectory);

        if ($baseReal === false) {
            throw new InvalidArgumentException('Unable to determine the realpath of the base directory.');
        }

        if (str_contains($fullPath, '~')) {
            throw new InvalidArgumentException('Path must remain inside the storage base directory.');
        }

        $baseTrimmed = rtrim($this->baseDirectory, DIRECTORY_SEPARATOR . ' ');
        $baseNorm = $this->normalizePath($baseTrimmed);
        $fullNorm = $this->normalizePath($fullPath);
        $baseWithSep = $baseNorm . DIRECTORY_SEPARATOR;

        if ($baseTrimmed !== '' && $fullNorm !== $baseNorm && ! str_starts_with($fullNorm, $baseWithSep)) {
            throw new InvalidArgumentException('Path must remain inside the storage base directory.');
        }

        $pathSuffix = $baseTrimmed === '' ? $fullPath : ($fullNorm === $baseNorm ? '' : mb_substr($fullNorm, mb_strlen($baseWithSep)));
        $normalizedAbsolute = $this->normalizePath($baseReal . DIRECTORY_SEPARATOR . $pathSuffix);

        $baseWithSeparator = $baseReal . DIRECTORY_SEPARATOR;
        if ($normalizedAbsolute !== $baseReal && ! str_starts_with($normalizedAbsolute, $baseWithSeparator)) {
            throw new InvalidArgumentException('Path must remain inside the storage base directory.');
        }
    }

    /**
     * Check if the file exists
     */
    public function exists(string $path): bool
    {
        $fullPath = $this->buildPath($path);
        $this->ensurePathUnderBase($fullPath);

        return file_exists($fullPath);
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
        $fullPath = $this->buildPath($path);
        $this->ensurePathUnderBase($fullPath);

        return file_get_contents($fullPath);
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
        $this->ensurePathUnderBase($fullPath);

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
