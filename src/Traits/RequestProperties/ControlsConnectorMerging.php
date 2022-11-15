<?php

namespace Saloon\Traits\RequestProperties;

use Saloon\Data\MergeOptions;

trait ControlsConnectorMerging
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
     * @return MergeOptions
     */
    public function mergeOptions(): MergeOptions
    {
        return $this->mergeOptions ??= new MergeOptions;
    }
}
