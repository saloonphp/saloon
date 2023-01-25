<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

use Saloon\Contracts\SimulatedResponsePayload;

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
    public function isSimulated(): bool
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
     * @param \Saloon\Contracts\SimulatedResponsePayload $simulatedResponsePayload
     * @return $this
     */
    public function setSimulatedResponsePayload(SimulatedResponsePayload $simulatedResponsePayload): static
    {
        $this->simulatedResponsePayload = $simulatedResponsePayload;

        return $this;
    }

    /**
     * Get the simulated response payload if the response was simulated.
     *
     * @return \Saloon\Contracts\SimulatedResponsePayload|null
     */
    public function getSimulatedResponsePayload(): ?SimulatedResponsePayload
    {
        return $this->simulatedResponsePayload;
    }
}
