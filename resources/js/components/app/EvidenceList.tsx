import { Form } from '@inertiajs/react';
import { Download, EllipsisVertical, Eye, FileText } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useDateTime } from '@/components/admin/users';
import { ConflictDialog } from '@/components/app/ConflictDialog';
import { StatusBadge } from '@/components/app/status-badge';
import type { StatusCategory } from '@/components/app/status-badge';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCommand } from '@/lib/command';
import { formatBytes } from '@/lib/files';

export type ScanStatus = 'NOT_SCANNED' | 'CLEAN' | 'INFECTED' | 'ERROR';

export type Classification = 'INTERNAL' | 'CONFIDENTIAL' | 'RESTRICTED';

export type EvidenceFile = {
    link_id: number;
    public_id: string;
    original_name: string;
    mime_type: string;
    size_bytes: number;
    sha256: string;
    classification: Classification;
    scan_status: ScanStatus;
    purpose: string;
    label: string | null;
    uploaded_by: string | null;
    uploaded_at: string;
    active: boolean;
    unlinked_by: string | null;
    unlinked_at: string | null;
    /** null when the actor may no longer download the file, e.g. a released file nothing references any more. */
    download_url: string | null;
    preview_url: string | null;
    can_release: boolean;
};

const scanCategory: Record<ScanStatus, StatusCategory> = {
    NOT_SCANNED: 'waiting',
    CLEAN: 'completed',
    INFECTED: 'rejected',
    ERROR: 'blocked',
};

export type EvidenceListProps = {
    files: EvidenceFile[];
    /** The release command of a link, from EvidenceController.release.form(). */
    releaseForm: (linkId: number) => { action: string; method: 'post' };
    emptyText: string;
};

/**
 * The files linked to one object (PG-012): each with its download, a preview for PDF and images, its classification and scan
 * status, who uploaded it and its fingerprint; a released file the user may no longer read keeps its history without them. "Lepas tautan" sits in the secondary menu and only for a link the owning
 * module lets this user release; a sealed link never shows it. The server checks every command again.
 */
export function EvidenceList({
    files,
    releaseForm,
    emptyText,
}: EvidenceListProps) {
    const { t } = useTranslation();
    const dateTime = useDateTime();
    const command = useCommand();
    const [releasing, setReleasing] = useState<EvidenceFile | null>(null);
    const unknown = t('files:file.unknown_person');

    if (files.length === 0) {
        return <p className="text-sm text-muted-foreground">{emptyText}</p>;
    }

    return (
        <>
            <ul className="grid gap-3" data-test="evidence-list">
                {files.map((file) => (
                    <li
                        key={file.link_id}
                        className="flex flex-col gap-3 rounded-lg border border-border p-4"
                        data-test="evidence-row"
                    >
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div className="flex min-w-0 items-start gap-3">
                                <FileText
                                    className="mt-0.5 size-5 shrink-0 text-muted-foreground"
                                    aria-hidden="true"
                                />
                                <div className="min-w-0 space-y-1">
                                    <p className="font-medium break-all text-foreground">
                                        {file.original_name}
                                    </p>
                                    {file.label && (
                                        <p className="text-sm text-muted-foreground">
                                            {file.label}
                                        </p>
                                    )}
                                    <p className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                        <span className="rounded-md bg-neutral-surface px-2 py-0.5">
                                            {t(
                                                `files:file.purpose.${file.purpose}`,
                                                { defaultValue: file.purpose },
                                            )}
                                        </span>
                                        <span className="rounded-md bg-neutral-surface px-2 py-0.5">
                                            {t(
                                                `files:file.classification.${file.classification}`,
                                            )}
                                        </span>
                                        <span>
                                            {formatBytes(file.size_bytes, t)}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div className="flex flex-wrap items-center gap-2">
                                <StatusBadge
                                    category={scanCategory[file.scan_status]}
                                    label={t(
                                        `files:file.scan.${file.scan_status}`,
                                    )}
                                />
                                {file.download_url && (
                                    <Button variant="outline" size="sm" asChild>
                                        <a
                                            href={file.download_url}
                                            data-test="download-link"
                                        >
                                            <Download aria-hidden="true" />
                                            {t('files:file.download')}
                                        </a>
                                    </Button>
                                )}
                                {file.preview_url && (
                                    <Button variant="ghost" size="sm" asChild>
                                        <a
                                            href={file.preview_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            data-test="preview-link"
                                        >
                                            <Eye aria-hidden="true" />
                                            {t('files:file.preview')}
                                        </a>
                                    </Button>
                                )}
                                {file.can_release && (
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                aria-label={t(
                                                    'files:file.more',
                                                )}
                                                data-test="evidence-more"
                                            >
                                                <EllipsisVertical aria-hidden="true" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem
                                                className="text-danger"
                                                onSelect={() =>
                                                    setReleasing(file)
                                                }
                                            >
                                                {t('files:file.release')}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                )}
                            </div>
                        </div>
                        <div className="grid gap-1 text-sm">
                            <p className="text-muted-foreground">
                                {t('files:file.uploaded', {
                                    name: file.uploaded_by ?? unknown,
                                    time: dateTime(file.uploaded_at),
                                })}
                            </p>
                            {file.unlinked_at && (
                                <p className="text-muted-foreground">
                                    {t('files:file.released', {
                                        name: file.unlinked_by ?? unknown,
                                        time: dateTime(file.unlinked_at),
                                    })}
                                </p>
                            )}
                            {/* A definition list holds only its term and its value (axe definition-list). */}
                            <dl className="flex flex-wrap gap-x-2">
                                <dt className="text-muted-foreground">
                                    {t('files:file.sha256')}
                                </dt>
                                <dd className="font-mono text-xs break-all">
                                    {file.sha256}
                                </dd>
                            </dl>
                        </div>
                    </li>
                ))}
            </ul>

            <Dialog
                open={releasing !== null}
                onOpenChange={(open) => !open && setReleasing(null)}
            >
                <DialogContent>
                    <DialogTitle>{t('files:file.release_title')}</DialogTitle>
                    <DialogDescription>
                        {t('files:file.release_description')}
                    </DialogDescription>
                    {releasing && (
                        <Form
                            {...releaseForm(releasing.link_id)}
                            {...command.visit}
                            onSuccess={() => {
                                command.visit.onSuccess();
                                setReleasing(null);
                            }}
                            options={{ preserveScroll: true }}
                            className="grid gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <p className="text-sm break-all text-foreground">
                                        {releasing.original_name}
                                    </p>
                                    <InputError
                                        message={
                                            errors._idempotency ??
                                            errors._domain
                                        }
                                    />
                                    <DialogFooter className="gap-2">
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            onClick={() => setReleasing(null)}
                                        >
                                            {t('common:actions.cancel')}
                                        </Button>
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            disabled={processing}
                                            data-test="confirm-release-button"
                                        >
                                            {t('files:file.release_confirm')}
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    )}
                </DialogContent>
            </Dialog>
            <ConflictDialog command={command} />
        </>
    );
}
