import type { LucideIcon } from 'lucide-react';
import {
    CircleAlert,
    CircleCheck,
    CircleX,
    Clock,
    FilePen,
    FlaskConical,
    Lock,
    PencilLine,
    ThumbsUp,
} from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { cn } from '@/lib/utils';

// DESIGN_SYSTEM section 8: a status is always text, icon and color together.
const categories = {
    draft: { icon: FilePen, className: 'bg-neutral-surface text-neutral' },
    waiting: { icon: Clock, className: 'bg-warning-surface text-warning' },
    action: { icon: CircleAlert, className: 'bg-brand-950 text-text-on-brand' },
    revision: {
        icon: PencilLine,
        className: 'bg-warning-surface text-warning',
    },
    approved: { icon: ThumbsUp, className: 'bg-success-surface text-success' },
    completed: {
        icon: CircleCheck,
        className: 'bg-success-surface text-success',
    },
    rejected: { icon: CircleX, className: 'bg-danger-surface text-danger' },
    blocked: { icon: Lock, className: 'bg-blocked-surface text-blocked' },
    simulation: {
        icon: FlaskConical,
        className: 'bg-simulation-surface text-simulation',
    },
} satisfies Record<string, { icon: LucideIcon; className: string }>;

export type StatusCategory = keyof typeof categories;

export const statusCategories = Object.keys(categories) as StatusCategory[];

type Props = {
    category: StatusCategory;
    /** The exact state label from the states catalog, when a workflow has one. */
    label?: string;
    className?: string;
};

export function StatusBadge({ category, label, className }: Props) {
    const { t } = useTranslation();
    const { icon: Icon, className: colors } = categories[category];

    return (
        <span
            className={cn(
                'inline-flex h-6 items-center gap-1 rounded-full px-2.5 text-xs font-medium',
                colors,
                className,
            )}
        >
            <Icon className="size-3.5" aria-hidden="true" />
            {label ?? t(`states:category.${category}`)}
        </span>
    );
}
