<?php
namespace App\Repositories;

use App\Models\PaymentTransaction;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface {
    public function record(array $data){
        return PaymentTransaction::create($data);
    }
    public function updateStatus(string $invoiceId, string $status){
        return PaymentTransaction::where('invoice_id',$invoiceId)->update(['status'=>$status]);
    }
}
