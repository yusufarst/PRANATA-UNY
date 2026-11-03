import { Link, usePage } from '@inertiajs/react';
import { House, Users } from 'lucide-react';
import UserAdministrationController from '@/actions/App/Modules/Identity/Http/Controllers/UserAdministrationController';
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

export function AppSidebar() {
    const { auth } = usePage().props;

    // The menu offers only what the account may open; the server checks every route again.
    const mainNavItems: NavItem[] = [
        {
            title: 'common:nav.home',
            href: dashboard(),
            icon: House,
        },
        ...(auth.can.manageUsers
            ? [
                  {
                      title: 'common:nav.users',
                      href: UserAdministrationController.index(),
                      icon: Users,
                  },
              ]
            : []),
    ];

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
