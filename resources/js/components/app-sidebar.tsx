import { Link, usePage } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { House, KeyRound, Network, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import {
    Sidebar,
    SidebarContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

// The icon of each menu entry the server may send; an entry without one still shows.
const icons: Record<string, LucideIcon> = {
    home: House,
    users: Users,
    organization: Network,
    access: KeyRound,
};

export function AppSidebar() {
    const { menu } = usePage().props;

    // TAC-01: the server sends only the entries this account may open; it checks every route again.
    const mainNavItems: NavItem[] = menu.map((entry) => ({
        title: `common:nav.${entry.key}`,
        href: entry.href,
        icon: icons[entry.key] ?? null,
    }));

    return (
        <Sidebar collapsible="icon" variant="sidebar">
            <SidebarHeader>
                <SidebarMenu className="group-data-[collapsible=icon]:items-center">
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>
        </Sidebar>
    );
}
