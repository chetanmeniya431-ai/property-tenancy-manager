<?php

namespace Database\Seeders;

use App\Models\RentPayment;
use App\Models\Tenancy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RentPaymentSeeder extends Seeder
{
    /**
     * @param  array<int, Tenancy>  $tenancies  indexed exactly as TenancySeeder::run() returned them
     */
    public function run(array $tenancies, User $recorder): void
    {
        [$t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10] = $tenancies;

        // 3 long-running tenancies with a full 12 months of on-time payments.
        $this->addMonthlyHistory($t1, 12, skipCurrentMonth: false, recorder: $recorder);
        $this->addMonthlyHistory($t6, 12, skipCurrentMonth: false, recorder: $recorder);
        $this->addMonthlyHistory($t3, 12, skipCurrentMonth: false, recorder: $recorder); // keeps T3 clean for the Expired Tenancy signal only

        // 2 tenancies with a missed payment this month -> Rent Overdue signal.
        $this->addMonthlyHistory($t2, 12, skipCurrentMonth: true, recorder: $recorder);
        $this->addMonthlyHistory($t4, 5, skipCurrentMonth: true, recorder: $recorder);

        // 1 tenancy with a dispute history: a couple of payments recorded several days late.
        $this->addMonthlyHistory($t5, 8, skipCurrentMonth: false, recorder: $recorder, lateMonths: [2, 5]);

        // Notice-given tenancy: normal history, up to date.
        $this->addMonthlyHistory($t7, 12, skipCurrentMonth: false, recorder: $recorder);

        // Commercial tenancy: up to date.
        $this->addMonthlyHistory($t8, 10, skipCurrentMonth: false, recorder: $recorder);

        // Historical/ended tenancies: a short paid-up trail, no current-month logic applies.
        $this->addMonthlyHistory($t9, 6, skipCurrentMonth: false, recorder: $recorder, endingMonthsAgo: 15);
        $this->addMonthlyHistory($t10, 6, skipCurrentMonth: false, recorder: $recorder, endingMonthsAgo: 12);
    }

    /**
     * @param  int[]  $lateMonths  offsets (months back) that should be recorded a few days after the due date
     */
    protected function addMonthlyHistory(
        Tenancy $tenancy,
        int $months,
        bool $skipCurrentMonth,
        User $recorder,
        array $lateMonths = [],
        int $endingMonthsAgo = 0,
    ): void {
        $start = $skipCurrentMonth ? 1 : 0;

        for ($i = $start; $i < $months + $start; $i++) {
            $monthsBack = $i + $endingMonthsAgo;
            $period = Carbon::now()->subMonthsNoOverflow($monthsBack);
            $dueDay = min($tenancy->payment_due_day, $period->daysInMonth);
            $dueDate = $period->copy()->day($dueDay);

            $lateOffset = in_array($monthsBack, $lateMonths, true) ? 6 : 0;

            RentPayment::create([
                'tenancy_id' => $tenancy->id,
                'amount' => $tenancy->monthly_rent,
                'payment_date' => $dueDate->copy()->addDays($lateOffset)->toDateString(),
                'method' => 'Standing order',
                'reference' => 'RENT-'.$tenancy->id.'-'.$period->format('Ym'),
                'notes' => $lateOffset > 0 ? 'Paid late — see dispute notes on tenancy.' : null,
                'recorded_by' => $recorder->id,
                'created_at' => now(),
            ]);
        }
    }
}
