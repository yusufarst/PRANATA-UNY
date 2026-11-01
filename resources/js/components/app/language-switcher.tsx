import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import type { Locale } from '@/lib/i18n';
import { supportedLocales, toLocale } from '@/lib/i18n';
import { update } from '@/routes/locale';

export function chooseLocale(locale: Locale, current: Locale): void {
    if (locale === current) {
        return;
    }

    // preserveState keeps the page and any input that is not saved yet (INTERACTION_PATTERNS section 6).
    router.post(
        update.url(),
        { locale },
        { preserveScroll: true, preserveState: true },
    );
}

export function LanguageSwitcher() {
    const { t, i18n } = useTranslation();
    const current = toLocale(i18n.language);

    return (
        <div
            role="group"
            aria-label={t('common:language.label')}
            className="flex items-center gap-1"
        >
            {supportedLocales.map((locale) => (
                <Button
                    key={locale}
                    type="button"
                    size="sm"
                    variant={locale === current ? 'secondary' : 'ghost'}
                    className="h-11 min-w-11 px-2 sm:h-8 sm:min-w-8"
                    aria-pressed={locale === current}
                    aria-label={t(`common:language.${locale}_name`)}
                    onClick={() => chooseLocale(locale, current)}
                >
                    {t(`common:language.${locale}`)}
                </Button>
            ))}
        </div>
    );
}
