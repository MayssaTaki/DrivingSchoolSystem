<?php

namespace App\Exports;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportdailyPaymentExport implements FromCollection, WithHeadings
{
    public function collection()
    {
 $days = [
    1 => 'الأحد',
    2 => 'الاثنين',
    3 => 'الثلاثاء',
    4 => 'الأربعاء',
    5 => 'الخميس',
    6 => 'الجمعة',
    7 => 'السبت',
];


       $report = PaymentTransaction::select(
            DB::raw('DATE(created_at) as date'),
                DB::raw('DAYOFWEEK(created_at) as day'),
            DB::raw("SUM(CASE WHEN status = 'confirmed' THEN amount ELSE 0 END) as total_paid"),
            DB::raw("SUM(CASE WHEN status = 'refund_confirmed' THEN amount ELSE 0 END) as total_refunded")
        )
     ->groupBy('date', 'day')  
    ->orderBy('date', 'desc')
    ->get();;


$report = $report->map(function ($item) use ($days) {
    return [
        'date'          => $item->date,
        'day_name'      => $days[(int)$item->day] ?? $item->day,
        'total_paid'    => $item->total_paid,
        'total_refunded'=> $item->total_refunded,
    ];
});


    return $report;
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'اسم اليوم',
            'إجمالي المدفوع',
            'إجمالي المسترد',
        ];
    }
}
