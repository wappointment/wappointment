<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/** A failure that another attempt cannot fix (missing data, disabled feature, unsupported transport). */
class PermanentJobFailure extends \RuntimeException {}
