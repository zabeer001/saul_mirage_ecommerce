<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use Carbon\Carbon;

class UpdateCustomerStatus extends Command
{
    protected $signature = 'customers:update-status';
    protected $description = 'Automatically update customer status based on recent orders';

    public function handle()
    {
        $threeMonthsAgo = now()->subMonths(3);
        $updatedCount = 0;

        $customers = Customer::orderBy('created_at', 'desc')->get(); // no limit here, or you can limit

        foreach ($customers as $customer) {
            $hasRecentOrder = $customer->orders()
                ->where('created_at', '>=', $threeMonthsAgo)
                ->exists();

            $newStatus = $hasRecentOrder ? 'active' : 'inactive';

            if ($customer->status !== $newStatus) {
                $customer->status = $newStatus;
                $customer->save();
                $updatedCount++;
            }
        }

        $this->info("Updated $updatedCount customer(s).");
    }
}