<?php

namespace App\Exports;

use App\Models\BookingStatusLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingStatusLogsExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return BookingStatusLog::with(['booking.session', 'changer'])
            ->orderBy('changed_at', 'desc')
            ->get();
    }

    public function map($log): array
    {
        return [
            $log->id,
            optional($log->booking->session)->session_date,
            optional($log->booking->session)->start_time,
            $log->status,
            \Carbon\Carbon::parse($log->changed_at)->format('Y-m-d H:i:s'),
            optional($log->changer)->name,
            optional($log->changer)->role,
            \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Booking Date',
            'Booking Time',
            'Status',
            'Changed At',
            'Changed By',
            'Role',
            'Created At',
        ];
    }
}
