<?php

namespace Saloon\Enums;

enum DefaultMiddleware: string
{
    case AUTHENTICATE_REQUEST = 'authenticate_request';
    case DETERMINE_MOCK_RESPONSE = 'determine_mock_response';
    case RECORD_FIXTURE = 'record_fixture';
}
