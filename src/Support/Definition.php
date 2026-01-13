<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Support;

use InvalidArgumentException;

final class Definition
{
    /**
     * @param  array<string,mixed>  $definition
     * @return array<int,array<string,mixed>>
     */
    public static function steps(array $definition): array
    {
        $steps = $definition['steps'] ?? null;

        if (! is_array($steps)) {
            throw new InvalidArgumentException('Workflow definition missing "steps" array.');
        }

        return $steps;
    }

    /**
     * @param  array<string,mixed>  $definition
     */
    public static function findStep(array $definition, string $key): ?array
    {
        foreach (self::steps($definition) as $step) {
            if (($step['key'] ?? null) === $key) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Determine the first step key.
     * Preference: step.type == "start", else first element.
     *
     * @param  array<string,mixed>  $definition
     */
    public static function firstStepKey(array $definition): string
    {
        foreach (self::steps($definition) as $step) {
            if (($step['type'] ?? null) === 'start') {
                return (string) ($step['key'] ?? '');
            }
        }

        $first = self::steps($definition)[0] ?? null;

        return (string) ($first['key'] ?? '');
    }

    /**
     * Find "next" for a step.
     *
     * @param  array<string,mixed>  $step
     */
    public static function nextKey(array $step): ?string
    {
        $next = $step['next'] ?? null;

        return is_string($next) && $next !== '' ? $next : null;
    }

    /**
     * @param  array<string,mixed>  $definition
     * @return array<int,array<string,mixed>>
     */
    public static function transitions(array $definition): array
    {
        $t = $definition['transitions'] ?? [];

        return is_array($t) ? $t : [];
    }

    /**
     * Find override transition by (from step_key + action)
     *
     * @param  array<string,mixed>  $definition
     */
    public static function transitionTo(array $definition, string $from, string $on): ?string
    {
        foreach (self::transitions($definition) as $t) {
            if (($t['from'] ?? null) === $from && ($t['on'] ?? null) === $on) {
                $to = $t['to'] ?? null;

                return is_string($to) && $to !== '' ? $to : null;
            }
        }

        return null;
    }

    /**
     * Check if a step is an end step.
     *
     * @param  array<string,mixed>  $definition
     */
    public static function isEndStep(array $definition, string $stepKey): bool
    {
        $step = self::findStep($definition, $stepKey);

        return is_array($step) && (($step['type'] ?? null) === 'end');
    }
}
