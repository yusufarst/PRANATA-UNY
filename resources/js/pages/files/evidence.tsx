import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Upload } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import EvidenceController from '@/actions/App/Modules/Files/Http/Controllers/EvidenceController';
import type { EvidenceFile } from '@/components/app/EvidenceList';
import { EvidenceList } from '@/components/app/EvidenceList';
import { FileDropzone } from '@/components/app/FileDropzone';
import { PageHeader } from '@/components/app/page-header';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import type { UploadProfile } from '@/lib/files';
import { dashboard } from '@/routes';

export type EvidencePanelProps = {
    owner: {
        type: string;
        id: number;
        title: string;
        subtitle: string | null;
        parent_url: string;
        parent_title: string;
    };
    files: EvidenceFile[];
    purposes: UploadProfile[];
    can: { upload: boolean };
};

type Props = { panel: EvidencePanelProps };

const sectionClassName =
    'flex flex-col gap-4 rounded-lg border border-border bg-card p-4 shadow-card sm:p-6';

/**
 * PG-012 Bukti objek (RT-031, IX-011): the evidence of one row of an owning module, with "Unggah bukti" as the single
 * primary action and the way back to the exact parent. The server decides who may open, upload and release.
 */
export default function EvidencePage({ panel }: Props) {
    const { t } = useTranslation();
    const [uploading, setUploading] = useState(false);
    const active = panel.files.filter((file) => file.active);
    const history = panel.files.filter((file) => !file.active);
    const owner = { type: panel.owner.type, id: panel.owner.id };

    const uploadAction = panel.can.upload ? (
        <Button
            onClick={() => setUploading(true)}
            data-test="upload-evidence-button"
        >
            <Upload aria-hidden="true" />
            {t('files:evidence.upload')}
        </Button>
    ) : undefined;

    return (
        <>
            <Head title={t('files:evidence.title')} />
            <div className="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <div>
                    <Button variant="ghost" size="sm" asChild className="-ml-2">
                        <Link
                            href={panel.owner.parent_url}
                            data-test="evidence-back"
                        >
                            <ArrowLeft aria-hidden="true" />
                            {t('files:evidence.back', {
                                parent: panel.owner.parent_title,
                            })}
                        </Link>
                    </Button>
                </div>

                <PageHeader
                    title={t('files:evidence.title')}
                    description={t('files:evidence.intro')}
                    action={uploadAction}
                />

                <section
                    aria-labelledby="evidence-owner"
                    className={sectionClassName}
                    data-test="evidence-owner"
                >
                    <h2
                        id="evidence-owner"
                        className="text-sm text-muted-foreground"
                    >
                        {t('files:evidence.owner')}
                    </h2>
                    <p className="text-lg font-semibold text-foreground">
                        {panel.owner.title}
                    </p>
                    {panel.owner.subtitle && (
                        <p className="text-sm text-muted-foreground">
                            {panel.owner.subtitle}
                        </p>
                    )}
                    {!panel.can.upload && (
                        <p className="text-sm text-muted-foreground">
                            {t('files:evidence.no_permission')}
                        </p>
                    )}
                </section>

                <section
                    aria-labelledby="evidence-active"
                    className={sectionClassName}
                    data-test="evidence-active"
                >
                    <h2
                        id="evidence-active"
                        className="text-lg font-semibold text-foreground"
                    >
                        {t('files:evidence.active')}
                    </h2>
                    <EvidenceList
                        files={active}
                        releaseForm={(linkId) =>
                            EvidenceController.release.form({
                                ...owner,
                                link: linkId,
                            })
                        }
                        emptyText={
                            panel.can.upload
                                ? `${t('files:evidence.empty_title')}. ${t('files:evidence.empty_description')}`
                                : t('files:evidence.empty_read_only')
                        }
                    />
                </section>

                {history.length > 0 && (
                    <section
                        aria-labelledby="evidence-history"
                        className={sectionClassName}
                        data-test="evidence-history"
                    >
                        <h2
                            id="evidence-history"
                            className="text-lg font-semibold text-foreground"
                        >
                            {t('files:evidence.history')}
                        </h2>
                        <EvidenceList
                            files={history}
                            releaseForm={(linkId) =>
                                EvidenceController.release.form({
                                    ...owner,
                                    link: linkId,
                                })
                            }
                            emptyText=""
                        />
                    </section>
                )}

                <Dialog open={uploading} onOpenChange={setUploading}>
                    <DialogContent className="max-h-[90vh] overflow-y-auto">
                        <DialogTitle>
                            {t('files:evidence.upload_title')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('files:evidence.upload_intro')}
                        </DialogDescription>
                        <FileDropzone
                            form={EvidenceController.store.form(owner)}
                            purposes={panel.purposes}
                            onUploaded={() => setUploading(false)}
                        />
                    </DialogContent>
                </Dialog>
            </div>
        </>
    );
}

EvidencePage.layout = {
    breadcrumbs: [{ title: 'files:evidence.title', href: dashboard() }],
};
