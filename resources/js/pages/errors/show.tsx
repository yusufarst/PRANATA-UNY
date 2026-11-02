import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { LanguageSwitcher } from '@/components/app/language-switcher';
import {
    ErrorState,
    ForbiddenState,
    NotFoundState,
} from '@/components/app/state-views';
import { Button } from '@/components/ui/button';
import type { ErrorStatus } from '@/lib/i18n';
import { dashboard } from '@/routes';

export default function ErrorPage({
    status,
    returnUrl = null,
}: {
    status: ErrorStatus;
    returnUrl?: string | null;
}) {
    const { t } = useTranslation();
    const title = t(`common:errors.${status}.title`);
    const description = t(`common:errors.${status}.description`);
    // A POST fails during maintenance and with an expired page token, so the switch would be a dead control there.
    // After a failed mutation it would also redirect back to an address that may answer no GET.
    const canSwitchLanguage =
        status !== 419 && status !== 503 && returnUrl === null;

    const goHome = (
        <Button asChild>
            <Link href={dashboard()}>{t('common:errors.home')}</Link>
        </Button>
    );
    const reload = (
        <Button
            type="button"
            onClick={() =>
                returnUrl === null
                    ? window.location.reload()
                    : window.location.assign(returnUrl)
            }
        >
            {t(status === 419 ? 'common:errors.reload' : 'common:errors.retry')}
        </Button>
    );

    return (
        <>
            <Head title={title} />
            <main className="flex min-h-svh flex-col items-center justify-center gap-6 p-6">
                {canSwitchLanguage && <LanguageSwitcher />}
                <div className="w-full max-w-lg">
                    {status === 403 && (
                        <ForbiddenState
                            title={title}
                            description={description}
                            action={goHome}
                        />
                    )}
                    {status === 404 && (
                        <NotFoundState
                            title={title}
                            description={description}
                            action={goHome}
                        />
                    )}
                    {status !== 403 && status !== 404 && (
                        <ErrorState
                            title={title}
                            description={description}
                            action={reload}
                        />
                    )}
                </div>
            </main>
        </>
    );
}
