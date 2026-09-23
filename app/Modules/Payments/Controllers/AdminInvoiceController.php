<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Models\Invoice;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
    /**
     * List all invoices across all tenants (System Admin only).
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['company', 'payment'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('modules.payments.admin.invoices', compact('invoices'));
    }

    /**
     * Show invoice detail.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['company', 'payment', 'items']);
        return view('modules.payments.admin.invoice-show', compact('invoice'));
    }
}
