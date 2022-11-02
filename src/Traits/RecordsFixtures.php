<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits;

trait RecordsFixtures
{
    /**
     * Denotes if the request is being used to record a fixture.
     *
     * @var bool
     */
    protected bool $isRecordingFixture = false;

    /**
     * Set if the request is being used to record a fixture.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsRecordingFixture(bool $value): static
    {
        $this->isRecordingFixture = $value;

        return $this;
    }

    /**
     * Get if the request is recording a fixture.
     *
     * @return bool
     */
    public function isRecordingFixture(): bool
    {
        return $this->isRecordingFixture;
    }

    /**
     * Get if the request is not recording a fixture.
     *
     * @return bool
     */
    public function isNotRecordingFixture(): bool
    {
        return ! $this->isRecordingFixture();
    }
}
