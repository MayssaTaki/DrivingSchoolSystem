<?php

namespace App\Services;
use Illuminate\Support\Str;
use App\Repositories\Contracts\PaymentTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use App\Services\Interfaces\MtnPaymentClientServiceInterface;
use App\Exports\ReportMonthlyPaymentExport;
use App\Exports\ReportdailyPaymentExport;
use App\Exports\LicensePaymentsdailyExport;
use App\Exports\LicensePaymentsExport;
use App\Exports\BookingPaymentsExport;
use App\Exports\BookingPaymentsdailyExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Booking;
use App\Models\LicenseRequest;

class MtnPaymentClientService implements MtnPaymentClientServiceInterface
{
    protected $baseUrl;
    protected $terminalId;
    protected $privateKeyPath;
    protected $lang;
 protected $repo;
    public function __construct(PaymentTransactionRepositoryInterface $repo)
    {
        $this->baseUrl        = env('MTN_BASE_URL', 'https://cashmobile.mtnsyr.com:9000');
        $this->terminalId     = env('MTN_TERMINAL'); 
$this->privateKeyPath = base_path(env('MTN_PRIVATE_KEY_PATH', 'keys/mtn_private.pem'));
        $this->lang           = env('MTN_LANG', 'ar');
                $this->repo           = $repo;

    }

    protected function sign(array $body): string
    {
        $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $pkey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));
        if (!$pkey) {
            throw new \Exception("Private key not loaded");
        }

        $signature = '';
        if (!openssl_sign($json, $signature, $pkey, OPENSSL_ALGO_SHA256)) {
            throw new \Exception("Signing failed");
        }

        openssl_free_key($pkey);

        return base64_encode($signature);
    }

    protected function headers(string $requestName, string $xSignature): array
    {
        return [
            'Request-Name'    => $requestName,
            'Subject'         => $this->terminalId,
            'X-Signature'     => $xSignature,
            'Accept-Language' => $this->lang,
            'Content-Type'    => 'application/json; charset=utf-8',
        ];
    }

    protected function request(string $requestName, array $body)
    {
        $signature = $this->sign($body);
        $url = rtrim($this->baseUrl, '/');

        $response = Http::withHeaders($this->headers($requestName, $signature))
            ->asJson()
            ->post($url, $body);

        return [
            'status' => $response->status(),
            'json'   => $response->json(),
        ];
    }

   public function createInvoice(int $amountSyp, int $ttl = 15)
{
    $amountMinUnits = $amountSyp * 100;
    $invoiceId = time();

    $body = [
        'Invoice' => $invoiceId,
        'Amount'  => $amountMinUnits,
        'TTL'     => $ttl,
    ];

    $resp = $this->request('pos_web/invoice/create', $body);
  $this->repo->create([
            'invoice_id'   => $invoiceId,
            'amount'       => $amountSyp,
            'status'       => $resp['status'] == 200 ? 'created' : 'failed',
            'raw_response' => json_encode($resp, JSON_UNESCAPED_UNICODE),
        ]);
    return [
        'invoiceId' => $invoiceId,
        'apiResponse' => $resp,
    ];
}

public function initiatePayment(array $data)
{
    $guid = (string) Str::uuid();

    $body = [
        'Invoice' => (int)$data['invoiceId'],
        'Phone'   => $data['phone'],
        'Guid'    => $guid,
    ];

    $resp = $this->request('pos_web/payment_phone/initiate', $body);

    $this->repo->updateStatus($data['invoiceId'], 'initiated', $resp);
    $this->repo->findByInvoice($data['invoiceId'])?->update(['guid' => $guid]);
    if (!empty($resp['json']['OperationNumber'])) {
        $this->repo->updateOperationNumber($data['invoiceId'], $resp['json']['OperationNumber']);
    }

   
    return [
        'invoiceId' => $data['invoiceId'],
        'guid'      => $guid,
        'apiResponse' => $resp,
    ];
}

public function confirmPayment(array $data)
{
    $hashedOtp = base64_encode(hash('sha256', $data['otp'], true));

    $body = [
        'Invoice'         => (int)$data['invoiceId'],
        'Phone'           => $data['phone'],
        'Guid'            => $data['guid'],
        'OperationNumber' => (int)$data['operationNumber'],
        'Code'            => $hashedOtp,
    ];

    $resp = $this->request('pos_web/payment_phone/confirm', $body);

      $this->repo->updateStatus(
            $data['invoiceId'],
            $resp['status'] == 200 ? 'confirmed' : 'failed',
            $resp
        );

          if (!empty($resp['json']['OperationNumber'])) {
        $this->repo->updateOperationNumber($data['invoiceId'], $resp['json']['OperationNumber']);
    }
    return [
        'invoiceId' => $data['invoiceId'],
        'apiResponse' => $resp,
    ];
}

public function getInvoice(int $invoiceId, ?string $transaction = null)
{
    $body = [
        'Invoice'     => $invoiceId,
    ];

    if ($transaction) {
        $body['Transaction'] = $transaction;
    }

    $resp = $this->request('pos_web/invoice/get', $body);

    return [
        'invoiceId'   => $invoiceId,
        'apiResponse' => $resp,
    ];
}

