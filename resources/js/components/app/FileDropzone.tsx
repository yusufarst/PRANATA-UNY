import { Form } from '@inertiajs/react';
import { ShieldAlert, Upload } from 'lucide-react';
import type { ChangeEvent, DragEvent } from 'react';
import { useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { selectClassName } from '@/components/admin/users';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCommand } from '@/lib/command';
import type { UploadProfile } from '@/lib/files';
import { checkFile, formatBytes, megabytes } from '@/lib/files';
import { cn } from '@/lib/utils';

export type FileDropzoneProps = {
    /** The upload command of the owner row, from EvidenceController.store.form(). */
    form: { action: string; method: 'post' };
    purposes: UploadProfile[];
    onUploaded?: () => void;
};

/**
 * IX-011: one file for one purpose on one object, with the kind and size checked before the upload starts, the progress
 * while it runs and the server's refusal with its reason. The purpose and label go with the file; the server computes the
 * fingerprint and size from the stored bytes itself.
 */
export function FileDropzone({
    form,
    purposes,
    onUploaded,
}: FileDropzoneProps) {
    const { t } = useTranslation();
    const command = useCommand();
    const inputRef = useRef<HTMLInputElement>(null);
    const [purpose, setPurpose] = useState(purposes[0]?.purpose ?? '');
    const [selected, setSelected] = useState<File | null>(null);
    const [dragging, setDragging] = useState(false);
    const profile =
        purposes.find((each) => each.purpose === purpose) ?? purposes[0];
    const problem =
        selected !== null && profile ? checkFile(selected, profile) : null;
    const problemText =
        problem === null
            ? null
            : t(`files:dropzone.errors.${problem.key}`, problem.params);

    const take = (files: FileList | null) => {
        setSelected(files?.[0] ?? null);
    };

    const onChange = (event: ChangeEvent<HTMLInputElement>) => {
        take(event.target.files);
    };

    const onDrop = (event: DragEvent<HTMLDivElement>) => {
        event.preventDefault();
        setDragging(false);
        const file = event.dataTransfer.files[0];

        if (file && inputRef.current) {
            const transfer = new DataTransfer();

            transfer.items.add(file);
            inputRef.current.files = transfer.files;
            setSelected(file);
        }
    };

    if (!profile) {
        return null;
    }

    return (
        <Form
            {...form}
            {...command.visit}
            onSuccess={() => {
                command.visit.onSuccess();
                setSelected(null);

                if (inputRef.current) {
                    inputRef.current.value = '';
                }

                onUploaded?.();
            }}
            options={{ preserveScroll: true }}
            className="grid gap-4"
            data-test="file-dropzone"
        >
            {({ processing, progress, errors }) => (
                <>
                    {(errors._domain || errors._idempotency) && (
                        <Alert
                            className="border-danger"
                            data-test="upload-error"
                        >
                            <ShieldAlert aria-hidden="true" />
                            <AlertTitle>
                                {t('files:dropzone.failed')}
                            </AlertTitle>
                            <AlertDescription>
                                {errors._domain ?? errors._idempotency}
                            </AlertDescription>
                        </Alert>
                    )}

                    {purposes.length > 1 ? (
                        <div className="grid gap-2">
                            <Label htmlFor="evidence_purpose">
                                {t('files:dropzone.purpose')}
                            </Label>
                            <select
                                id="evidence_purpose"
                                name="purpose"
                                value={purpose}
                                onChange={(event) =>
                                    setPurpose(event.target.value)
                                }
                                className={selectClassName}
                                aria-invalid={!!errors.purpose}
                            >
                                {purposes.map((option) => (
                                    <option
                                        key={option.purpose}
                                        value={option.purpose}
                                    >
                                        {t(
                                            `files:file.purpose.${option.purpose}`,
                                            { defaultValue: option.purpose },
                                        )}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.purpose} />
                        </div>
                    ) : (
                        <input type="hidden" name="purpose" value={purpose} />
                    )}

                    <div
                        onDragOver={(event) => {
                            event.preventDefault();
                            setDragging(true);
                        }}
                        onDragLeave={() => setDragging(false)}
                        onDrop={onDrop}
                        className={cn(
                            'grid gap-3 rounded-lg border-2 border-dashed p-6 text-center transition-colors',
                            dragging
                                ? 'border-brand-600 bg-accent'
                                : 'border-border',
                        )}
                        data-test="dropzone-area"
                    >
                        <Upload
                            className="mx-auto size-8 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <p className="text-sm text-foreground">
                            {t('files:dropzone.intro')}
                        </p>
                        <p
                            id="evidence_file_hint"
                            className="text-sm text-muted-foreground"
                        >
                            {t('files:dropzone.limits', {
                                extensions: profile.extensions.join(', '),
                                max: megabytes(profile.max_bytes),
                            })}
                        </p>
                        <div className="flex flex-col items-center gap-2">
                            <Label
                                htmlFor="evidence_file"
                                className="cursor-pointer rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-xs hover:bg-accent"
                            >
                                {t('files:dropzone.choose')}
                            </Label>
                            <input
                                ref={inputRef}
                                id="evidence_file"
                                name="file"
                                type="file"
                                required
                                accept={profile.extensions
                                    .map((extension) => `.${extension}`)
                                    .join(',')}
                                onChange={onChange}
                                aria-describedby="evidence_file_hint"
                                aria-invalid={!!errors.file || problem !== null}
                                className="sr-only"
                                data-test="evidence-file-input"
                            />
                            <span className="text-xs text-muted-foreground">
                                {t('files:dropzone.drop')}
                            </span>
                        </div>
                        {selected && (
                            <p
                                className="text-sm text-foreground"
                                data-test="selected-file"
                            >
                                {t('files:dropzone.selected', {
                                    name: selected.name,
                                    size: formatBytes(selected.size, t),
                                })}
                            </p>
                        )}
                        <InputError message={problemText ?? errors.file} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="evidence_label">
                            {t('files:dropzone.label')}
                        </Label>
                        <Input
                            id="evidence_label"
                            name="label"
                            maxLength={200}
                            aria-describedby="evidence_label_hint"
                            aria-invalid={!!errors.label}
                        />
                        <p
                            id="evidence_label_hint"
                            className="text-sm text-muted-foreground"
                        >
                            {t('files:dropzone.label_hint')}
                        </p>
                        <InputError message={errors.label} />
                    </div>

                    {processing && progress && (
                        <div className="grid gap-1">
                            <p
                                className="text-sm text-muted-foreground"
                                aria-live="polite"
                            >
                                {t('files:dropzone.uploading', {
                                    percent: progress.percentage ?? 0,
                                })}
                            </p>
                            <div
                                role="progressbar"
                                aria-valuemin={0}
                                aria-valuemax={100}
                                aria-valuenow={progress.percentage ?? 0}
                                className="h-2 w-full overflow-hidden rounded-full bg-neutral-surface"
                            >
                                <div
                                    className="h-full bg-brand-600 transition-[width]"
                                    style={{
                                        width: `${progress.percentage ?? 0}%`,
                                    }}
                                />
                            </div>
                        </div>
                    )}

                    <div className="flex justify-end">
                        <Button
                            type="submit"
                            disabled={
                                processing ||
                                selected === null ||
                                problem !== null
                            }
                            data-test="upload-button"
                        >
                            {t('files:dropzone.submit')}
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
