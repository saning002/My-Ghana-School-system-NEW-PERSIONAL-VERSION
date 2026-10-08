<?php

namespace App\Services;

use App\Models\GeneratedDocument;
use Illuminate\Support\Str;

class DocumentVerificationService
{
    /**
     * Record a generated document and return the model (with uuid for QR).
     */
    public function record(
        string $type,
        string $title,
        ?int $studentId = null,
        ?int $periodId = null,
        array $meta = []
    ): GeneratedDocument {
        $user = auth()->user();
        return GeneratedDocument::create([
            'uuid'               => (string) Str::uuid(),
            'document_type'      => $type,
            'title'              => $title,
            'student_id'         => $studentId,
            'academic_period_id' => $periodId,
            'generated_by'       => $user?->id ?? 0,
            'generated_by_name'  => $user?->full_name ?? 'System',
            'status'             => 'valid',
            'meta'               => $meta,
        ]);
    }

    /**
     * Generate a simple QR code URL using the free qrserver API (no package needed).
     */
    public function qrUrl(GeneratedDocument $doc, int $size = 120): string
    {
        $verifyUrl = urlencode($doc->verify_url);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$verifyUrl}";
    }

    /**
     * Find a document by UUID for public verification.
     */
    public function find(string $uuid): ?GeneratedDocument
    {
        return GeneratedDocument::with(['student.user', 'period.session'])
            ->where('uuid', $uuid)
            ->first();
    }
}
