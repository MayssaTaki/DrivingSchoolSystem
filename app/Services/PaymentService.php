<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\Interfaces\PaymentServiceInterface;
class PaymentService implements PaymentServiceInterface {
    protected $repo;
    private $privateKey;
    public function __construct(PaymentRepositoryInterface $repo) {
        $this->repo = $repo;
        $this->privateKey = config('mtn.private_key');
    }

    protected function signPayload(array $payload): string {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $binary = hash('sha256', $json, true);
        openssl_sign($binary, $sig, $this->privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($sig);
    }

    public function activateTerminal(string $publicKey, string $secret, string $serial): array {
        $payload = ['Key'=>$publicKey,'Secret'=>$secret,'Subject'=>config('mtn.terminal_id'),'Serial'=>$serial];
        $sig = $this->signPayload($payload);
        $resp = Http::withHeaders([
            'Request-Name'=>'pos_web/pos/activate','Subject'=>config('mtn.terminal_id'),'X-Signature'=>$sig
        ])->post(config('mtn.base_url').'/pos/activate',$payload)->json();
        return $resp;
    }

    public function createInvoice(int $amount): array {
        $invoiceId = 'INV'.uniqid();
        $payload = ['Amount'=>$amount*100,'Invoice number'=>$invoiceId,'TTL'=>300];
        $sig = $this->signPayload($payload);
        $resp = Http::withHeaders([
            'Request-Name'=>'pos_web/invoice/create','Subject'=>config('mtn.terminal_id'),'X-Signature'=>$sig
        ])->post(config('mtn.base_url').'/invoice/create',$payload)->json();
        $this->repo->record(['invoice_id'=>$invoiceId,'amount'=>$amount,'status'=>$resp['Status']??'failed','raw_response'=>json_encode($resp)]);
        return ['invoice_id'=>$invoiceId,'response'=>$resp];
    }

    public function initiatePayment(string $invoiceId,string $guid,string $phone): array {
        $payload=['Invoice number'=>$invoiceId,'Guid'=>$guid,'Phone'=>$phone];
        $sig=$this->signPayload($payload);
        return Http::withHeaders([
            'Request-Name'=>'pos_web/payment_phone/initiate','Subject'=>config('mtn.terminal_id'),'X-Signature'=>$sig
        ])->post(config('mtn.base_url').'/payment_phone/initiate',$payload)->json();
    }

    public function confirmPayment(string $invoiceId,string $guid,string $otp): array {
        $hashed = base64_encode(hash('sha256',$otp,true));
        $payload=['Invoice number'=>$invoiceId,'Guid'=>$guid,'OTP code'=>$hashed];
        $sig=$this->signPayload($payload);
        $resp = Http::withHeaders([
            'Request-Name'=>'pos_web/payment_phone/confirm','Subject'=>config('mtn.terminal_id'),'X-Signature'=>$sig
        ])->post(config('mtn.base_url').'/payment_phone/confirm',$payload)->json();
        if ($resp['Status']=='Success') $this->repo->updateStatus($invoiceId,'paid');
        return $resp;
    }

    public function initiateRefund(string $invoiceId,string $guid): array {
        $payload=['Invoice number'=>$invoiceId,'Guid'=>$guid];
        $sig=$this->signPayload($payload);
        return Http::withHeaders([
            'Request-Name'=>'pos_web/invoice/refund/initiate','Subject'=>config('mtn.terminal_id'),'X-Signature'=>$sig
        ])->post(config('mtn.base_url').'/invoice/refund/initiate',$payload)->json();
    }

    public function confirmRefund(string $invoiceId,string $guid,string $otp): array {
        $hashed=base64_encode(hash('sha256',$otp,true));
        $payload=['Invoice number'=>$invoiceId,'Guid'=>$guid,'OTP code'=>$hashed];
        $sig=$this->signPayload($payload);
        $resp=Http::withHeaders([
            'Request-Name'=>'pos_web/invoice/refund/confirm','Subject'=>config('mtn.terminal_id'),'X-Signature'=>$sig
        ])->post(config('mtn.base_url').'/invoice/refund/confirm',$payload)->json();
        if ($resp['Status']=='Refunded') $this->repo->updateStatus($invoiceId,'refunded');
        return $resp;
    }
}
