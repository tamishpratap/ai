<?php

namespace App\Console\Commands;

use App\Models\LevelIncome;
use App\Models\MemberDetail;
use App\Models\StakingDetail;
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
        $incomes = StakingDetail::where('status', 'Active')->get();
        foreach ($incomes as $value) {
            $memberid = $value->memberid;
            $invest_amount = $value->invest_amount;
            $referral_installments = $value->referral_installments + 1;
            
            $memUpdate = MemberDetail::where('memberid', $memberid)->first();
            if (!$memUpdate || $memUpdate->status != 'Active') {
                continue;
            }

            $sponsorid = getSponsorid($memberid);
            $limit = 4;
            if($referral_installments <= 50){
                for ($i = 1; $i < $limit; $i++) {
                    $level = $i;
                    
                    $amount = levelamount($level, $invest_amount);
                    if ($amount === null || $amount <= 0) {
                        break;
                    }
    
                    if ($sponsorid != 'Root' && $referral_installments <= 50) {
                        $var = MemberDetail::where('memberid', $sponsorid)->first();
                        if (!$var) {
                            break;
                        }
                        
                        $status = $var->status;
                        $name = $var->name;
                        $downline = $var->downline;
    
                        if ($status == 'Active') {
                            if ($amount > 0) {
                                $insert = new LevelIncome();
                                $insert->memberid = $sponsorid;
                                $insert->level = $level;
                                $insert->level_id = $memberid;
                                $insert->amount = $amount;
                                $insert->package = $invest_amount;
                                $insert->name = $name;
                                $insert->downline = $downline;
                                // $insert->type = 'Level Income';
                                $insert->status = 'Paid';
                                $insert->save();
                                
                                $wallet = $var->wallet;
                                $var->wallet += $amount;
                                $var->save();
                
                                walletTransfer($sponsorid, $amount, 'debit', $wallet, 'Direct Income', 'Direct Income amount has been transfered into Main Wallet.');
                            }
                        }
    
                        $sponsorid = $var->sponsorid;
                    } else {
                        break;
                    }
                }
                
                $value->referral_installments = $referral_installments;
                $value->save();
            }
        }
    }
}
