<?php

namespace App\Console\Commands;

use App\Models\WithdrawalRequest;
use Illuminate\Console\Command;

class ApproveWithdrawal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:approve-withdrawal';

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
