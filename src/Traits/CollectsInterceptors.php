<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsInterceptors
{
    /**
     * @var array
     */
    protected array $responseInterceptors = [];

    /**
     * @param callable $function
     * @return void
     */
    public function addResponseInterceptor(callable $function): void
    {
        $this->responseInterceptors[] = $function;
    }

    /**
     * @param array ...$interceptorsCollection
     * @return $this
     */
    public function mergeResponseInterceptors(array ...$interceptorsCollection): static
    {
        foreach ($interceptorsCollection as $responseInterceptor) {
            $this->responseInterceptors = array_merge($this->responseInterceptors, $responseInterceptor);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getResponseInterceptors(): array
    {
        return $this->responseInterceptors;
    }
}
