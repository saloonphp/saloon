<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use InvalidArgumentException;
use Saloon\Enums\VersionMode;
use Saloon\Http\PendingRequest;

/**
 * @phpstan-ignore trait.unused
 */
trait HasApiVersion
{
    /**
     * API Version
     */
    protected ?string $apiVersion = null;

    /**
     * Where the API version should be applied to the request
     */
    protected VersionMode $versionMode = VersionMode::Header;

    /**
     * The header or query parameter name used for the API version
     */
    protected string $versionKey = 'api-version';

    /**
     * Set the API version
     *
     * @return $this
     */
    public function setApiVersion(string $version): static
    {
        $this->apiVersion = $version;

        return $this;
    }

    /**
     * Get the API version
     */
    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    /**
     * Boot HasApiVersion plugin.
     */
    public function bootHasApiVersion(PendingRequest $pendingRequest): void
    {
        if (! $this->apiVersion) {
            return;
        }

        match ($this->versionMode) {
            VersionMode::Header => $pendingRequest->headers()->add($this->versionKey, $this->apiVersion),
            VersionMode::QueryParam => $pendingRequest->query()->add($this->versionKey, $this->apiVersion),
            VersionMode::Url => $pendingRequest->setUrl(
                str_replace('{version}', $this->ensureVersionIsSafeForUrl($this->apiVersion), $pendingRequest->getUrl())
            ),
        };
    }

    /**
     * Ensure the version can only ever form a single subdomain label or path segment.
     *
     * @throws InvalidArgumentException
     */
    protected function ensureVersionIsSafeForUrl(string $version): string
    {
        if (preg_match('/^[A-Za-z0-9_-]+(\.[A-Za-z0-9_-]+)*$/', $version) !== 1) {
            throw new InvalidArgumentException(sprintf('The API version "%s" is not safe to use in a URL. Versions may only contain letters, numbers, dashes, underscores and single dots.', $version));
        }

        return $version;
    }
}
