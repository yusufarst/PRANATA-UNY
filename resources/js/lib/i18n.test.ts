import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join } from 'node:path';
import { describe, expect, it } from 'vite-plus/test';
import i18n, { errorStatuses, supportedLocales, toLocale } from './i18n';

function sourceFiles(folder: string): string[] {
    return readdirSync(folder).flatMap((name) => {
        const path = join(folder, name);

        if (statSync(path).isDirectory()) {
            return sourceFiles(path);
        }

        return /\.tsx?$/.test(name) && !/\.test\./.test(name) ? [path] : [];
    });
}

const usedKeys = [
    ...new Set(
        sourceFiles('resources/js').flatMap((path) =>
            [
                ...readFileSync(path, 'utf8').matchAll(
                    /['"`]((?:common|states|auth|account):[\w.]+)['"`]/g,
                ),
            ].map((match) => match[1]),
        ),
    ),
];

describe('translation catalogs (TAC-04)', () => {
    it('finds the keys used in the source', () => {
        expect(usedKeys.length).toBeGreaterThan(50);
        expect(usedKeys).toContain('common:nav.home');
    });

    it.each(supportedLocales)('has every used key in %s', (locale) => {
        const missing = usedKeys.filter(
            (key) => !i18n.exists(key, { lng: locale, fallbackLng: [] }),
        );

        expect(missing).toEqual([]);
    });

    it.each(supportedLocales)('has every error page in %s', (locale) => {
        const missing = errorStatuses
            .flatMap((status) => [
                `common:errors.${status}.title`,
                `common:errors.${status}.description`,
            ])
            .filter(
                (key) => !i18n.exists(key, { lng: locale, fallbackLng: [] }),
            );

        expect(missing).toEqual([]);
    });

    it('reports a key that does not exist', () => {
        expect(i18n.exists('common:nav.tidak_ada', { lng: 'id' })).toBe(false);
    });
});

describe('language', () => {
    it('defaults to Indonesian and refuses an unknown language', () => {
        expect(toLocale(undefined)).toBe('id');
        expect(toLocale('fr')).toBe('id');
        expect(toLocale('en')).toBe('en');
    });

    it('changes the text when the language changes', async () => {
        await i18n.changeLanguage('id');
        expect(i18n.t('common:nav.home')).toBe('Beranda');

        await i18n.changeLanguage('en');
        expect(i18n.t('common:nav.home')).toBe('Home');

        await i18n.changeLanguage('id');
    });
});
