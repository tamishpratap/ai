<?php

namespace App\Console\Commands;

use App\Models\MemberDetail;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use Illuminate\Console\Command;

class DailyIncomeDis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-income-dis';

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
}
