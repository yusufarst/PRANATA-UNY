import { Form, Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('auth:login.title')} />

            {status && (
                <div
                    role="status"
                    className="text-center text-sm font-medium text-success"
                >
                    {status}
                </div>
            )}

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="login">
                                {t('auth:fields.login')}
                            </Label>
                            <Input
                                id="login"
                                type="text"
                                name="login"
                                required
                                autoFocus
                                autoCapitalize="none"
                                autoComplete="username"
                                placeholder={t('auth:fields.login_placeholder')}
                            />
                            <InputError message={errors.login} />
                        </div>

                        <div className="grid gap-2">
                            <div className="flex items-center">
                                <Label htmlFor="password">
                                    {t('auth:fields.password')}
                                </Label>
                                {canResetPassword && (
                                    <TextLink
                                        href={request()}
                                        className="ml-auto text-sm"
                                    >
                                        {t('auth:login.forgot')}
                                    </TextLink>
                                )}
                            </div>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                autoComplete="current-password"
                                placeholder={t('auth:fields.password')}
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex items-center space-x-3">
                            <Checkbox id="remember" name="remember" />
                            <Label htmlFor="remember">
                                {t('auth:login.remember')}
                            </Label>
                        </div>

                        <Button
                            type="submit"
                            className="mt-4 w-full"
                            disabled={processing}
                            data-test="login-button"
                        >
                            {processing && <Spinner />}
                            {t('auth:login.submit')}
                        </Button>
                    </div>
                )}
            </Form>
        </>
    );
}

Login.layout = {
    title: 'auth:login.heading',
    description: 'auth:login.intro',
};
