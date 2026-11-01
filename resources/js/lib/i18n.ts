import i18next from 'i18next';
import { initReactI18next } from 'react-i18next';

export const supportedLocales = ['id', 'en'] as const;
export type Locale = (typeof supportedLocales)[number];

export const errorStatuses = [403, 404, 419, 429, 500, 503] as const;
export type ErrorStatus = (typeof errorStatuses)[number];

const defaultLocale: Locale = 'id';

export function toLocale(value: unknown): Locale {
    return supportedLocales.find((locale) => locale === value) ?? defaultLocale;
}

type Catalog = Record<string, unknown>;

const catalogs = import.meta.glob<Catalog>('../locales/*/*.json', {
    eager: true,
    import: 'default',
});

const resources: Record<string, Record<string, Catalog>> = {};

for (const [path, catalog] of Object.entries(catalogs)) {
    const [, locale, namespace] =
        /locales\/(\w+)\/(\w+)\.json$/.exec(path) ?? [];

    resources[locale] = { ...resources[locale], [namespace]: catalog };
}

void i18next.use(initReactI18next).init({
    resources,
    // The server renders the language of the session into the root element.
    lng: toLocale(
        typeof document === 'undefined' ? undefined : document.documentElement.lang,
    ),
    fallbackLng: defaultLocale,
    defaultNS: 'common',
    ns: Object.keys(resources[defaultLocale]),
    interpolation: { escapeValue: false },
    initAsync: false,
});

export function applyLocale(value: unknown): void {
    const locale = toLocale(value);

    if (i18next.language !== locale) {
        void i18next.changeLanguage(locale);
    }

    if (typeof document !== 'undefined') {
        document.documentElement.lang = locale;
    }
}

export default i18next;
