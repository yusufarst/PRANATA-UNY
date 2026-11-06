import { renderToStaticMarkup } from 'react-dom/server';
import { beforeEach, describe, expect, it, vi } from 'vite-plus/test';
import type { EvidenceFile } from '@/components/app/EvidenceList';
import { EvidenceList } from '@/components/app/EvidenceList';
import { FileDropzone } from '@/components/app/FileDropzone';
import { checkFile, formatBytes } from '@/lib/files';
import i18n from '@/lib/i18n';

vi.mock('@inertiajs/react', () => import('@/test/inertia-mock'));

const profile = {
    purpose: 'DECREE',
    extensions: ['pdf', 'jpg', 'png'],
    max_bytes: 10 * 1024 * 1024,
};

const file: EvidenceFile = {
    link_id: 7,
    public_id: '01jtestfile0000000000000000',
    original_name: 'SK Penunjukan.pdf',
    mime_type: 'application/pdf',
    size_bytes: 2048,
    sha256: 'a'.repeat(64),
    classification: 'INTERNAL',
    scan_status: 'NOT_SCANNED',
    purpose: 'DECREE',
    label: 'SK 12/2026',
    uploaded_by: 'Admin Uji',
    uploaded_at: '2026-11-06T03:00:00Z',
    active: true,
    unlinked_by: null,
    unlinked_at: null,
    download_url: '/files/01jtestfile0000000000000000',
    preview_url: '/files/01jtestfile0000000000000000?inline=1',
    can_release: true,
};

const releaseForm = (linkId: number) => ({
    action: `/files/evidence/org_appointments/1/links/${linkId}/release`,
    method: 'post' as const,
});

beforeEach(async () => {
    await i18n.changeLanguage('id');
});

describe('checkFile (SEC-10, SEC-13 before the upload starts)', () => {
    it('accepts an allowed kind within the limit', () => {
        expect(checkFile({ name: 'sk.pdf', size: 10 }, profile)).toBeNull();
        expect(checkFile({ name: 'SK.PDF', size: 10 }, profile)).toBeNull();
    });

    it('refuses a kind outside the allowlist and a name without extension', () => {
        expect(checkFile({ name: 'sk.exe', size: 10 }, profile)?.key).toBe(
            'extension',
        );
        expect(checkFile({ name: 'sk', size: 10 }, profile)?.key).toBe(
            'extension',
        );
    });

    it('refuses a second extension before the last one, not a number in the name', () => {
        expect(checkFile({ name: 'sk.php.pdf', size: 10 }, profile)?.key).toBe(
            'double_extension',
        );
        expect(checkFile({ name: 'SK No.12.pdf', size: 10 }, profile)).toBeNull();
        expect(
            checkFile({ name: 'Laporan v1.2.pdf', size: 10 }, profile),
        ).toBeNull();
        expect(checkFile({ name: 'sk.pdf.exe', size: 10 }, profile)?.key).toBe(
            'extension',
        );
    });

    it('refuses an empty file and one over the limit of the purpose', () => {
        expect(checkFile({ name: 'sk.pdf', size: 0 }, profile)?.key).toBe(
            'empty',
        );
        expect(
            checkFile({ name: 'sk.pdf', size: profile.max_bytes + 1 }, profile),
        ).toEqual({ key: 'size', params: { max: '10' } });
    });
});

describe('FileDropzone', () => {
    it('names the kinds and the limit, and keeps the purpose with the file', () => {
        const html = renderToStaticMarkup(
            <FileDropzone
                form={{
                    action: '/files/evidence/org_appointments/1',
                    method: 'post',
                }}
                purposes={[profile]}
            />,
        );

        expect(html).toContain('pdf, jpg, png');
        expect(html).toContain('10 MB');
        expect(html).toContain('Pilih berkas');
        expect(html).toContain('name="purpose" value="DECREE"');
        expect(html).toContain('accept=".pdf,.jpg,.png"');
        // Nothing is chosen yet, so the primary action waits.
        expect(html).toMatch(
            /<button[^>]*data-test="upload-button"[^>]*disabled/,
        );
    });

    it('speaks English after the language switch', async () => {
        await i18n.changeLanguage('en');

        expect(
            renderToStaticMarkup(
                <FileDropzone
                    form={{ action: '/x', method: 'post' }}
                    purposes={[profile]}
                />,
            ),
        ).toContain('Choose a file');
    });
});

describe('EvidenceList', () => {
    it('shows the file with its download, preview, scan status and fingerprint', () => {
        const html = renderToStaticMarkup(
            <EvidenceList
                files={[file]}
                releaseForm={releaseForm}
                emptyText="-"
            />,
        );

        expect(html).toContain('SK Penunjukan.pdf');
        expect(html).toContain('SK 12/2026');
        expect(html).toContain('href="/files/01jtestfile0000000000000000"');
        expect(html).toContain('?inline=1');
        expect(html).toContain('Belum dipindai');
        expect(html).toContain('a'.repeat(64));
        expect(html).toContain('2.0 KB');
        expect(html).toContain('aria-label="Lainnya"');
    });

    it('offers no release for a link the module does not let this user release', () => {
        const html = renderToStaticMarkup(
            <EvidenceList
                files={[{ ...file, can_release: false, preview_url: null }]}
                releaseForm={releaseForm}
                emptyText="-"
            />,
        );

        expect(html).not.toContain('aria-label="Lainnya"');
        expect(html).not.toContain('?inline=1');
    });

    it('shows the empty text without a list', () => {
        const html = renderToStaticMarkup(
            <EvidenceList
                files={[]}
                releaseForm={releaseForm}
                emptyText="Belum ada bukti"
            />,
        );

        expect(html).toContain('Belum ada bukti');
        expect(html).not.toContain('<ul');
    });
});

describe('formatBytes', () => {
    it('reads sizes in the language of the page', async () => {
        expect(formatBytes(512, i18n.t)).toBe('512 B');
        expect(formatBytes(3 * 1024 * 1024, i18n.t)).toBe('3 MB');
        await i18n.changeLanguage('en');
        expect(formatBytes(1536, i18n.t)).toBe('1.5 KB');
    });
});
