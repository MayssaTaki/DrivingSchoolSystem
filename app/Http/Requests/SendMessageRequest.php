<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest {
   public function rules() {
    return [
        'receiver_id' => 'required|exists:users,id',
        'content'     => 'nullable|required_without:file|string|max:1000',
        'file'        => 'nullable|file|max:2048|mimes:jpg,jpeg,png,pdf',
    ];
}

public function messages() {
    return [
        'receiver_id.required' => 'الرجاء تحديد مستقبل الرسالة',
        'receiver_id.exists'   => 'المستخدم المحدد غير موجود',
        'content.required_without' => 'الرجاء كتابة نص الرسالة أو إرفاق ملف',
        'file.max'            => 'حجم الملف يجب ألا يتجاوز 2 ميغابايت',
        'file.mimes'          => 'يُسمح فقط بالصور أو PDF',
    ];
}
}
