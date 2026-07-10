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
        if (Setting::get('trading_reward_status', 'enabled') !== 'enabled') {
            $this->info('Trading rewards are globally paused - nothing credited.');
            return;
        }

        $maxDays = 200;
        $rewardDate = date('Y-m-d');
        // trading_reward_percentage is intentionally not a settings-table key - set_rates.roi_rate
        // (already admin-editable via AdminController::rateUpdate) stays the single source of
        // truth for this rate, so it isn't duplicated into two competing config values.
        $rate = (float) (optional(SetRate::find(1))->roi_rate ?? 0);

        if ($rate <= 0) {
            $this->info('Trading reward percentage is 0 - nothing credited.');
            return;
        }

        $stakings = StakingDetail::where('status', 'Active')->get();
        $incomeService = app(IncomeService::class);

        foreach ($stakings as $value) {
            if ($value->installments >= $maxDays) {
                $value->status = 'Deactive';
                $value->save();
                continue;
            }

            // One trading reward per member per calendar day (unique reward-date key).
            if (StakingIncome::where('memberid', $value->memberid)->where('date', $rewardDate)->exists()) {
                continue;
            }

            $memberid = $value->memberid;
            $invest_amount = $value->invest_amount;

            DB::transaction(function () use ($value, $memberid, $invest_amount, $rate, $rewardDate, $maxDays, $incomeService) {
                $memUpdate = MemberDetail::where('memberid', $memberid)->lockForUpdate()->first();

                if (!$memUpdate || $memUpdate->status !== 'Active') {
                    return;
                }

                $calculatedAmount = round($invest_amount * $rate / 100, 8);
                $cap = $incomeService->applyIncomeCap($memUpdate, $calculatedAmount, 'trading');
                $installment = $value->installments + 1;

                $qry = new StakingIncome();
                $qry->date = $rewardDate;
                $qry->memberid = $memberid;
                $qry->total_investment = $invest_amount;
                $qry->rate = $rate;
                $qry->amount = $cap['allowed_amount'];
                $qry->installment = $installment;
                $qry->status = 'Paid';
                $qry->save();

                $value->installments += 1;
                if ($installment >= $maxDays) {
                    $value->status = 'Deactive';
                }
                $value->type = 'Staking Income';
                $value->save();

                if ($cap['allowed_amount'] > 0) {
                    $wallet = $memUpdate->wallet;
                    $memUpdate->wallet += $cap['allowed_amount'];
                    $memUpdate->save();

                    walletTransfer(
                        $memberid,
                        $cap['allowed_amount'],
                        'debit',
                        $wallet,
                        'ROI Income',
                        sprintf(
                            'Trading reward for %s: $%s @ %s%% = $%s (credited $%s)',
                            $rewardDate,
                            number_format($invest_amount, 2),
                            rtrim(rtrim(number_format($rate, 2), '0'), '.'),
                            number_format($calculatedAmount, 2),
                            number_format($cap['allowed_amount'], 2)
                        ),
                        'TRADING-' . $memberid . '-' . $rewardDate
                    );
                }
            });
        }
    }
}
