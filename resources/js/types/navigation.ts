import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
};

export type PortfolioAdminNavigation = {
    locale: string;
    title: string;
    profile: string;
    resume: string;
    list: string;
    add: string;
    edit: string;
    sections: { id: string; title: string }[];
};