public function initiateRefund(array $data)
{
    $body = [
        'Invoice' => (int) $data['invoiceId'],
    ];

    $resp = $this->request('pos_web/invoice/refund/initiate', $body);

    $this->repo->updateStatus($data['invoiceId'], 'refund_initiated', $resp);

    return [
        'invoiceId'   => $data['invoiceId'],
        'apiResponse' => $resp,
    ];
}

public function confirmRefund(array $data)
{
    $body = [
        'BaseInvoice'   => (int) $data['baseInvoice'],
        'RefundInvoice' => (int) $data['refundInvoice'],
    ];

    $resp = $this->request('pos_web/invoice/refund/confirm', $body);

    $this->repo->updateStatus($data['baseInvoice'], 'refund_confirmed', $resp);

    return [
        'baseInvoice'   => $data['baseInvoice'],
        'refundInvoice' => $data['refundInvoice'],
        'apiResponse'   => $resp,
    ];
}
    public function activate(string $secret = null)
    {
        $body = [
            'Terminal' => $this->terminalId,
            'Secret'   => $secret ?? env('MTN_ACTIVATION_SECRET'),
        ];

        return $this->request('pos_web/terminal/activate', $body);
    }

    public function getMonthlyFinancialReport()
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


    $report = PaymentTransaction::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw("SUM(CASE WHEN status = 'confirmed' THEN amount ELSE 0 END) as total_paid"),
            DB::raw("SUM(CASE WHEN status = 'refund_confirmed' THEN amount ELSE 0 END) as total_refunded")
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();

    $report->map(function ($item) use ($months) {
        $item->month_name = $months[(int)$item->month] ?? $item->month;
        return $item;
    });

    return $report;
}

   public function getDailyFinancialReport()
{              $days = [
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
    ->get();
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
      public function exportReportMonthlyPayment()
{
    return Excel::download(new ReportMonthlyPaymentExport(), 'Report_Monthly_Payment.xlsx');
}

      public function exportReportdailyPayment()
{
    return Excel::download(new ReportdailyPaymentExport(), 'Report_daily_Payment.xlsx');
}
 public function getMonthlyLicensePayments()
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

        return $report->map(function ($item) {
            return [
                'year' => $item->year,
                'month' => $item->month,
                'month_name' => $this->months[(int)$item->month] ?? $item->month,
                'total_paid' => $item->total_paid,
                'total_refunded' => $item->total_refunded,
            ];
        });
    }
    public function getdailyLicensePayments()
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
        $report = LicenseRequest::join('payment_transactions as pt', 'license_requests.payment_transaction_id', '=', 'pt.id')
            ->select(
                DB::raw('DATE(pt.created_at) as date'),
                DB::raw('DAYOFWEEK(pt.created_at) as day'),
                DB::raw("SUM(CASE WHEN pt.status = 'confirmed' THEN pt.amount ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN pt.status = 'refund_confirmed' THEN pt.amount ELSE 0 END) as total_refunded")
            )
             ->groupBy('date', 'day')  
    ->orderBy('date', 'desc')
    ->get();

        return $report->map(function ($item)use ($days) {
            return [
                 'date'          => $item->date,
        'day_name'      => $days[(int)$item->day] ?? $item->day,
                'total_paid' => $item->total_paid,
                'total_refunded' => $item->total_refunded,
            ];
        });
    }
public function exportLicensePayments()
{
    return Excel::download(new LicensePaymentsExport(), 'License_Payments.xlsx');
}
public function exportLicensePaymentsdaily()
{
    return Excel::download(new LicensePaymentsdailyExport(), 'License_Payments_daily.xlsx');
}
 public function getMonthlyBookingPayments()
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
        $report = Booking::join('payment_transactions as pt', 'bookings.payment_transaction_id', '=', 'pt.id')
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

        return $report->map(function ($item) {
            return [
                'year' => $item->year,
                'month' => $item->month,
                'month_name' => $this->months[(int)$item->month] ?? $item->month,
                'total_paid' => $item->total_paid,
                'total_refunded' => $item->total_refunded,
            ];
        });
    }
 public function getdailyBookingPayments()
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
        $report = Booking::join('payment_transactions as pt', 'bookings.payment_transaction_id', '=', 'pt.id')
            ->select(
                DB::raw('DATE(pt.created_at) as date'),
                DB::raw('DAYOFWEEK(pt.created_at) as day'),
                DB::raw("SUM(CASE WHEN pt.status = 'confirmed' THEN pt.amount ELSE 0 END) as total_paid"),
                DB::raw("SUM(CASE WHEN pt.status = 'refund_confirmed' THEN pt.amount ELSE 0 END) as total_refunded")
            )
             ->groupBy('date', 'day')  
    ->orderBy('date', 'desc')
    ->get();

        return $report->map(function ($item)use ($days) {
            return [
                 'date'          => $item->date,
        'day_name'      => $days[(int)$item->day] ?? $item->day,
                'total_paid' => $item->total_paid,
                'total_refunded' => $item->total_refunded,
            ];
        });
    }
    public function exportBookingPayments()
{
    return Excel::download(new BookingPaymentsExport(), 'Booking_Payments.xlsx');
}
    public function exportBookingPaymentsdaily()
{
    return Excel::download(new BookingPaymentsdailyExport(), 'Booking_Payments_daily.xlsx');
}
}
