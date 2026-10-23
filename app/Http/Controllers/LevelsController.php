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

        $genealogyData = $this->buildLevelGenealogy($result['d_data']);
        $result['tree'] = $genealogyData['tree'];
        $result['levels'] = $genealogyData['levels'];
        $result['totalMembersCount'] = $genealogyData['totalMembersCount'];
        $result['maxLevel'] = $genealogyData['maxLevel'];
        $result['is_root_view'] = true;

        return view('member.team.geneology')->with($result);
    }

    public function ViewGeneology($did)
    {
        $loggedInMemberId = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $loggedInMemberId)->first();
        $target = is_numeric($did) ? MemberDetail::find($did) : MemberDetail::where('memberid', $did)->first();

        if (!$target) {
            session()->flash('searchMsg', 'Member not found');
            session()->flash('failedMsg', 'Member not found');
            return redirect()->to('member/team/geneology');
        }

        $result['d_data'] = $target;
        $memberid = $result['d_data']['memberid'];
        $result['directs'] = MemberDetail::where('sponsorid', $memberid)->get();

        $genealogyData = $this->buildLevelGenealogy($result['d_data']);
        $result['tree'] = $genealogyData['tree'];
        $result['levels'] = $genealogyData['levels'];
        $result['totalMembersCount'] = $genealogyData['totalMembersCount'];
        $result['maxLevel'] = $genealogyData['maxLevel'];
        $result['is_root_view'] = ($memberid === $loggedInMemberId);

        return view('member.team.view-geneology')->with($result);
    }

    public function searchGenelogy(Request $request)
    {
        $request->validate([
            'searchid' => 'required'
        ]);
        $loggedInMemberId = session('MEMBER_ID');
        $result['data'] = MemberDetail::where('memberid', $loggedInMemberId)->first();
        $searchid = trim($request->post('searchid'));
        $result['d_data'] = MemberDetail::where('memberid', $searchid)->first();

        if (!$result['d_data']) {
            session()->flash('searchMsg', 'This memberid is not registered with us');
            session()->flash('failedMsg', 'This memberid is not registered with us');
            return redirect()->to('member/team/geneology');
        }

        $memberid = $result['d_data']['memberid'];
        $result['directs'] = MemberDetail::where('sponsorid', $memberid)->get();

        $genealogyData = $this->buildLevelGenealogy($result['d_data']);
        $result['tree'] = $genealogyData['tree'];
        $result['levels'] = $genealogyData['levels'];
        $result['totalMembersCount'] = $genealogyData['totalMembersCount'];
        $result['maxLevel'] = $genealogyData['maxLevel'];
        $result['is_root_view'] = ($memberid === $loggedInMemberId);

        return view('member.team.geneology')->with($result);
    }

    /**
     * Build dynamic level-wise genealogy data structure starting from a root member.
     * Uses batch queries per level for performance, with cycle/loop protection.
     */
    private function buildLevelGenealogy($rootMember)
    {
        if (!$rootMember) {
            return [
                'tree' => null,
                'levels' => [],
                'totalMembersCount' => 0,
                'maxLevel' => 0,
            ];
        }

        $rootId = $rootMember->memberid;
        $visited = [$rootId => true];
        $levels = [];
        $membersBySponsor = [];
        $currentSponsorIds = [$rootId];
        $levelIndex = 1;
        $maxDepthLimit = 20;
        $totalMembersCount = 0;

        while (!empty($currentSponsorIds) && $levelIndex <= $maxDepthLimit) {
            $children = MemberDetail::whereIn('sponsorid', $currentSponsorIds)->get();
            if ($children->isEmpty()) {
                break;
            }

            $nextSponsorIds = [];
            $currentLevelMembers = [];

            foreach ($children as $child) {
                if (isset($visited[$child->memberid])) {
                    continue;
                }
                $visited[$child->memberid] = true;

                $child->level = $levelIndex;
                $currentLevelMembers[] = $child;
                $membersBySponsor[$child->sponsorid][] = $child;
                $nextSponsorIds[] = $child->memberid;
            }

            if (empty($currentLevelMembers)) {
                break;
            }

            $levels[$levelIndex] = $currentLevelMembers;
            $totalMembersCount += count($currentLevelMembers);
            $currentSponsorIds = $nextSponsorIds;
            $levelIndex++;
        }

        $treeVisited = [$rootId => true];
        $rootMember->level = 0;
        $this->attachTreeChildren($rootMember, $membersBySponsor, $treeVisited, 1);

        return [
            'tree' => $rootMember,
            'levels' => $levels,
            'totalMembersCount' => $totalMembersCount,
            'maxLevel' => count($levels),
        ];
    }

    private function attachTreeChildren(&$parent, &$membersBySponsor, &$treeVisited, $level)
    {
        $pid = $parent->memberid;
        $children = $membersBySponsor[$pid] ?? [];
        $attachedChildren = [];

        foreach ($children as $child) {
            if (isset($treeVisited[$child->memberid])) {
                continue;
            }
            $treeVisited[$child->memberid] = true;
            $child->level = $level;
            $this->attachTreeChildren($child, $membersBySponsor, $treeVisited, $level + 1);
            $attachedChildren[] = $child;
        }

        $parent->children = $attachedChildren;
        $parent->children_count = count($attachedChildren);
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
