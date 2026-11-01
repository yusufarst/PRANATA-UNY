import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { PageHeader } from '@/components/app/page-header';
import { EmptyState } from '@/components/app/state-views';
import { dashboard } from '@/routes';

export default function Home() {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('common:home.title')} />
            <div className="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title={t('common:home.title')}
                    description={t('common:home.description')}
                />
                <EmptyState
                    title={t('common:home.empty_title')}
                    description={t('common:home.empty_description')}
                />
            </div>
        </>
    );
}

Home.layout = {
    breadcrumbs: [
        {
            title: 'common:nav.home',
            href: dashboard(),
        },
    ],
};
