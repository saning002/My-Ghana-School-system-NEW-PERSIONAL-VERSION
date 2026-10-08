<?php

namespace App\Http\Controllers;

use App\Services\DocumentVerificationService;

class DocumentVerificationController extends Controller
{
    public function __construct(private DocumentVerificationService $docService) {}

    public function show(string $uuid)
    {
        $doc = $this->docService->find($uuid);
        return view('verify.document', compact('doc'));
    }
}
