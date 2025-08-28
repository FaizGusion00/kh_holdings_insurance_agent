<?php

namespace App\Jobs;

use App\Services\CommissionCalculationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessMonthlyCommissionJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 600; // 10 minutes
    public $tries = 1; // Only try once for monthly processing

    protected int $month;
    protected int $year;

    /**
     * Create a new job instance.
     */
    public function __construct(int $month = null, int $year = null)
    {
        $this->month = $month ?: Carbon::now()->month;
        $this->year = $year ?: Carbon::now()->year;
    }

    /**
     * Execute the job.
     */
    public function handle(CommissionCalculationService $commissionService): void
    {
        Log::info("Processing monthly commission job", [
            'month' => $this->month,
            'year' => $this->year
        ]);

        try {
            $result = $commissionService->processMonthlyCommissions($this->month, $this->year);

            if ($result['success']) {
                Log::info("Monthly commission processing completed successfully", [
                    'month' => $this->month,
                    'year' => $this->year,
                    'processed_count' => $result['processed_count'],
                    'total_amount' => $result['total_amount'] ?? 0
                ]);

                // TODO: Send notification to administrators about successful processing
                
            } else {
                Log::error("Monthly commission processing failed", [
                    'month' => $this->month,
                    'year' => $this->year,
                    'message' => $result['message']
                ]);
                
                $this->fail(new \Exception($result['message']));
            }

        } catch (\Exception $e) {
            Log::error("Monthly commission job failed", [
                'month' => $this->month,
                'year' => $this->year,
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
        Log::error("Monthly commission job failed permanently", [
            'month' => $this->month,
            'year' => $this->year,
            'error' => $exception->getMessage()
        ]);

        // TODO: Send urgent notification to administrators about failed monthly processing
    }
}
