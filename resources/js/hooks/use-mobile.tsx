import { useSyncExternalStore } from 'react';

// INTERACTION_PATTERNS section 7: a drawer below 640 px, an icon rail up to 1023 px, the full sidebar above.
const MOBILE_BREAKPOINT = 640;
const DESKTOP_BREAKPOINT = 1024;

function mediaQuery(query: string) {
    return typeof window === 'undefined' ? undefined : window.matchMedia(query);
}

const mobile = mediaQuery(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`);
const tablet = mediaQuery(
    `(min-width: ${MOBILE_BREAKPOINT}px) and (max-width: ${DESKTOP_BREAKPOINT - 1}px)`,
);

function subscribeTo(mql: MediaQueryList | undefined) {
    return (callback: (event: MediaQueryListEvent) => void) => {
        if (!mql) {
            return () => {};
        }

        mql.addEventListener('change', callback);

        return () => {
            mql.removeEventListener('change', callback);
        };
    };
}

const subscribeToMobile = subscribeTo(mobile);
const subscribeToTablet = subscribeTo(tablet);

function getServerSnapshot(): boolean {
    return false;
}

export function useIsMobile(): boolean {
    return useSyncExternalStore(
        subscribeToMobile,
        () => mobile?.matches ?? false,
        getServerSnapshot,
    );
}

export function useIsTablet(): boolean {
    return useSyncExternalStore(
        subscribeToTablet,
        () => tablet?.matches ?? false,
        getServerSnapshot,
    );
}
