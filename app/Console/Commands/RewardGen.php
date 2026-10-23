<?php

namespace App\Console\Commands;
use App\Models\MemberDetail;
use App\Models\RewardIncome;

use Illuminate\Console\Command;

class RewardGen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reward-gen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
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
}
