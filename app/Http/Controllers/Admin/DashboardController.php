<?php

namespace App\Http\Controllers\Admin;

use App\Models\TeachAddress;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    public function dashboard()
    {

        for ($i = 7; $i > 0; --$i) {
            $startDay = Carbon::today()->subDays($i);
            $endDay   = Carbon::today()
                ->subDays($i)
                ->endOfDay();

            $addressCount = TeachAddress::where('created_at', '>', $startDay)
                ->where('created_at', '<', $endDay)
                ->count();

            $addressData[] = [
                'y'     => (string) $startDay,
                'value' => $addressCount,
            ];
        }

        // 小B用户统计
        $allCount                               = TeachAddress::count();
        $statisticData['addresses']['dayCount'] = json_encode($addressData);
        $statisticData['addresses']['total']    = $allCount;

        for ($i = 7; $i > 0; --$i) {
            $startDay = Carbon::today()->subDays($i);
            $endDay   = Carbon::today()
                ->subDays($i)
                ->endOfDay();

            $noApprovalCount = TeachAddress::where('created_at', '>', $startDay)
                ->where('created_at', '<', $endDay)
                ->where('status', 'NO_APPROVAL')
                ->count();

            $noApprovalData[] = [
                'y'     => (string) $startDay,
                'value' => $noApprovalCount,
            ];
        }

        // 小B用户统计
        $noCount = TeachAddress::where('status', 'NO_APPROVAL')->count();

        $statisticData['no_address']['dayCount'] = json_encode($noApprovalData);
        $statisticData['no_address']['total']    = $noCount;

        return view('admin.dashboard.dashboard', compact('statisticData'));
    }
}
