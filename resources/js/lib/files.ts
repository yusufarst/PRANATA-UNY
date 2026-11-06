import type { TFunction } from 'i18next';

/** What may be uploaded for a purpose, as EvidencePanel.php sends it (config/files.php). */
export type UploadProfile = {
    purpose: string;
    extensions: string[];
    max_bytes: number;
};

export type FileProblem =
    | { key: 'extension'; params: { allowed: string } }
    | { key: 'double_extension'; params?: undefined }
    | { key: 'size'; params: { max: string } }
    | { key: 'empty'; params?: undefined };

export function megabytes(bytes: number): string {
    const value = bytes / (1024 * 1024);

    return Number.isInteger(value) ? String(value) : value.toFixed(1);
}

/** A size as people read it, in the language of the page: "512 B", "1.5 KB", "3 MB". */
export function formatBytes(bytes: number, t: TFunction): string {
    if (bytes < 1024) {
        return t('files:size.bytes', { value: String(bytes) });
    }

    if (bytes < 1024 * 1024) {
        return t('files:size.kb', { value: (bytes / 1024).toFixed(1) });
    }

    return t('files:size.mb', { value: megabytes(bytes) });
}

/**
 * The same checks the server makes before it stores anything (PrivateFileRule), so the user hears about a wrong kind or
 * size before the upload starts. The server checks again, and reads the signature of the bytes too.
 */
export function checkFile(
    file: { name: string; size: number },
    profile: UploadProfile,
): FileProblem | null {
    const name = file.name;
    const dot = name.lastIndexOf('.');
    const extension = dot === -1 ? '' : name.slice(dot + 1).toLowerCase();

    if (extension === '' || !profile.extensions.includes(extension)) {
        return {
            key: 'extension',
            params: { allowed: profile.extensions.join(', ') },
        };
    }

    if (/\.[a-z0-9]{1,5}$/i.test(name.slice(0, dot))) {
        return { key: 'double_extension' };
    }

    if (file.size === 0) {
        return { key: 'empty' };
    }

    if (file.size > profile.max_bytes) {
        return { key: 'size', params: { max: megabytes(profile.max_bytes) } };
    }

    return null;
}
