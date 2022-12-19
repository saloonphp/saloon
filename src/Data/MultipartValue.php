<?php

declare(strict_types=1);

namespace Saloon\Data;

use Saloon\Contracts\Arrayable;

class MultipartValue implements Arrayable
{
    /**
     * Constructor
     *
     * @param string $name
     * @param mixed $value
     * @param string|null $filename
     * @param array $headers
     */
    public function __construct(
        public string $name,
        public mixed $value,
        public ?string $filename = null,
        public array $headers = []
    ) {
        //
    }

    /**
     * Convert the instance to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'contents' => $this->value,
            'filename' => $this->filename,
            'headers' => $this->headers,
        ];
    }
}
