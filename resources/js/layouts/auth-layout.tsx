import { useTranslation } from 'react-i18next';
import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';

/** Pages pass catalog keys, because a static layout property cannot translate. */
export default function AuthLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: React.ReactNode;
}) {
    const { t } = useTranslation();

    return (
        <AuthLayoutTemplate
            title={title && t(title)}
            description={description && t(description)}
        >
            {children}
        </AuthLayoutTemplate>
    );
}
