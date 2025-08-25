<?php

namespace App\Exports;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\LicenseRequest;
class LicensePaymentsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $months = [
    1  => 'كانون الثاني',
    2  => 'شباط',
    3  => 'آذار',
    4  => 'نيسان',
    5  => 'أيار',
    6  => 'حزيران',
    7  => 'تموز',
    8  => 'آب',
    9  => 'أيلول',
    10 => 'تشرين الأول',
    11 => 'تشرين الثاني',
    12 => 'كانون الأول',
];


  $report = LicenseRequest::join('payment_transactions as pt', 'license_requests.payment_transaction_id', '=', 'pt.id')
    ->select(
        DB::raw('YEAR(pt.created_at) as year'),
        DB::raw('MONTH(pt.created_at) as month'),
        DB::raw("SUM(CASE WHEN pt.status = 'confirmed' THEN pt.amount ELSE 0 END) as total_paid"),
        DB::raw("SUM(CASE WHEN pt.status = 'refund_confirmed' THEN pt.amount ELSE 0 END) as total_refunded")
    )
    ->groupBy('year', 'month')
    ->orderBy('year', 'desc')
    ->orderBy('month', 'desc')
    ->get();


   $report = $report->map(function ($item) use ($months) {
    return [
        'year'          => $item->year,
        'month'         => $item->month,
        'month_name'    => $months[(int)$item->month] ?? $item->month,
        'total_paid'    => $item->total_paid,
        'total_refunded'=> $item->total_refunded,
    ];
});


    return $report;
    }

    public function headings(): array
    {
        return [
            'السنة',
            'الشهر',
            'اسم الشهر',
            'إجمالي المدفوع',
            'إجمالي المسترد',
        ];
    }
}
