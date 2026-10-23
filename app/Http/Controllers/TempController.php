<?php

namespace App\Http\Controllers;

use App\Models\RewardIncome;
use App\Models\LevelIncome;
use App\Models\MemberDetail;
use App\Models\SetRate;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Models\WithdrawalRequest;

class TempController extends Controller
{
    public function dailyIncomeDis()
    {
        $stakings = StakingDetail::where('status', 'Active')->get();
        foreach ($stakings as $value) {
            $memberid = $value->memberid;
            $invest_amount = $value->invest_amount;
            $memUpdate = MemberDetail::where('memberid', $memberid)->first();
            if (!$memUpdate || $memUpdate->status != 'Active') {
                continue;
            }
            $rate = $value->rate;
            $dailyrate = $rate / 30;
            $amount = $invest_amount * $dailyrate / 100;
            $capping = stakingCapping($memberid, $value->id);
            $achieved = Income3xachieved($memberid);
            $balance = $capping - $achieved;

            if ($balance <= 0) {

                $value->status = 'Deactive';
                $value->save();

                continue;
            }
            if ($amount > $balance) {
                $amount = $balance;
            }
            $installment = $value->installments + 1;
            if ($amount > 0) {

                $qry = new StakingIncome();
                $qry->date = date('Y-m-d');
                $qry->memberid = $memberid;
                $qry->total_investment = $invest_amount;
                $qry->rate = $dailyrate;
                $qry->amount = $amount;
                $qry->installment = $installment;
                $qry->status = 'Paid';
                $qry->save();

                $value->installments += 1;
                $value->type = 'Staking Income';
                $value->save();

                $wallet = $memUpdate->wallet;
                $memUpdate->wallet += $amount;
                $memUpdate->save();

                walletTransfer(
                    $memberid,
                    $amount,
                    'debit',
                    $wallet,
                    'ROI Income',
                    'ROI Income amount has been transfered into Main Wallet.'
                );
                if ($amount >= $balance) {

                    $value->status = 'Deactive';
                    $value->save();
                }
            }
        }
    }
    
    public function levelIncomeDis()
    {
        $incomes = StakingIncome::where([['status', 'Paid'], ['level_status', 0]])->whereDate('created_at', date('Y-m-d'))->get();
        foreach ($incomes as $value) {
            $memberid = $value['memberid'];
            $name = getName($memberid);
            $share = $value['amount'];
            $sponsorid = getSponsorid($memberid);
            $limit = 21;
            for ($i = 1; $i < $limit; $i++) {
                $level = $i;
                $rate = levelRate($level);
                $stak_amount = $share * $rate / 100;

                if ($sponsorid != 'Root') {
                    $var = MemberDetail::where('memberid', $sponsorid)->first();
                    $status = $var->status;
                    $downline = $var->downline;
                    
                    $capping = capping($sponsorid);
                    $achieved = Income3xachieved($sponsorid);
                    $balance = $capping - $achieved;
                    if ($balance <= $stak_amount) {
                        $amount = $balance;
                    } else {
                        $amount = $stak_amount;
                    }

                    if ($amount > 0 && $status == 'Active') {

                        $insert = new LevelIncome();
                        $insert->memberid = $sponsorid;
                        $insert->level = $level;
                        $insert->rate = $rate;
                        $insert->level_id = $memberid;
                        $insert->amount = $amount;
                        $insert->downline = $downline;
                        $insert->staking_income = $share;
                        $insert->name = $name;
                        $insert->type = 'Staking';

                        if ($level == 1 && $downline >= 1 || $level == 2 && $downline >= 2 || $level == 3 && $downline >= 3 || $level == 4 && $downline >= 4 || $level == 5 && $downline >= 5 || $level == 6 && $downline >= 6 || $level == 7 && $downline >= 7 || $level == 8 && $downline >= 8 || $level == 9 && $downline >= 9 || $level == 10 && $downline >= 10 || $level == 11 && $downline >= 11 || $level == 12 && $downline >= 12 || $level == 13 && $downline >= 13 || $level == 14 && $downline >= 14 || $level == 15 && $downline >= 15 || $level == 16 && $downline >= 16 || $level == 17 && $downline >= 17 || $level == 17 && $downline >= 18 || $level == 19 && $downline >= 19 || $level == 20 && $downline >= 20) {

                            $insert->status = 'Paid';
                            $insert->save();
                            
                            $wallet = $var->wallet;
                            $var->wallet += $amount;
                            $var->save();

                            walletTransfer($sponsorid, $amount, 'debit', $wallet, 'Level Income', '' . $i . ' Level Income Amount Added into wallet.');
                        } else {
                            $insert->status = 'Flushed';
                            $insert->save();
                        }
                    }

                    $sponsorid = $var['sponsorid'];
                }
            }
            $update = StakingIncome::where('id', $value->id)->update(['level_status' => 1]);
        }
    }
    
