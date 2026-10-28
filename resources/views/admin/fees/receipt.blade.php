!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->receipt_number ?? 'N/A' }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI'?'N/A' }}</div>
            <div class="date">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y'?'–' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Student ID</div>
                <div class="value">{{ $student?->student_id ?? '–' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Programme</div>
                <div class="value">{{ $student?->program?->name ?? '–' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Academic Session</div>
                <div class="value">{{ $session?->year ?? 'Not specified' }}</div>
            </div>
        </div>

        {{-- Payer Info --}}
        @if($payment->payer_name || $payment->payer_phone)
        <div class="section-title">Paid By</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Payer Name</div>
                <div class="value">{{ $payment->payer_name ?? '–' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Phone</div>
                <div class="value">{{ $payment->payer_phone ?? '–' }}</div>
            </div>
        </div>
        @endif

        {{-- Payment Details --}}
        <div class="section-title">Payment Details</div>
        <table class="fee-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Payment Method</th>
                    <th class="amount">Amount (GHS)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        School Fees Payment
                        @if($session) – {{ $session->year }} @endif
                        @if($payment->notes) <br><small style="color:#94a3b8;">{{ $payment->notes }}</small> @endif
                    </td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'cash')) }}</td>
                    <td class="amount">{{ number_format($payment->amount_paid, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total Amount Paid</td>
                    <td class="amount">GHS {{ number_format($payment->amount_paid, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Amount highlight --}}
        <div class="amount-box">
            <div>
                <div class="label">Amount Paid</div>
                <div style="font-size:11px;opacity:0.6;margin-top:2px;">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'cash')) }}</div>
            </div>
            <div class="value">GHS {{ number_format($payment->amount_paid, 2) }}</div>
        </div>

    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
        <div>{{ $schoolName }} – Official Receipt</div>
        <div>Generated: {{ now()->format('d M Y H:i') }}</div>
        <div>Receipt: {{ $payment->receipt_number ?? 'N/A' }}</div>
    </div>

</div>

<div class="print-actions">
    <button class="btn-print" onclick="window.print()"><i>?Y-?</i> Print Receipt</button>
    <a href="{{ url()->previous() }}" class="btn-back">–? Back</a>
</div>

</body>
</html>
