import { Link, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCurrentUrl } from '@/hooks/use-current-url';

// IX-016: the server sends only the workspaces this user may open. Selecting one grants nothing.
export function WorkspaceSwitcher() {
    const { t } = useTranslation();
    const { workspaces } = usePage().props;
    const { isCurrentOrParentUrl } = useCurrentUrl();

    if (workspaces.length === 0) {
        return null;
    }

    const current =
        workspaces.find((workspace) => isCurrentOrParentUrl(workspace.href)) ??
        workspaces[0];
    const currentLabel = t(`common:workspace.${current.key}`);

    if (workspaces.length === 1) {
        return (
            <span className="truncate text-sm font-semibold text-brand-950">
                <span className="sr-only">{t('common:workspace.label')}</span>
                {currentLabel}
            </span>
        );
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    className="h-11 min-w-0 gap-2 px-2 sm:h-9"
                    aria-label={t('common:workspace.label')}
                >
                    <span className="truncate">{currentLabel}</span>
                    <ChevronsUpDown className="size-4" aria-hidden="true" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" className="w-56">
                {workspaces.map((workspace) => (
                    <DropdownMenuItem key={workspace.key} asChild>
                        <Link href={workspace.href} className="cursor-pointer">
                            {t(`common:workspace.${workspace.key}`)}
                            {workspace.key === current.key && (
                                <Check
                                    className="ml-auto size-4"
                                    aria-hidden="true"
                                />
                            )}
                        </Link>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