    public function rewardGen()
    {
        // First rank and team reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 0],
            ['team_biz', '>=', 10000]
        ])->get();

        foreach ($members as $value) {

            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 5000 && $weaker_leg >= 5000) {
                
                $reward_amount = 125;
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Silver';
                    $value->save();
    
    
                    $inc = new RewardIncome();
                    $inc->memberid = $memberid;
                    $inc->rank = 'Silver';
                    $inc->amount = $amount;
                    $inc->reward_status = 1;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }

        // Second rank and team reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 1],
            ['team_biz', '>=', 40000]
        ])->get();

        foreach ($members as $value) {

            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 20000 && $weaker_leg >= 20000) {

                $reward_amount = 375;
                                
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Gold';
                    $value->save();
    
                    $inc = new RewardIncome();
                    $inc->memberid = $value->memberid;
                    $inc->rank = 'Gold';
                    $inc->amount = $amount;
                    $inc->reward_status = 2;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }

        // Third rank and team reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 2],
            ['team_biz', '>=', 130000]
        ])->get();

        foreach ($members as $value) {
            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 65000 && $weaker_leg >= 65000) {

                $reward_amount = 1125;
                                
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Platinum';
                    $value->save();
    
                    $inc = new RewardIncome();
                    $inc->memberid = $value->memberid;
                    $inc->rank = 'Platinum';
                    $inc->amount = $amount;
                    $inc->reward_status = 3;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }

        // Forth rank and team reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 3],
            ['team_biz', '>=', 430000]
        ])->get();

        foreach ($members as $value) {
            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 215000 && $weaker_leg >= 215000) {

                $reward_amount = 3750;
                                
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Ruby';
                    $value->save();
    
                    $inc = new RewardIncome();
                    $inc->memberid = $value->memberid;
                    $inc->rank = 'Ruby';
                    $inc->amount = $amount;
                    $inc->reward_status = 4;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }

        // Fifth rank and team reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 4],
            ['team_biz', '>=', 1030000]
        ])->get();

        foreach ($members as $value) {
            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 515000 && $weaker_leg >= 515000) {

                $reward_amount = 7500;
                                
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Emerald';
                    $value->save();
    
                    $inc = new RewardIncome();
                    $inc->memberid = $value->memberid;
                    $inc->rank = 'Emerald';
                    $inc->amount = $amount;
                    $inc->reward_status = 5;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }

        // sixth rank and team reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 5],
            ['team_biz', '>=', 2830000]
        ])->get();

        foreach ($members as $value) {
            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 1415000 && $weaker_leg >= 1415000) {

                $reward_amount = 22500;
                                
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Sapphire';
                    $value->save();
    
                    $inc = new RewardIncome();
                    $inc->memberid = $value->memberid;
                    $inc->rank = 'Sapphire';
                    $inc->amount = $amount;
                    $inc->reward_status = 6;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }

        // seventh rank and direct reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 6],
            ['team_biz', '>=', 8230000]
        ])->get();

        foreach ($members as $value) {
            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 4115000 && $weaker_leg >= 4115000) {

                $reward_amount = 67500;
                                
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Blue Diamond';
                    $value->save();
    
                    $inc = new RewardIncome();
                    $inc->memberid = $value->memberid;
                    $inc->rank = 'Blue Diamond';
                    $inc->amount = $amount;
                    $inc->reward_status = 7;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }

        // eight rank and reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 7],
            ['team_biz', '>=', 26230000]
        ])->get();

        foreach ($members as $value) {
            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 13115000 && $weaker_leg >= 13115000) {

                $reward_amount = 225000;
                                
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Crown Diamond';
                    $value->save();
    
                    $inc = new RewardIncome();
                    $inc->memberid = $value->memberid;
                    $inc->rank = 'Crown Diamond';
                    $inc->amount = $amount;
                    $inc->reward_status = 8;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }

        // nine rank and team reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 8],
            ['team_biz', '>=', 62230000]
        ])->get();

        foreach ($members as $value) {
            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 31115000 && $weaker_leg >= 31115000) {

                $reward_amount = 450000;
                                
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Regal Diamond';
                    $value->save();
    
                    $inc = new RewardIncome();
                    $inc->memberid = $value->memberid;
                    $inc->rank = 'Regal Diamond';
                    $inc->amount = $amount;
                    $inc->reward_status = 9;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }

        // ten rank and team reward creation
        $members = MemberDetail::where([
            ['status', 'Active'],
            ['rank_status', 9],
            ['team_biz', '>=', 134230000]
        ])->get();

        foreach ($members as $value) {
            $memberid = $value->memberid;
            $directs = MemberDetail::where('sponsorid', $memberid)->orderBy('team_biz', 'desc')->get();
            // First two largest team_biz values
            $power_leg = (int) $directs->pluck('team_biz')->first();


            // Third team is the sum of all remaining directs except the top two
            $remaining = $directs->slice(1);
            $weaker_leg = (int) $remaining->sum('team_biz');

            if ($power_leg >= 67115000 && $weaker_leg >= 67115000) {

                $reward_amount = 900000;
                                
                $capping = capping($memberid);
                $achieved = Income3xachieved($memberid);
                $balance = $capping - $achieved;
                if ($balance <= $reward_amount) {
                    $amount = $balance;
                } else {
                    $amount = $reward_amount;
                }

                if ($amount > 0) {
                    $value->rank_status += 1;
                    $value->rank = 'Imperial Crown Diamond';
                    $value->save();
    
                    $inc = new RewardIncome();
                    $inc->memberid = $value->memberid;
                    $inc->rank = 'Imperial Crown Diamond';
                    $inc->amount = $amount;
                    $inc->reward_status = 10;
                    $inc->team_biz = $value->team_biz;
                    $inc->status = 'Paid';
                    $inc->type = 'Reward';
                    $inc->save();
    
                    $wallet = $value->wallet;
                    $value->wallet += $amount;
                    $value->save();
    
                    walletTransfer($memberid, $amount, 'credit', $wallet, 'Reward Income', 'Reward Income has been transfered into wallet.');
                }
            }
        }
    }
    
    public function ApproveWithdrawal()
    {
        $requests = WithdrawalRequest::whereIn('status', ['CREATING', 'PROCESSING'])->get();
        foreach ($requests as $value) {
            //echo count($requests);
            $wid = $value->batch_withdrawal_id;
            $memberid = $value['memberid'];
            $amount = $value['gross_amount'];
            $service = $value['service_charge'];
            $jwt = getJWttoken();
            $curl = curl_init();


            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.nowpayments.io/v1/payout/' . $wid . '/verify',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "verification_code": "123456"
                    }',
                CURLOPT_HTTPHEADER => array(
                    'x-api-key: ' . env('nowpayments_api_key'),
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $jwt . ''
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            //echo $response;
            if ($response == 'OK') {
                $var = WithdrawalRequest::find($value->id);
                $var->status = 'Approved';
                $var->payment_date = date('Y-m-d H:i:s');
                $var->save();
            } else {
                $var = WithdrawalRequest::find($value->id);
                $var->attempt += 1;
                $var->save();
            }
        }
    }
}
