<?php

namespace App\Http\Controllers;

use App\Exports\InquiryDetailsExport;
use App\Models\InquiryDetails;
use App\Models\SystemLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $inquiryCounts = InquiryDetails::select('status_id', DB::raw('count(*) as count'))
            ->whereIn('status_id', [1, 2, 3, 4])
            ->groupBy('status_id')
            ->pluck('count', 'status_id')
            ->toArray();

        $pendingInquiry = $inquiryCounts[1] ?? 0;
        $completeInquiry = $inquiryCounts[2] ?? 0;
        $rejectedInquiry = $inquiryCounts[3] ?? 0;
        $confirmInquiry = $inquiryCounts[4] ?? 0;

        return view('dashboard')->with(compact('pendingInquiry', 'completeInquiry', 'rejectedInquiry', 'confirmInquiry'));
    }

    public function inquiryDetails(Request $request)
    {
        if ($request->ajax()) {
            $inquiry = InquiryDetails::query();

            if (isset($request->actionType) && $request->actionType == 'report') {
                if (!empty($request->startDate) && !empty($request->endDate)) {
                    $inquiry = $inquiry->whereBetween(DB::raw('DATE(created_at)'), [$request->startDate, $request->endDate]);
                }

                if (!empty($request->statusId)) {
                    $inquiry = $inquiry->where('status_id', $request->statusId);
                }
            } else {
                $inquiry = $inquiry->where('status_id', '1');
            }

            //return dd($inquiry->toRawSql());
            return DataTables::of($inquiry)
                ->addIndexColumn()
                ->addColumn('display_status', function ($row) {
                    $class = 'warning';
                    if ($row->status_id == '2') {
                        $class = 'success';
                    } else if ($row->status_id == '3') {
                        $class = 'danger';
                    } else if ($row->status_id == '4') {
                        $class = 'info';
                    }

                    $html = '<span class="badge text-bg-' . $class . '">' . $this->getArrayNameById($this->statusArray, $row->status_id) . '</span>';
                    if ($row->status_id == '4') {
                        $html .= '<br><span class="link-primary">' . $this->formatDateTime('d M, Y h:i A', $row->confirm_date) . '</span>';
                    }
                    return $html;
                })
                ->addColumn('display_inquiry_date', function ($row) {
                    return $this->formatDateTime('d M, Y h:i A', $row->created_at);
                })
                ->addColumn('action', function ($row) {
                    if ($row->status_id == '1') {
                        return '<div class="btn-group"> <button type="button" class="btn btn-light dropdown-toggle"
                                    style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                    data-bs-toggle="dropdown" aria-expanded="false"> Action </button>
                                <ul class="dropdown-menu" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                    <li> <a class="dropdown-item change-status" data-id="' . $row->id . '" href="javascript:void(0);">Status</a></li>
                                </ul>
                            </div>';
                    } else {
                        return '';
                    }

                })
                ->rawColumns(['display_status', 'action', 'display_inquiry_date'])
                ->make(true);
        }

        return view('inquiry');
    }

    public function changeStatus(Request $request)
    {
        $save = InquiryDetails::where('id', $request->id)->update(['status_id' => $request->statusId]);

        if ($request->statusId == '4') {
            InquiryDetails::where('id', $request->id)->update(['confirm_date' => $this->formatDateTime(mDateTime: $request->confirmDate)]);
        }

        if ($save) {
            $inquiryDetails = InquiryDetails::find($request->id);
            $message = '';
            if ($request->statusId == '2') { // Completed
                $message = 'Your service completed for #' . $inquiryDetails->inquiry_no . "\n";
                $message .= "Name: " . $inquiryDetails->name . "\n";
                $message .= "Mobile: " . $inquiryDetails->mobile . "\n";
                $message .= "Vehicle No: " . $inquiryDetails->vehicle_no . "\n";
            } else if ($request->statusId == '4') { // Confirmed
                $message = 'Your booking confirmed as #' . $inquiryDetails->inquiry_no . "\n";
                $message .= "Date: " . $this->formatDateTime('d M, Y h:i A', $inquiryDetails->confirm_date) . "\n";
                $message .= "Name: " . $inquiryDetails->name . "\n";
                $message .= "Mobile: " . $inquiryDetails->mobile . "\n";
                $message .= "Vehicle No: " . $inquiryDetails->vehicle_no . "\n";
                $message .= "Service Type: " . $this->serviceTypeArray[$inquiryDetails->service_type_id] . "\n";
                $message .= "Location: " . $this->branchArray[$inquiryDetails->branch_id] . "\n";
            }

            $this->sendWhatsAppMessage($inquiryDetails->mobile, $message);
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

    public function export(Request $request)
    {
        try {    
            $exportStartDate = $request->input('exportStartDate');
            $exportEndDate = $request->input('exportEndDate');
            $exportStatusId = $request->input('exportStatusId', '');
           // dd($exportStartDate, $exportEndDate, $exportStatusId);
            return Excel::download(new InquiryDetailsExport($exportStartDate, $exportEndDate, $exportStatusId), 'inquiry.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
