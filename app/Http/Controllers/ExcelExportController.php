<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportController extends Controller
{
    public function usersExport(Request $request) 
    {
        $agentId = $request->agent_id;
        $subAgentId = $request->sub_agent_id;
        $month = $request->month;
        return Excel::download(new UsersExport($agentId,$subAgentId,$month), 'users.xlsx');
    }
}
