import { useTranslation } from 'react-i18next';

/** Screen-reader text inside the copied shadcn/ui primitives, which stay free of hooks where they can. */
export function TranslatedText({ textKey }: { textKey: string }) {
    const { t } = useTranslation();

    return t(textKey);
}
