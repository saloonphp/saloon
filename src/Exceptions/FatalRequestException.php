<?php declare(strict_types=1);

namespace Saloon\Exceptions;

use Throwable;
use Saloon\Http\PendingSaloonRequest;

class FatalRequestException extends SaloonException
{
    /**
     * The PendingSaloonRequest
     *
     * @var PendingSaloonRequest
     */
    protected PendingSaloonRequest $pendingSaloonRequest;

    /**
     * Constructor
     *
     * @param Throwable $originalException
     * @param PendingSaloonRequest $pendingRequest
     */
    public function __construct(Throwable $originalException, PendingSaloonRequest $pendingRequest)
    {
        parent::__construct($originalException->getMessage(), $originalException->getCode(), $originalException);

        $this->pendingSaloonRequest = $pendingRequest;
    }

    /**
     * Get the original exception.
     *
     * @return Throwable
     */
    public function getOriginalException(): Throwable
    {
        return $this->getPrevious();
    }

    /**
     * Get the PendingSaloonRequest that caused the exception.
     *
     * @return PendingSaloonRequest
     */
    public function getPendingSaloonRequest(): PendingSaloonRequest
    {
        return $this->pendingSaloonRequest;
    }
}
