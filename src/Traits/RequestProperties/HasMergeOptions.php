<?php

namespace Saloon\Traits\RequestProperties;

use Saloon\Data\MergeOptions;

trait HasMergeOptions
{
    /**
     * Merge Options
     *
     * @var MergeOptions
     */
    protected MergeOptions $mergeOptions;

    /**
     * Manage Merge Options
     *
     * When a PendingRequest is created the MergeOptions will be used to determine
     * which properties will be merged from the connector.
     *
     * @return MergeOptions
     */
    public function mergeOptions(): MergeOptions
    {
        return $this->mergeOptions ??= new MergeOptions;
    }
}
