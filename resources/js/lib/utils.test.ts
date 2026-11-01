import { describe, expect, it } from 'vite-plus/test';
import { cn } from './utils';

describe('cn', () => {
    it('joins class names and lets the later Tailwind utility win', () => {
        expect(cn('px-2', 'text-sm', 'px-4')).toBe('text-sm px-4');
    });

    it('drops falsy values', () => {
        expect(cn('block', false, undefined, null, '')).toBe('block');
    });
});
