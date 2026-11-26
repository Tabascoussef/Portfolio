import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Compass,
    FileText,
    FolderGit2,
    FolderKanban,
    History,
    LayoutGrid,
    Settings2,
    Wrench,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as portfolioEntries } from '@/routes/portfolio-admin/entries';
import { edit as portfolioSettings } from '@/routes/portfolio-admin/profile';
import { edit as portfolioResume } from '@/routes/portfolio-admin/resume';
import type { NavItem, PortfolioAdminNavigation } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

const sectionIcons: Partial<Record<string, LucideIcon>> = {
    projects: FolderKanban,
    skills: Wrench,
    journey: History,
    principles: Compass,
};

export function AppSidebar() {
    const { portfolioAdmin } = usePage<{
        portfolioAdmin: PortfolioAdminNavigation | null;
    }>().props;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
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
                {portfolioAdmin && (
                    <NavMain
                        label={portfolioAdmin.title}
                        lang={portfolioAdmin.locale}
                        fullPage
                        items={[
                            {
                                title: portfolioAdmin.profile,
                                href: portfolioSettings.url(),
                                icon: Settings2,
                            },
                            {
                                title: portfolioAdmin.resume,
                                href: portfolioResume.url(),
                                icon: FileText,
                            },
                            ...portfolioAdmin.sections.map(({ id, title }) => ({
                                title,
                                href: portfolioEntries.url(id),
                                icon: sectionIcons[id],
                            })),
                        ]}
                    />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
