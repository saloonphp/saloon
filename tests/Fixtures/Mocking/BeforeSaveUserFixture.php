<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;
use Saloon\Data\RecordedResponse;

class BeforeSaveUserFixture extends Fixture
{
    /**
     * Define the name of the fixture
     *
     * @return string
     */
    protected function defineName(): string
    {
        return 'user';
    }

    /**
     * Modify the fixture before it is sent
     *
     * @param \Saloon\Data\RecordedResponse $recordedResponse
     * @return \Saloon\Data\RecordedResponse
     */
    protected function beforeSave(RecordedResponse $recordedResponse): RecordedResponse
    {
        $recordedResponse->statusCode = 222;

        return $recordedResponse;
    }
}
