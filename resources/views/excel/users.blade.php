<table>
    <thead>
        <tr>
            <th style="text-align: right;"><b>SN.</b></th>
            <th><b>User ID</b></th>
            <th><b>Role</b></th>
            <th><b>Name</b></th>
            <th><b>Mobile</b></th>
            <th><b>Email</b></th>
            <th><b>DOB</b></th>
            <th><b>Designation</b></th>
            <th><b>State</b></th>
            <th><b>City</b></th>
            <th><b>Pin Code</b></th>
            <th><b>Address</b></th>
            <th><b>Aadhar</b></th>
            <th><b>PAN</b></th>
            <th><b>Company</b></th>
            <th><b>GST</b></th>
            <th><b>Status</b></th>
            <th><b>Joining Date</b></th>
            <th><b>Rejected Reason</b></th>
            <th><b>Total Contacts</b></th>
            <th><b>Total Leads</b></th>
            <th><b>Total Deals</b></th>
        </tr>
    </thead>

    <tbody>

        @foreach($records as $user)

        <!-- LEADER HEADER -->
        <tr>
            <th colspan="22">
                <b>├── Leader {{ $loop->iteration }} / Total Contacts: {{ $user->contacts->count() }} / Total SubAgents: {{ $user->subAgents->count() }}</b>
            </th>
        </tr>

        <!-- LEADER DETAILS -->
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $user->user_id }}</td>
            <td>{{ $user->role }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->mobile }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->dob }}</td>
            <td>{{ $user->designation->name ?? '' }}</td>
            <td>{{ $user->state->name ?? '' }}</td>
            <td>{{ $user->city->name ?? '' }}</td>
            <td>{{ $user->pin_code }}</td>
            <td>{{ $user->address }}</td>
            <td>{{ $user->aadhar_no }}</td>
            <td>{{ $user->pan_no }}</td>
            <td>{{ $user->company_name }}</td>
            <td>{{ $user->gst_no }}</td>
            <td>{{ $user->status }}</td>
            <td>{{ $user->approved_date }}</td>
            <td>{{ $user->rejected_reason }}</td>
            <td>{{ $user->total_contacts }}</td>
            <td>{{ $user->total_leads }}</td>
            <td>{{ $user->total_deals }}</td>
        </tr>

        <!-- CONTACTS SECTION -->
        @if($user->contacts->count() > 0)
        <tr>
            <th></th>
            <th colspan="21"><b>│ ├── Contacts ({{ $user->contacts->count() }})</b></th>
        </tr>

        <tr>
            <th></th>
            <th style="text-align: right;"><b>SN.</b></th>
            <th><b>Name</b></th>
            <th><b>Mobile</b></th>
            <th><b>Project</b></th>
            <th><b>Remark</b></th>
            <th><b>Date</b></th>
            <th><b>Time</b></th>
            <th><b>Status</b></th>
            <td colspan="14"></td>
        </tr>
        @endif

        @foreach($user->contacts as $contact)
        <tr>
            <td></td>
            <td>{{ $loop->iteration }}</td>
            <td>&nbsp;&nbsp;&nbsp;{{ $contact->name }}</td>
            <td>{{ $contact->mobile }}</td>
            <td>{{ $contact->project->name ?? '' }}</td>
            <td>{{ $contact->remark }}</td>
            <td>{{ $contact->date }}</td>
            <td>{{ $contact->time }}</td>
            <td>{{ $contact->status }}</td>
            <td colspan="14"></td>
        </tr>

        <!-- CALL HISTORY SECTION -->
        @if($contact->contactCallHistories->count() > 0)

        <tr>
            <th></th>
            <th></th>
            <th colspan="20"><b>&nbsp;&nbsp;&nbsp;│ │ ├── Call History ({{ $contact->contactCallHistories->count() }}) for {{ $contact->name }}</b></th>
        </tr>

        <tr>
            <th></th>
            <th></th>
            <th style="text-align: right;"><b>SN.</b></th>
            <th><b>Date</b></th>
            <th><b>Remark</b></th>
            <th><b>Visited</b></th>
            <th><b>Status</b></th>
            <td colspan="15"></td>
        </tr>

        @endif

        @foreach($contact->contactCallHistories as $history)
        <tr>
            <td></td>
            <td></td>
            <td>{{ $loop->iteration }}</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $history->date }}</td>
            <td>Remark: {{ $history->remark }}</td>
            <td>Visited: {{ $history->visited ? 'Yes' : 'No' }}</td>
            <td>Status: {{ $history->status }}</td>
            <td colspan="15"></td>
        </tr>
        @endforeach

        @endforeach

        <!-- SUB AGENTS SECTION -->
        @if($user->subAgents->count() > 0)

        <tr>
            <th></th>
            <th colspan="21"><b>└── Sub Agents ({{ $user->subAgents->count() }})</b></th>
        </tr>

        <tr>
            <th></th>
            <th style="text-align: right;"><b>SN.</b></th>
            <th><b>User ID</b></th>
            <th><b>Role</b></th>
            <th><b>Name</b></th>
            <th><b>Mobile</b></th>
            <th><b>Email</b></th>
            <th><b>DOB</b></th>
            <th><b>Designation</b></th>
            <th><b>State</b></th>
            <th><b>City</b></th>
            <th><b>Pin Code</b></th>
            <th><b>Address</b></th>
            <th><b>Aadhar</b></th>
            <th><b>PAN</b></th>
            <th><b>Company</b></th>
            <th><b>GST</b></th>
            <th><b>Status</b></th>
            <th><b>Joining Date</b></th>
            <th><b>Rejected Reason</b></th>
            <th><b>Total Contact</b></th>
            <th><b>Total Leads</b></th>
            <th><b>Total Deals</b></th>
        </tr>

        @endif

        @foreach($user->subAgents as $sub)

        <tr>
            <td></td>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $sub->user_id }}</td>
            <td>{{ $sub->role }}</td>
            <td>{{ $sub->name }}</td>
            <td>{{ $sub->mobile }}</td>
            <td>{{ $sub->email }}</td>
            <td>{{ $sub->dob }}</td>
            <td>{{ $sub->designation->name ?? '' }}</td>
            <td>{{ $sub->state->name ?? '' }}</td>
            <td>{{ $sub->city->name ?? '' }}</td>
            <td>{{ $sub->pin_code }}</td>
            <td>{{ $sub->address }}</td>
            <td>{{ $sub->aadhar_no }}</td>
            <td>{{ $sub->pan_no }}</td>
            <td>{{ $sub->company_name }}</td>
            <td>{{ $sub->gst_no }}</td>
            <td>{{ $sub->status }}</td>
            <td>{{ $sub->approved_date }}</td>
            <td>{{ $sub->rejected_reason }}</td>
            <td>{{ $sub->total_contacts }}</td>
            <td>{{ $sub->total_leads }}</td>
            <td>{{ $sub->total_deals }}</td>
        </tr>

        <!-- SUB AGENT CONTACTS -->
        @if($sub->contacts->count() > 0)
        <tr>
            <th colspan="2"></th>
            <th colspan="20"><b>&nbsp;&nbsp;&nbsp;│ ├── Contacts ({{ $sub->contacts->count() }})</b></th>
        </tr>

        <tr>
            <th colspan="2"></th>
            <th style="text-align: right;"><b>SN.</b></th>
            <th><b>Name</b></th>
            <th><b>Mobile</b></th>
            <th><b>Project</b></th>
            <th><b>Remark</b></th>
            <th><b>Date</b></th>
            <th><b>Time</b></th>
            <th><b>Status</b></th>
            <td colspan="11"></td>
        </tr>
        @endif

        @foreach($sub->contacts as $contact)

        <tr>
            <td colspan="2"></td>
            <td>{{ $loop->iteration }}</td>
            <td>&nbsp;&nbsp;&nbsp;{{ $contact->name }}</td>
            <td>{{ $contact->mobile }}</td>
            <td>{{ $contact->project->name ?? '' }}</td>
            <td>{{ $contact->remark }}</td>
            <td>{{ $contact->date }}</td>
            <td>{{ $contact->time }}</td>
            <td>{{ $contact->status }}</td>
            <td colspan="11"></td>
        </tr>

        @endforeach

        <tr><td colspan="22"></td></tr>

        @endforeach

        <tr><td colspan="22"></td></tr>

        @endforeach

    </tbody>
</table>
