import { Form, Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import ForcedPasswordChangeController from '@/actions/App/Modules/Identity/Http/Controllers/ForcedPasswordChangeController';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';

type Props = {
    passwordRules: string;
};

// The change an account must make before anything else (TASK-010 TAC-04); signing out stays open.
export default function ChangePassword({ passwordRules }: Props) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('auth:change.title')} />

            <Form
                {...ForcedPasswordChangeController.update.form()}
                resetOnError={[
                    'current_password',
                    'password',
                    'password_confirmation',
                ]}
                resetOnSuccess
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="current_password">
                                {t('auth:change.current')}
                            </Label>
                            <PasswordInput
                                id="current_password"
                                name="current_password"
                                autoComplete="current-password"
                                className="mt-1 block w-full"
                                autoFocus
                                placeholder={t('auth:change.current')}
                            />
                            <InputError message={errors.current_password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">
                                {t('auth:change.new')}
                            </Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                autoComplete="new-password"
                                className="mt-1 block w-full"
                                placeholder={t('auth:change.new')}
                                passwordrules={passwordRules}
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">
                                {t('auth:fields.confirm_password')}
                            </Label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                autoComplete="new-password"
                                className="mt-1 block w-full"
                                placeholder={t('auth:fields.confirm_password')}
                                passwordrules={passwordRules}
                            />
                            <InputError
                                message={errors.password_confirmation}
                            />
                        </div>

                        <Button
                            type="submit"
                            className="mt-4 w-full"
                            disabled={processing}
                            data-test="change-password-button"
                        >
                            {processing && <Spinner />}
                            {t('auth:change.submit')}
                        </Button>

                        <TextLink
                            href={logout()}
                            className="mx-auto block text-sm"
                        >
                            {t('auth:change.logout')}
                        </TextLink>
                    </div>
                )}
            </Form>
        </>
    );
}

ChangePassword.layout = {
    title: 'auth:change.title',
    description: 'auth:change.intro',
};
