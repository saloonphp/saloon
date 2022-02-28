<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Data;

use Sammyjo20\Saloon\Http\SaloonResponse;

class User
{
    /**
     * @param string $name
     * @param string $actualName
     * @param string $twitter
     */
    public function __construct(
        public string $name,
        public string $actualName,
        public string $twitter,
    ) {
        //
    }

    /**
     * @param SaloonResponse $response
     * @return static
     */
    public static function fromSaloon(SaloonResponse $response): self
    {
        $data = $response->json();

        return new static($data['name'], $data['actual_name'], $data['twitter']);
    }
}
