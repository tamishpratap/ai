<?php

use App\Models\MemberDetail;

function getDirectCount($memberid)
{
     $directCount = MemberDetail::where('sponsorid', $memberid)->count();
     return $directCount;
}

function getLevelData($memberid)
{
    $directs = MemberDetail::where('sponsorid', $memberid)->get();
    return $directs;
}

function binary_count_update($memberid, $placementid, $position)
{
    while ($placementid != 'Root') {
        if ($position == 'Left') {
            $query = MemberDetail::where('memberid', $placementid)->first();
            $query->left_count = $query->left_count + 1;
            $query->save();
            $position = $query->position;
            $placementid = $query->placementid;
        } elseif ($position == 'Right') {
            $query = MemberDetail::where('memberid', $placementid)->first();
            $query->right_count = $query->right_count + 1;
            $query->save();
            $position = $query->position;
            $placementid = $query->placementid;
        }
    }
}