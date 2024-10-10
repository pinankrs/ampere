<?php

namespace App\Http\Controllers;

use App\Models\InquiryDetails;
use App\Models\SystemLogs;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return view('dashboard');
    }

    public function inquiryDetails(Request $request)
    {
        if ($request->ajax()) {
            $inquiry = InquiryDetails::query()->where('status_id', '1');
            return DataTables::of($inquiry)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group"> <button type="button" class="btn btn-light dropdown-toggle"
                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                data-bs-toggle="dropdown" aria-expanded="false"> Action </button>
                            <ul class="dropdown-menu" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <li> <a class="dropdown-item change-status" data-id="' . $row->id . '" href="javascript:void(0);">Status</a></li>
                            </ul>
                        </div>';
                })
                ->make(true);
        }

        return view('inquiry');
    }

    public function changeStatus(Request $request)
    {
        $save = InquiryDetails::where('id', $request->id)->update(['status_id' => $request->statusId]);
        if ($save) {
            SystemLogs::create([
                'inquiry_id' => $request->id,
                'remark' => 'Status changed to ' . $this->getArrayNameById($this->statusArray, $request->statusId),
                'action_id' => 3,
                'created_by' => auth()->id(),
            ]);
            return response()->json(['code' => 1, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['code' => 0, 'message' => 'Failed to update status']);
        }

    }
}
