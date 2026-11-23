<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Records — {{ $schoolName }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; }
@page { size: A4 portrait; margin: 12mm 12mm 10mm 12mm; }
.hdr { text-align:center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
.hdr .school { font-size: 15pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
.hdr .sub    { font-size: 9pt; color: #444; margin-top: 2px; }
.hdr .title  { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin-top: 6px;
               border-top: 1px solid #ccc; padding-top: 5px; }
.meta { display: flex; justify-content: space-between; font-size: 8.5pt; color: #555;
        margin-bottom: 8px; }
.filter-label { font-size: 8.5pt; font-weight: bold; color: #333; margin-bottom: 8px; }
table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
thead tr { background: #f0f0f0; }
th { border: 1px solid #bbb; padding: 5px 7px; text-align: left; font-weight: bold;
     text-transform: uppercase; font-size: 7.5pt; letter-spacing: .04em; }
td { border: 1px solid #ddd; padding: 5px 7px; vertical-align: top; }
tr:nth-child(even) td { background: #fafafa; }
.amount { text-align: right; font-weight: bold; }
.total-row td { background: #f0fdf4; font-weight: bold; border-top: 2px solid #aaa; }
.total-row .amount { color: #166534; font-size: 10pt; }
.footer { margin-top: 16px; font-size: 8pt; color: #666; text-align: center;
          border-top: 1px solid #ccc; padding-top: 6px; }
@media print {
    .no-print { display: none; }
}
</style>
</head>
<body>

<div class="no-print" style="padding:12px;background:#f1f5f9;text-align:center;margin-bottom:10px;">
    <button onclick="window.print()" style="padding:8px 24px;background:#1e40af;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:700;">
        <span>🖨 Print / Save PDF</span>
    </button>
    <button onclick="window.close()" style="margin-left:10px;padding:8px 16px;background:#64748b;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;">
        Close
    </button>
</div>

<div class="hdr">
    <div class="school">{{ $schoolName }}</div>
    @if($schoolSubtitle)<div class="sub">{{ $schoolSubtitle }}</div>@endif
    <div class="title">Fee Payment Records</div>
</div>

<div class="meta">
    <span>Printed: {{ now()->format('d M Y, h:i A') }}</span>
    <span>Total Records: {{ $payments->count() }}</span>
</div>

@if($filterLabel)
<div class="filter-label">Filter: {{ $filterLabel }}</div>
@endif

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Time</th>
            <th>Student Name</th>
            <th>Student ID</th>
            <th>Programme</th>
            <th class="amount">Amount (GH₵)</th>
            <th>Method</th>
            <th>Receipt #</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payments as $i => $pmt)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($pmt->payment_date)->format('d/m/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($pmt->created_at)->format('h:i A') }}</td>
            <td><strong>{{ $pmt->student?->user?->full_name ?? '—' }}</strong></td>
            <td style="font-family:monospace;">{{ $pmt->student?->student_id ?? '—' }}</td>
            <td>{{ $pmt->student?->program?->name ?? '—' }}</td>
            <td class="amount">{{ number_format($pmt->amount_paid, 2) }}</td>
            <td>{{ ucwords(str_replace('_', ' ', $pmt->payment_method ?? 'cash')) }}</td>
            <td style="font-family:monospace;font-size:7.5pt;">{{ $pmt->receipt_number ?? '—' }}</td>
            <td style="font-size:7.5pt;color:#555;">{{ $pmt->notes ?? '' }}</td>
        </tr>
        @empty
        <tr><td colspan="10" style="text-align:center;color:#888;padding:16px;">No payment records.</td></tr>
        @endforelse

        {{-- Total row --}}
        @if($payments->count())
        <tr class="total-row">
            <td colspan="6" style="text-align:right;font-weight:bold;">TOTAL</td>
            <td class="amount">{{ number_format($total, 2) }}</td>
            <td colspan="3"></td>
        </tr>
        @endif
    </tbody>
</table>

<div class="footer">
    {{ $schoolName }} · Fee Payment Records · Generated {{ now()->format('d M Y') }}
</div>

<script>
window.onload = function() {
    // Auto-print on open (can be suppressed by user)
};
</script>
</body>
</html>
