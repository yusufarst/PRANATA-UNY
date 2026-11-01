import { AccountMenu } from '@/components/app/account-menu';
import { LanguageSwitcher } from '@/components/app/language-switcher';
import { WorkspaceSwitcher } from '@/components/app/workspace-switcher';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <>
            <header className="flex h-16 shrink-0 items-center gap-2 border-b border-border bg-surface px-4 lg:px-6">
                {/* The tablet width is always the icon rail, so a toggle there would do nothing. */}
                <SidebarTrigger className="-ml-1 size-11 sm:hidden lg:flex lg:size-7" />
                <WorkspaceSwitcher />
                <div className="hidden min-w-0 sm:block">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
                <div className="ml-auto flex shrink-0 items-center gap-2">
                    <LanguageSwitcher />
                    <AccountMenu />
                </div>
            </header>
            {breadcrumbs.length > 1 && (
                <div className="border-b border-border bg-surface px-4 py-2 sm:hidden">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            )}
        </>
    );
}
