<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PerStudentInvoice extends Model
{
    // per_student_invoices lives on the MAIN database
    protected $connection = 'pgsql';

    protected $fillable = [
        'tenant_id',
        'academic_period_id',
        'student_count',
        'rate_per_student',
        'tier_id',
        'amount',
        'status',
        'invoice_number',
        'paystack_reference',
        'paid_at',
        'due_date',
        'term_label',
    ];

    protected $casts = [
        'student_count'     => 'integer',
        'rate_per_student'  => 'float',
        'amount'            => 'float',
        'paid_at'           => 'datetime',
        'due_date'           => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', now()->toDateString());
            });
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Generate a new invoice for a school's current term.
     * Uses Tenant::calculateTermAmount() to get student count, rate, and amount.
     * Returns the created invoice, or null if school is not on per-student billing.
     */
    public static function generateForTerm(Tenant $tenant): ?self
    {
        if (!$tenant->isPerStudentBilling()) {
            return null;
        }

        $billing = $tenant->calculateTermAmount();

        if ($billing['model'] !== 'per_student' || $billing['student_count'] === 0) {
            return null;
        }

        // Check if an invoice already exists for this term (avoid duplicates)
        $existing = static::forTenant($tenant->id)
            ->where('student_count', $billing['student_count'])
            ->where('rate_per_student', $billing['rate'])
            ->whereIn('status', ['pending', 'paid'])
            ->latest()
            ->first();

        if ($existing) {
            return $existing; // Already invoiced for this term
        }

        // Get current academic period label
        $termLabel = 'Current Term';
        $periodId = null;
        try {
            config(['database.connections.tenant' => $tenant->dbConfig()]);
            \Illuminate\Support\Facades\DB::purge('tenant');
            \Illuminate\Support\Facades\DB::reconnect('tenant');

            $period = \Illuminate\Support\Facades\DB::connection('tenant')
                ->table('academic_periods')
                ->where('is_active', true)
                ->first();

            if ($period) {
                $periodId = $period->id;
                $session = \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('academic_sessions')
                    ->where('id', $period->academic_session_id ?? null)
                    ->first();

                $termLabel = trim(($period->name ?? $period->month ?? 'Term') .
                    ($session ? ', ' . $session->year : ''));
            }
        } catch (\Throwable $e) {}

        // Due date: 14 days from now
        $dueDate = now()->addDays(14)->toDateString();

        $invoice = static::create([
            'tenant_id'         => $tenant->id,
            'academic_period_id' => $periodId,
            'student_count'     => $billing['student_count'],
            'rate_per_student'  => $billing['rate'],
            'tier_id'           => $billing['tier']['id'] ?? null,
            'amount'            => $billing['term_amount'],
            'status'            => 'pending',
            'invoice_number'    => static::generateInvoiceNumber(),
            'due_date'          => $dueDate,
            'term_label'        => $termLabel,
        ]);

        // Cache the invoice info on the tenant
        $tenant->update([
            'last_invoice_amount'        => $billing['term_amount'],
            'last_invoice_student_count' => $billing['student_count'],
            'last_invoiced_at'           => now(),
        ]);

        return $invoice;
    }

    /**
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        return 'INV-' . date('Y') . '-' . strtoupper(Str::random(8));
    }

    /**
     * Check if a school has any overdue invoices.
     */
    public static function hasOverdue(int $tenantId): bool
    {
        return static::forTenant($tenantId)
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->exists();
    }

    /**
     * Mark an invoice as paid.
     */
    public function markAsPaid(string $paystackRef = null): void
    {
        $this->update([
            'status'             => 'paid',
            'paystack_reference' => $paystackRef,
            'paid_at'            => now(),
        ]);
    }
}
