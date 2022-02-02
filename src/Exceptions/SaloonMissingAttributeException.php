<?php

namespace Sammyjo20\Saloon\Exceptions;

use \Exception;
use Sammyjo20\Saloon\Http\SaloonRequest;

class SaloonMissingAttributeException extends SaloonException
{
    /**
     * Throw an exception if we are missing a required attribute.
     *
     * @param SaloonRequest $request
     * @param string $attribute
     */
    public function __construct(SaloonRequest $request, string $attribute)
    {
        $message = sprintf('Missing "%s" request attribute on the "%s" request class.', $attribute, get_class($request));

        parent::__construct($message);
    }
}
