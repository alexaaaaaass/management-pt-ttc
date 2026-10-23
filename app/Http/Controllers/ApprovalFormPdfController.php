<?php

namespace App\Http\Controllers;

use App\Models\ApprovalForm;
use Barryvdh\DomPDF\Facade\Pdf;

class ApprovalFormPdfController extends Controller
{
    protected function getSafeFilename(ApprovalForm $approvalForm): string
    {
        $sanitized = str_replace(['/', '\\'], '-', $approvalForm->form_no);
        return 'ApprovalForm-' . $sanitized . '.pdf';
    }

    public function preview(ApprovalForm $approvalForm)
    {
        $approvalForm->loadMissing(['customer', 'items.finishGoodItem']);

        $pdf = Pdf::loadView('pdf.approval-form', compact('approvalForm'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream($this->getSafeFilename($approvalForm));
    }

    public function download(ApprovalForm $approvalForm)
    {
        $approvalForm->loadMissing(['customer', 'items.finishGoodItem']);

        $pdf = Pdf::loadView('pdf.approval-form', compact('approvalForm'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($this->getSafeFilename($approvalForm));
    }
}
