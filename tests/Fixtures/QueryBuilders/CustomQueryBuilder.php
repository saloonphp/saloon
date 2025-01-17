<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\QueryBuilders;

use Saloon\Repositories\ArrayStore;

class CustomQueryBuilder extends ArrayStore
{
    public function limit(int $limit): self
    {
        $this->add('limit', $limit);

        return $this;
    }
}
