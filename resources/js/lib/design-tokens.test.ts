import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join } from 'node:path';
import { describe, expect, it } from 'vite-plus/test';

const css = readFileSync('resources/css/app.css', 'utf8');

const tokens = Object.fromEntries(
    [...css.matchAll(/--color-([\w-]+):\s*(#[0-9a-f]{6})\b/g)].map((match) => [
        match[1],
        match[2],
    ]),
);

function luminance(hex: string): number {
    const [red, green, blue] = [1, 3, 5]
        .map((start) => parseInt(hex.slice(start, start + 2), 16) / 255)
        .map((channel) =>
            channel <= 0.03928
                ? channel / 12.92
                : ((channel + 0.055) / 1.055) ** 2.4,
        );

    return 0.2126 * red + 0.7152 * green + 0.0722 * blue;
}

function contrast(foreground: string, background: string): number {
    const [lighter, darker] = [
        luminance(foreground),
        luminance(background),
    ].sort((a, b) => b - a);

    return (lighter + 0.05) / (darker + 0.05);
}

describe('design tokens (DESIGN_SYSTEM section 2)', () => {
    it('keeps the colors the Owner chose exactly', () => {
        expect(tokens['brand-950']).toBe('#172554');
        expect(tokens.canvas).toBe('#eff6ff');
        expect(tokens.surface).toBe('#ffffff');
    });

    it.each([
        ['text-primary', 'surface'],
        ['text-primary', 'canvas'],
        ['text-secondary', 'surface'],
        ['text-secondary', 'canvas'],
        ['text-muted', 'surface'],
        ['text-muted', 'canvas'],
        ['text-muted', 'surface-subtle'],
        ['text-on-brand', 'brand-950'],
        ['link', 'surface'],
        ['link', 'canvas'],
        ['success', 'success-surface'],
        ['warning', 'warning-surface'],
        ['danger', 'danger-surface'],
        ['info', 'info-surface'],
        ['neutral', 'neutral-surface'],
        ['blocked', 'blocked-surface'],
        ['simulation', 'simulation-surface'],
        ['brand-950', 'brand-100'],
    ])('%s on %s reaches 4.5:1 for text', (foreground, background) => {
        expect(tokens[foreground]).toBeDefined();
        expect(tokens[background]).toBeDefined();
        expect(
            contrast(tokens[foreground], tokens[background]),
        ).toBeGreaterThanOrEqual(4.5);
    });

    it.each([
        ['brand-600', 'surface'],
        ['brand-600', 'canvas'],
    ])('the focus ring %s on %s reaches 3:1', (foreground, background) => {
        expect(
            contrast(tokens[foreground], tokens[background]),
        ).toBeGreaterThanOrEqual(3);
    });

    it('recognizes a pair that fails', () => {
        expect(contrast('#64748b', '#eff6ff')).toBeLessThan(4.5);
    });
});

function sourceFiles(folder: string): string[] {
    return readdirSync(folder).flatMap((name) => {
        const path = join(folder, name);

        if (statSync(path).isDirectory()) {
            return sourceFiles(path);
        }

        return /\.tsx?$/.test(name) && !/\.test\./.test(name) ? [path] : [];
    });
}

describe('component colors', () => {
    it('holds no loose hex color and no third-party asset address', () => {
        const offenders = ['pages', 'layouts', 'components']
            .flatMap((folder) => sourceFiles(join('resources/js', folder)))
            .filter((path) =>
                /#[0-9a-fA-F]{3,8}\b|https?:\/\/(?!www\.w3\.org)/.test(
                    readFileSync(path, 'utf8'),
                ),
            );

        expect(offenders).toEqual([]);
    });
});
