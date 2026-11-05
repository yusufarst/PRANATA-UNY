import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import AssignmentController from '@/actions/App/Modules/Access/Http/Controllers/AssignmentController';
import { selectClassName } from '@/components/admin/users';
import type { AssignmentPanelProps } from '@/components/app/AssignmentPanel';
import { AssignmentPanel } from '@/components/app/AssignmentPanel';
import { PageHeader } from '@/components/app/page-header';
import { EmptyState } from '@/components/app/state-views';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    types: { key: string; kind: string }[];
    query: { type: string; reference: string };
    panel: AssignmentPanelProps | null;
    notFound: boolean;
};

/**
 * PG-504, one object: the administrator chooses its kind and types its number, then manages its assignments in the panel that
 * module pages reuse.
 */
export default function AssignmentObject({
    types,
    query,
    panel,
    notFound,
}: Props) {
    const { t } = useTranslation();

    const kindName = (key: string) => {
        const kind = types.find((type) => type.key === key)?.kind;

        return kind ? t(`admin:assignments.kinds.${kind}`) : key;
    };

    const open = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = Object.fromEntries(new FormData(event.currentTarget));

        router.get(AssignmentController.create.url(), data, {
            preserveState: true,
        });
    };

    return (
        <>
            <Head title={t('admin:assignments.find.title')} />
            <div className="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <div>
                    <Button variant="ghost" size="sm" asChild className="-ml-2">
                        <Link href={AssignmentController.index()}>
                            <ArrowLeft aria-hidden="true" />
                            {t('admin:assignments.back_to_list')}
                        </Link>
                    </Button>
                </div>

                <PageHeader
                    title={t('admin:assignments.find.title')}
                    description={t('admin:assignments.find.intro')}
                />

                {types.length === 0 ? (
                    <EmptyState
                        title={t('admin:assignments.find.no_types_title')}
                        description={t(
                            'admin:assignments.find.no_types_description',
                        )}
                    />
                ) : (
                    <form
                        role="search"
                        aria-label={t('admin:assignments.find.title')}
                        onSubmit={open}
                        className="grid gap-4 rounded-lg border border-border bg-card p-4 shadow-card sm:grid-cols-[1fr_2fr_auto] sm:items-end"
                    >
                        <div className="grid gap-2">
                            <Label htmlFor="find-type">
                                {t('admin:assignments.fields.subject_type')}
                            </Label>
                            <select
                                id="find-type"
                                name="type"
                                required
                                defaultValue={query.type || types[0].key}
                                className={selectClassName}
                            >
                                {types.map((type) => (
                                    <option key={type.key} value={type.key}>
                                        {t(`admin:assignments.kinds.${type.kind}`)}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="find-reference">
                                {t('admin:assignments.fields.subject')}
                            </Label>
                            <Input
                                id="find-reference"
                                name="reference"
                                required
                                maxLength={80}
                                defaultValue={query.reference}
                                placeholder={t(
                                    'admin:assignments.find.reference_placeholder',
                                )}
                                aria-invalid={notFound}
                            />
                        </div>
                        <Button type="submit" variant="secondary">
                            {t('admin:assignments.find.submit')}
                        </Button>
                        {notFound && (
                            <div className="sm:col-span-3">
                                <InputError
                                    message={t(
                                        'admin:assignments.find.not_found',
                                    )}
                                />
                            </div>
                        )}
                    </form>
                )}

                {panel && (
                    <>
                        <p
                            className="text-foreground"
                            data-test="assignment-object"
                        >
                            {`${kindName(panel.subject.type)} ${panel.subject.reference} · ${panel.subject.label}`}
                        </p>
                        <AssignmentPanel panel={panel} />
                    </>
                )}
            </div>
        </>
    );
}

AssignmentObject.layout = {
    breadcrumbs: [
        {
            title: 'admin:assignments.title',
            href: AssignmentController.index(),
        },
    ],
};
