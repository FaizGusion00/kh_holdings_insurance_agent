<?php

namespace App\Jobs;

use App\Models\PaymentTransaction;
use App\Services\CommissionCalculationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CalculateCommissionJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected PaymentTransaction $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct(PaymentTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(CommissionCalculationService $commissionService): void
    {
        Log::info("Processing commission calculation job", [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount
        ]);

        try {
            $result = $commissionService->calculateCommissionForPayment($this->transaction);

            if ($result['success']) {
                Log::info("Commission calculation completed successfully", [
                    'transaction_id' => $this->transaction->id,
                    'commissions_created' => $result['commissions_created'],
                    'total_amount' => $result['total_commission_amount']
                ]);
            } else {
                Log::error("Commission calculation failed", [
                    'transaction_id' => $this->transaction->id,
                    'message' => $result['message']
                ]);
                
                $this->fail(new \Exception($result['message']));
            }

        } catch (\Exception $e) {
            Log::error("Commission calculation job failed", [
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->fail($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Commission calculation job failed permanently", [
            'transaction_id' => $this->transaction->id,
            'error' => $exception->getMessage()
        ]);

        // TODO: Send notification to administrators about the failed commission calculation
    }
}
