<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsInterceptors
{
    protected array $responseInterceptors = [];

    public function addResponseInterceptor(callable $function): void
    {
        $this->responseInterceptors[] = $function;
    }

    public function mergeResponseInterceptors(array ...$interceptorsCollection): self
    {
        foreach ($interceptorsCollection as $responseInterceptor) {
            $this->responseInterceptors = array_merge($this->responseInterceptors, $responseInterceptor);
        }

        return $this;
    }

    public function getResponseInterceptors(): array
    {
        return $this->responseInterceptors;
    }
}
