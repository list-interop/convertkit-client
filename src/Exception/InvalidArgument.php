<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Exception;

use InvalidArgumentException;

class InvalidArgument extends InvalidArgumentException implements ConvertKitError
{
}
