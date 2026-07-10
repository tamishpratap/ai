<?php

namespace App\Http\Controllers;

use App\Models\IbIncome;
use App\Models\LevelIncome;
use App\Models\MemberDetail;
use App\Models\SetRate;
use App\Models\StakingDetail;
use App\Models\StakingIncome;

class TempController extends Controller
{

    public function dailyIncomeDis()
    {
        $maxDays = 200;
        $stakings = StakingDetail::where('status', 'Active')->get();
        foreach ($stakings as $value) {
            if ($value->installments >= $maxDays) {
                $value->status = 'Deactive';
                $value->save();
                continue;
            }

            $memberid = $value->memberid;
            $invest_amount = $value->invest_amount;
            $memUpdate = MemberDetail::where('memberid', $memberid)->first();

            if (!$memUpdate || $memUpdate->status != 'Active') {
                continue;
            }

            $rate = SetRate::find(1)->roi_rate;
            $installment = $value->installments + 1;
            $amount = $invest_amount * $rate / 100;

            if ($amount > 0) {
                $qry = new StakingIncome();
                $qry->date = date('Y-m-d');
                $qry->memberid = $memberid;
                $qry->total_investment = $invest_amount;
                $qry->rate = $rate;
                $qry->amount = $amount;
                $qry->installment = $installment;
                $qry->status = 'Paid';
                $qry->save();

                $value->installments += 1;
                if ($installment == $maxDays) {
                    $value->status = 'Deactive';
                }
                $value->type = 'Staking Income';
                $value->save();

                $wallet = $memUpdate->wallet;
                $memUpdate->wallet += $amount;
                $memUpdate->save();

                walletTransfer($memberid, $amount, 'debit', $wallet, 'ROI Income', 'ROI Income amount has been transfered into Main Wallet.');
            } else {
                $value->status = 'Deactive';
                $value->save();
            }
        }
    }

    public function levelIncomeDis()
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