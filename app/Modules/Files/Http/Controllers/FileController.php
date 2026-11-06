<?php

namespace App\Modules\Files\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Modules\Files\Events\FileDownloaded;
use App\Modules\Files\Events\FileIntegrityFailed;
use App\Modules\Files\Exceptions\FileIntegrityException;
use App\Modules\Files\Models\FileObject;
use App\Modules\Files\Providers\FilesServiceProvider;
use App\Modules\Files\Support\FileIntegrity;
use App\Modules\Platform\Support\RequestId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * GET /files/{public_id} (COMMAND_AND_API_CONTRACTS section 7, SEC-11, SEC-12, SEC-22): the only way bytes leave the private
 * disk. The subject that references the file must let the user read it; the bytes are copied and verified against size and
 * SHA-256 before the first byte is sent; a CONFIDENTIAL or RESTRICTED download is recorded. Missing or changed bytes fail
 * closed: no file content, a localized 500 with the request id, an audit event file.integrity_failed and an operations alert.
 *
 * ?inline=1 shows a PDF, PNG or JPG in the browser; everything else is always an attachment.
 */
class FileController
{
    public function show(Request $request, FileObject $file, FileIntegrity $integrity): Response
    {
        Gate::authorize(FilesServiceProvider::DOWNLOAD, $file);

        $inline = $request->boolean('inline') && $file->isPreviewable();

        try {
            $copy = $integrity->verifiedCopy($file);
        } catch (FileIntegrityException $exception) {
            return $this->integrityFailed($request, $exception, $inline ? 'preview' : 'download');
        }

        // Recorded before the first byte goes out; outside any transaction, so it is written at once.
        if ($file->isConfidential()) {
            FileDownloaded::dispatch($file, $request->user(), $inline);
        }

        $response = new BinaryFileResponse($copy->path, 200, [
            'Content-Type' => $file->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], false, null, false, false);
        $response->deleteFileAfterSend(true);
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            $inline ? HeaderUtils::DISPOSITION_INLINE : HeaderUtils::DISPOSITION_ATTACHMENT,
            $file->original_name,
            self::asciiName($file->original_name),
        ));

        return $response;
    }

    private function integrityFailed(Request $request, FileIntegrityException $exception, string $during): Response
    {
        $file = $exception->file;

        FileIntegrityFailed::dispatch($file, $exception->reason, $exception->found, $during, $request->user());

        // The operations alert (SECURITY_MODEL section 9): the log channel of the server carries it.
        Log::critical('file.integrity_failed', [
            'file' => $file->public_id,
            'reason' => $exception->reason,
            'during' => $during,
            'request_id' => RequestId::of($request),
        ]);
        report($exception);

        App::setLocale(SetLocale::resolve($request));

        $referer = (string) $request->headers->get('referer');
        $sameOrigin = str_starts_with($referer.'/', $request->getSchemeAndHttpHost().'/');

        return Inertia::render('errors/show', [
            'status' => 500,
            'locale' => App::getLocale(),
            'returnUrl' => $sameOrigin ? $referer : null,
            'requestId' => RequestId::of($request),
            'description' => __('files.integrity_failed'),
        ])
            ->toResponse($request)
            ->setStatusCode(500);
    }

    /**
     * A fallback name of ASCII only, as the disposition header needs one beside the UTF-8 name.
     */
    private static function asciiName(string $name): string
    {
        $ascii = (string) preg_replace('/[^\x20-\x7e]/', '_', $name);
        $ascii = str_replace(['"', '\\', '%'], '_', $ascii);

        return trim($ascii) === '' ? 'berkas' : $ascii;
    }
}
