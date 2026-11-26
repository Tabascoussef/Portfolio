import { Head, Link, usePage } from '@inertiajs/react';
import { FileText, List, Plus, Settings2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardFooter, CardHeader } from '@/components/ui/card';
import { dashboard } from '@/routes';
import {
    create as createPortfolioEntry,
    index as portfolioEntries,
} from '@/routes/portfolio-admin/entries';
import { edit as portfolioSettings } from '@/routes/portfolio-admin/profile';
import { edit as portfolioResume } from '@/routes/portfolio-admin/resume';
import { edit as editProfile } from '@/routes/profile';
import type { PortfolioAdminNavigation } from '@/types';

export default function Dashboard() {
    const { portfolioAdmin } = usePage<{
        portfolioAdmin: PortfolioAdminNavigation | null;
    }>().props;

    return (
        <>
            <Head title="Dashboard" />
            <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <h1
                    className="text-2xl font-semibold tracking-tight"
                    lang={portfolioAdmin?.locale}
                >
                    {portfolioAdmin?.title ?? 'Dashboard'}
                </h1>

                {portfolioAdmin ? (
                    <div
                        className="grid gap-4 sm:grid-cols-2 2xl:grid-cols-3"
                        lang={portfolioAdmin.locale}
                    >
                        {portfolioAdmin.sections.map(({ id, title }) => (
                            <Card key={id} className="gap-5 py-5">
                                <CardHeader className="px-5">
                                    <h2 className="font-semibold">{title}</h2>
                                </CardHeader>
                                <CardFooter className="mt-auto flex-wrap gap-2 px-5">
                                    <Button asChild size="sm" variant="outline">
                                        <a
                                            href={portfolioEntries.url(id)}
                                            aria-label={`${title} – ${portfolioAdmin.list}`}
                                        >
                                            <List aria-hidden />
                                            {portfolioAdmin.list}
                                        </a>
                                    </Button>
                                    <Button asChild size="sm">
                                        <a
                                            href={createPortfolioEntry.url(id)}
                                            aria-label={`${title} – ${portfolioAdmin.add}`}
                                        >
                                            <Plus aria-hidden />
                                            {portfolioAdmin.add}
                                        </a>
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))}
                        {[
                            {
                                title: portfolioAdmin.profile,
                                href: portfolioSettings.url(),
                                Icon: Settings2,
                            },
                            {
                                title: portfolioAdmin.resume,
                                href: portfolioResume.url(),
                                Icon: FileText,
                            },
                        ].map(({ title, href, Icon }) => (
                            <Card key={href} className="gap-5 py-5">
                                <CardHeader className="px-5">
                                    <h2 className="font-semibold">{title}</h2>
                                </CardHeader>
                                <CardFooter className="mt-auto px-5">
                                    <Button asChild size="sm" variant="outline">
                                        <a
                                            href={href}
                                            aria-label={`${title} – ${portfolioAdmin.edit}`}
                                        >
                                            <Icon aria-hidden />
                                            {portfolioAdmin.edit}
                                        </a>
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <div>
                        <Button asChild variant="outline">
                            <Link href={editProfile()}>
                                <Settings2 aria-hidden />
                                Profile settings
                            </Link>
                        </Button>
                    </div>
                )}
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
