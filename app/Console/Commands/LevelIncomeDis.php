<?php

namespace App\Console\Commands;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Models\MemberDetail;
use App\Models\LevelIncome;

use Illuminate\Console\Command;

class LevelIncomeDis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:level-income-dis';

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
}
