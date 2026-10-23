<?php

namespace App\Console\Commands;

use App\Models\MemberDetail;
use App\Models\SetRate;
use App\Models\Setting;
use App\Models\StakingDetail;
use App\Models\StakingIncome;
use App\Services\IncomeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
}
