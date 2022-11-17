<?php declare(strict_types=1);

namespace Saloon\Data;

class MergeOptions
{
    /**
     * @var bool
     */
    protected bool $includeConnectorHeaders = true;

    /**
     * @var bool
     */
    protected bool $includeConnectorQueryParameters = true;

    /**
     * @var bool
     */
    protected bool $includeConnectorConfig = true;

    /**
     * @var bool
     */
    protected bool $includeConnectorBody = true;

    /**
     * @var bool
     */
    protected bool $includeConnectorMiddleware = true;


    /**
     * @return $this
     */
    public function withConnectorHeaders(): static
    {
        $this->includeConnectorHeaders = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutConnectorHeaders(): static
    {
        $this->includeConnectorHeaders = false;

        return $this;
    }

    /**
     * Check if we should include connector headers
     *
     * @return bool
     */
    public function includesConnectorHeaders(): bool
    {
        return $this->includeConnectorHeaders;
    }

    // Todo: Write other merge options
}
