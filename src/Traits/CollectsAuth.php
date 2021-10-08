<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsAuth
{
    protected array $auth = [];

    public function defineAuth(): array
    {
        return [];
    }

    public function setAuth(array $auth): self
    {
        $this->auth = $auth;

        return $this;
    }

    public function addAuth(string $item, $value): self
    {
        $this->auth[$item] = $value;

        return $this;
    }

    public function getAuth(string $key = null): array
    {
        if (isset($key)) {
            return $this->auth[$key];
        }

        return $this->auth;
    }
}
