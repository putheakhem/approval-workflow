<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Exceptions;

use RuntimeException;

/**
 * Base exception for approval workflow errors.
 */
abstract class WorkflowException extends RuntimeException {}
