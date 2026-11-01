import type { LucideIcon } from 'lucide-react';
import { Inbox, OctagonAlert, SearchX, ShieldX } from 'lucide-react';
import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';

type StateProps = {
    title?: string;
    description?: string;
    /** The next sensible step; omitted when this user has none. */
    action?: ReactNode;
};

function StateView({
    icon: Icon,
    title,
    description,
    action,
    detail,
    role,
}: {
    icon: LucideIcon;
    title: string;
    description?: string;
    action?: ReactNode;
    detail?: ReactNode;
    role?: 'alert';
}) {
    return (
        <div
            role={role}
            className="flex flex-col items-center gap-4 rounded-lg border border-border bg-card p-8 text-center shadow-card"
        >
            <div className="flex size-12 items-center justify-center rounded-full bg-canvas text-brand-950">
                <Icon className="size-6" aria-hidden="true" />
            </div>
            <div className="max-w-md space-y-1">
                <h2 className="text-lg leading-tight font-semibold text-foreground">
                    {title}
                </h2>
                {description && (
                    <p className="text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
                {detail}
            </div>
            {action}
        </div>
    );
}

export function EmptyState({ title, description, action }: StateProps) {
    const { t } = useTranslation();

    return (
        <StateView
            icon={Inbox}
            title={title ?? t('common:states.empty_title')}
            description={description}
            action={action}
        />
    );
}

export function ErrorState({
    title,
    description,
    action,
    requestId,
}: StateProps & { requestId?: string }) {
    const { t } = useTranslation();

    return (
        <StateView
            role="alert"
            icon={OctagonAlert}
            title={title ?? t('common:states.error_title')}
            description={description ?? t('common:states.error_description')}
            action={action}
            detail={
                requestId && (
                    <p className="font-mono text-xs text-muted-foreground">
                        {t('common:states.request_id', { id: requestId })}
                    </p>
                )
            }
        />
    );
}

export function ForbiddenState({ title, description, action }: StateProps) {
    const { t } = useTranslation();

    return (
        <StateView
            icon={ShieldX}
            title={title ?? t('common:states.forbidden_title')}
            description={
                description ?? t('common:states.forbidden_description')
            }
            action={action}
        />
    );
}

export function NotFoundState({ title, description, action }: StateProps) {
    const { t } = useTranslation();

    return (
        <StateView
            icon={SearchX}
            title={title ?? t('common:states.not_found_title')}
            description={
                description ?? t('common:states.not_found_description')
            }
            action={action}
        />
    );
}
