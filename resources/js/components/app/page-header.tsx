import type { ReactNode } from 'react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import type { BreadcrumbItem } from '@/types';

type Props = {
    title: string;
    description?: string;
    breadcrumbs?: BreadcrumbItem[];
    status?: ReactNode;
    /** The single primary action of the page (INTERACTION_PATTERNS section 2). */
    action?: ReactNode;
    secondaryActions?: ReactNode;
};

export function PageHeader({
    title,
    description,
    breadcrumbs = [],
    status,
    action,
    secondaryActions,
}: Props) {
    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div className="min-w-0 space-y-2">
                {breadcrumbs.length > 0 && (
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                )}
                <div className="flex flex-wrap items-center gap-3">
                    <h1 className="text-2xl leading-tight font-semibold text-foreground">
                        {title}
                    </h1>
                    {status}
                </div>
                {description && (
                    <p className="text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
            </div>
            {(action || secondaryActions) && (
                <div className="flex shrink-0 flex-wrap items-center gap-2">
                    {secondaryActions}
                    {action}
                </div>
            )}
        </div>
    );
}
