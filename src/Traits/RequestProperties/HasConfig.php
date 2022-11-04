<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\RequestProperties;

use Sammyjo20\Saloon\Repositories\ArrayStore;
use Sammyjo20\Saloon\Contracts\ArrayStore as ArrayStoreContract;

trait HasConfig
{
    /**
     * Request Config
     *
     * @var ArrayStoreContract
     */
    protected ArrayStoreContract $config;

    /**
     * Access the config
     *
     * @return ArrayStoreContract
     */
    public function config(): ArrayStoreContract
    {
        return $this->config ??= new ArrayStore($this->defaultConfig());
    }

    /**
     * Default Config
     *
     * @return array
     */
    protected function defaultConfig(): array
    {
        return [];
    }
}
