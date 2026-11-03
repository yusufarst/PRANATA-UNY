import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export function NavMain({ items }: { items: NavItem[] }) {
    const { t } = useTranslation();
    const { isCurrentUrl } = useCurrentUrl();
    // The drawer of a phone stays mounted across visits, so a chosen entry closes it; otherwise it covers the page it opened.
    const { setOpenMobile } = useSidebar();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>{t('common:nav.main')}</SidebarGroupLabel>
            <SidebarMenu className="group-data-[collapsible=icon]:items-center">
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={isCurrentUrl(item.href)}
                            tooltip={{ children: t(item.title) }}
                        >
                            <Link
                                href={item.href}
                                prefetch
                                onClick={() => setOpenMobile(false)}
                            >
                                {item.icon && <item.icon />}
                                <span>{t(item.title)}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
