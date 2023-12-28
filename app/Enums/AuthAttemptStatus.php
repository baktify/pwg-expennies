<?php

namespace App\Enums;

enum AuthAttemptStatus
{
    case FAILED;
    case TWO_FACTOR_AUTH;
    case SUCCESS;
    case INTERNAL_SERVER_ERROR;
}
