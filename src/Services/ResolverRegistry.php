<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Services;

use InvalidArgumentException;
use PutheaKhem\ApprovalWorkflow\Contracts\ApproverResolverContract;

final class ResolverRegistry
{
    /** @var array<string,ApproverResolverContract> */
    private array $resolvers = [];

    public function register(string $type, ApproverResolverContract $resolver): void
    {
        $this->resolvers[$type] = $resolver;
    }

    public function get(string $type): ApproverResolverContract
    {
        $resolver = $this->resolvers[$type] ?? null;

        if (! $resolver instanceof ApproverResolverContract) {
            throw new InvalidArgumentException("Unknown approver resolver type [{$type}].");
        }

        return $resolver;
    }
}
