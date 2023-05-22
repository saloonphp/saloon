<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

use Saloon\Contracts\FakeResponse;

trait HasSimulationMethods
{
    /**
     * Check if the response has been cached
     *
     * @return bool
     */
    public function isCached(): bool
    {
        return $this->cached;
    }

    /**
     * Check if the response has been mocked
     *
     * @return bool
     */
    public function isMocked(): bool
    {
        return $this->mocked;
    }

    /**
     * Check if the response has been simulated
     *
     * @return bool
     */
    public function isFaked(): bool
    {
        return $this->isMocked() || $this->isCached();
    }

    /**
     * Set if a response has been cached or not.
     *
     * @param bool $value
     * @return $this
     */
    public function setCached(bool $value): static
    {
        $this->cached = true;

        return $this;
    }

    /**
     * Set if a response has been mocked or not.
     *
     * @param bool $value
     * @return $this
     */
    public function setMocked(bool $value): static
    {
        $this->mocked = true;

        return $this;
    }

    /**
     * Set the simulated response payload if the response was simulated.
     *
     * @param \Saloon\Contracts\FakeResponse $fakeResponse
     * @return $this
     */
    public function setFakeResponse(FakeResponse $fakeResponse): static
    {
        $this->fakeResponse = $fakeResponse;

        return $this;
    }

    /**
     * Get the simulated response payload if the response was simulated.
     *
     * @return \Saloon\Contracts\FakeResponse|null
     */
    public function getFakeResponse(): ?FakeResponse
    {
        return $this->fakeResponse;
    }
}
