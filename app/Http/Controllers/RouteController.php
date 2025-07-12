<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DefineRouteRequest;
use App\Services\Interfaces\RouteServiceInterface;

class RouteController extends Controller
{
    public function __construct(protected RouteServiceInterface $routeService) {}

    public function defineRoute($bookingId, DefineRouteRequest $request)
{
    try {
        $route = $this->routeService->defineRouteForBooking($bookingId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد مسار الجلسة بنجاح.',
            'data' => $route
        ]);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'ليس لديك صلاحية تحديد مسار لهذه الجلسة.'
        ], 403);
    } catch (\Exception $e) {
        if ($e->getMessage() === 'يمكن تحديد المسار فقط للجلسات المحجوزة.') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تحديد المسار إلا للجلسات التي حالتها محجوزة فقط.'
            ], 400);
        }

        if ($e->getMessage() === 'تم تحديد المسار من قبل لهذا الحجز.') {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بتحديد المسار مسبقًا لهذا الحجز ولا يمكن التكرار.'
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ غير متوقع أثناء تحديد المسار.',
            'error'   => $e->getMessage()
        ], 500);
    }
}

}
