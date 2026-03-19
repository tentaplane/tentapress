<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Models;

final class WorkflowState
{
    public const Draft = 'draft';
    public const InReview = 'in_review';
    public const ChangesRequested = 'changes_requested';
    public const Approved = 'approved';

    /**
     * @return array<int,string>
     */
    public static function all(): array
    {
        return [
            self::Draft,
            self::InReview,
            self::ChangesRequested,
            self::Approved,
        ];
    }
}
