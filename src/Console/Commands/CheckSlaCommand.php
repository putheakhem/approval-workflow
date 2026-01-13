<?php

declare(strict_types=1);

namespace PutheaKhem\ApprovalWorkflow\Console\Commands;

use Illuminate\Console\Command;
use PutheaKhem\ApprovalWorkflow\Services\SlaService;

final class CheckSlaCommand extends Command
{
    protected $signature = 'workflow:check-sla {--limit= : Max overdue tasks to process this run}';

    protected $description = 'Check overdue workflow tasks (SLA) and emit sla_breached events.';

    public function handle(SlaService $sla): int
    {
        $limit = $this->option('limit');
        $limit = is_numeric($limit) ? (int) $limit : null;

        $count = $sla->checkAndRecordBreaches($limit);

        $this->info("SLA breaches recorded: {$count}");

        return self::SUCCESS;
    }
}
