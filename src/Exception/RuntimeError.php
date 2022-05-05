<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Exception;

use RuntimeException;

class RuntimeError extends RuntimeException implements ConvertKitError
{
}
