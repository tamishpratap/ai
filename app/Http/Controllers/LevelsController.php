<?php

namespace App\Http\Controllers;

use App\Models\MemberDetail;
use App\Models\UplineMember;
use Illuminate\Http\Request;

class LevelsController extends Controller
{
    public function directMembers()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['directData'] = MemberDetail::where('sponsorid', $result['data']['memberid'])->get();
        return view('member.team.direct-members')->with($result);
    }

    public function geneology()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['d_data'] = $result['data'];
        $result['directs'] = MemberDetail::where('sponsorid', $memberid)->get();
        return view('member.team.geneology')->with($result);
    }

    public function ViewGeneology($did)
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $result['d_data'] = MemberDetail::find($did);
        $memberid = $result['d_data']['memberid'];
        $result['directs'] = MemberDetail::where('sponsorid', $memberid)->get();

        return view('member.team.view-geneology')->with($result);
    }

    public function searchGenelogy(Request $request)
    {
        $request->validate([
            'searchid' => 'required'
        ]);
        $result['data'] =  MemberDetail::where('memberid', session('MEMBER_ID'))->first();
        $memberid = $result['data']['memberid'];
        $searchid = $request->post('searchid');
        $result['d_data'] = MemberDetail::where('memberid', $searchid)->first();
        if (!$result['d_data']) {
            session()->flash('failedMsg', 'This memberid is not registered with us');
            return redirect()->to('member/team/geneology');
        }
        if ($memberid == $searchid) {
            session()->flash('failedMsg', 'You have entered your own memberid');
            return redirect()->to('member/team/geneology');
        }
        $memberid = $result['d_data']['memberid'];
        $result['directs'] = MemberDetail::where('sponsorid', $memberid)->get();
        return view('member.team.geneology')->with($result);
    }

    public function lelMemTable()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        return view('member.team.level-members-table')->with($result);
    }

    public function lelMemDetails($level)
    {
        $uplines = 'upline_' . $level;
        $result['data'] = MemberDetail::find(session('MEMBER_ID'));
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();
        $ids = UplineMember::where($uplines, $memberid)->pluck('memberid');
        $result['users'] = MemberDetail::whereIn('memberid', $ids)->get();
        $result['levelNo'] = $level;
        return view('member.team.level-members-details')->with($result);
    }

    public function totalTeamMembers()
    {
        $memberid = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $memberid)->first();

        $members = MemberDetail::where('sponsorid', $memberid)->pluck('memberid')->toArray();
        $total = count($members);
        $users = $members;
        while (count($members) != 0) {
            $members = MemberDetail::whereIn('sponsorid', $members)->pluck('memberid')->toArray();
            $users = array_merge($users, $members);
            $total += count($members);
        }
        $result['users'] = MemberDetail::whereIn('memberid', $users)->get();
        return view('member.team.total-team-members')->with($result);
    }
}
