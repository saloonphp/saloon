<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Enums\VersionMode;
use Saloon\Http\PendingRequest;

/**
 * @phpstan-ignore trait.unused
 */
trait HasApiVersion
{
    protected ?string $apiVersion = null;

    protected VersionMode $versionMode = VersionMode::Header;

    protected string $versionKey = 'api-version';

    public function setApiVersion(string $version): static
    {
        $this->apiVersion = $version;

        return $this;
    }

    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    public function bootHasApiVersion(PendingRequest $pendingRequest): void
    {
        if (! $this->apiVersion) {
            return;
        }

        match ($this->versionMode) {
            VersionMode::Header => $pendingRequest->headers()->add($this->versionKey, $this->apiVersion),

            VersionMode::QueryParam => $pendingRequest->query()->add($this->versionKey, $this->apiVersion),

            VersionMode::Subdomain,
            VersionMode::UrlPath => $pendingRequest->setUrl(
                str_replace('{version}', $this->apiVersion, $pendingRequest->getUrl())
            ),
        };
    }
}
