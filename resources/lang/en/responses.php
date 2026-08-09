<?php

return [
    200 => 'Request completed successfully.',
    201 => 'Resource created successfully.',
    202 => 'Request accepted and is being processed.',
    204 => 'Request completed successfully. No content returned.',
    301 => 'Resource has been moved permanently.',
    302 => 'Resource temporarily moved.',
    304 => 'Resource not modified.',
    400 => 'Invalid request. Please check the submitted data.',
    401 => 'Authentication required or token is invalid.',
    403 => 'Access denied. You do not have permission.',
    404 => 'Requested resource not found.',
    405 => 'HTTP method not allowed for this endpoint.',
    409 => 'Request conflict with existing data.',
    422 => 'Validation failed. Submitted data is invalid.',
    429 => 'Too many requests. Please try again later.',
    500 => 'Internal server error. Please try again later.',
    502 => 'Bad gateway. Invalid response from upstream server.',
    503 => 'Service temporarily unavailable.',
    504 => 'Gateway timeout. Server did not respond in time.',
];
