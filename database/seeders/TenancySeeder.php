<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;
use App\Services\Lease\LeaseIngestionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenancySeeder extends Seeder
{
    protected const DEFAULT_PETS_CLAUSE = 'Pets are not permitted at the property without the Landlord\'s prior written consent.';

    /**
     * @param  array<int, Property>  $properties  indexed 0..7 matching PropertySeeder order
     * @return array<int, Tenancy>
     */
    public function run(array $properties, User $creator): array
    {
        $today = now();

        $defs = [
            // Active, up to date, long-running (12 months of history)
            [
                'property' => $properties[0], 'status' => 'active',
                'tenant_name' => 'Chloe Bennett', 'tenant_email' => 'chloe.bennett@example.test', 'tenant_phone' => '07700 900111',
                'start' => $today->copy()->subMonths(14), 'end' => $today->copy()->addMonths(10),
                'rent' => 950, 'due_day' => 1, 'deposit' => 1100,
                'pets' => self::DEFAULT_PETS_CLAUSE, 'notice_months' => 1,
                'notes' => 'Off-street parking bay included. Bin collection Tuesdays.',
            ],
            // Active, missing this month's rent -> Rent Overdue signal #1
            [
                'property' => $properties[1], 'status' => 'active',
                'tenant_name' => 'James Okafor', 'tenant_email' => 'james.okafor@example.test', 'tenant_phone' => '07700 900112',
                'start' => $today->copy()->subMonths(13), 'end' => $today->copy()->addMonths(11),
                'rent' => 900, 'due_day' => 1, 'deposit' => 1050,
                'pets' => self::DEFAULT_PETS_CLAUSE, 'notice_months' => 1,
                'notes' => 'Communal bike storage available in basement.',
            ],
            // Active but lease_end already in the past -> Expired Tenancy, No Action signal
            [
                'property' => $properties[2], 'status' => 'active',
                'tenant_name' => 'Meera Kapoor', 'tenant_email' => 'meera.kapoor@example.test', 'tenant_phone' => '07700 900113',
                'start' => $today->copy()->subMonths(20), 'end' => $today->copy()->subDays(10),
                'rent' => 1150, 'due_day' => 5, 'deposit' => 1300,
                'pets' => self::DEFAULT_PETS_CLAUSE, 'notice_months' => 1,
                'notes' => 'Lease has technically lapsed — renewal paperwork outstanding.',
            ],
            // Active, missing this month's rent -> Rent Overdue signal #2
            [
                'property' => $properties[3], 'status' => 'active',
                'tenant_name' => "Liam O'Sullivan", 'tenant_email' => 'liam.osullivan@example.test', 'tenant_phone' => '07700 900114',
                'start' => $today->copy()->subMonths(5), 'end' => $today->copy()->addMonths(19),
                'rent' => 875, 'due_day' => 3, 'deposit' => 1000,
                'pets' => self::DEFAULT_PETS_CLAUSE, 'notice_months' => 1,
                'notes' => 'Second floor, no lift — note for contractors carrying equipment.',
            ],
            // Active, dispute history (late-recorded payments)
            [
                'property' => $properties[4], 'status' => 'active',
                'tenant_name' => 'Ayesha Rahman', 'tenant_email' => 'ayesha.rahman@example.test', 'tenant_phone' => '07700 900115',
                'start' => $today->copy()->subMonths(8), 'end' => $today->copy()->addMonths(16),
                'rent' => 1200, 'due_day' => 28, 'deposit' => 1400,
                'pets' => 'One well-behaved cat is permitted subject to a signed pet addendum; no dogs without separate consent.',
                'notice_months' => 1,
                'notes' => 'History of rent being paid a few days late; two written reminders sent previously.',
            ],
            // Active, lease ends in 30 days -> Lease Expiry 30 Days signal
            [
                'property' => $properties[5], 'status' => 'active',
                'tenant_name' => 'Tom & Ella Whitfield', 'tenant_email' => 'whitfield.family@example.test', 'tenant_phone' => '07700 900116',
                'start' => $today->copy()->subMonths(20), 'end' => $today->copy()->addDays(30),
                'rent' => 1450, 'due_day' => 1, 'deposit' => 1600,
                'pets' => 'Dogs are permitted in this garden-access house subject to Landlord consent and an additional pet deposit of £200.',
                'notice_months' => 1,
                'notes' => 'Garden maintenance is the tenant\'s responsibility per addendum.',
            ],
            // Notice given
            [
                'property' => $properties[6], 'status' => 'notice_given',
                'tenant_name' => 'Grace Lin', 'tenant_email' => 'grace.lin@example.test', 'tenant_phone' => '07700 900117',
                'start' => $today->copy()->subMonths(18), 'end' => $today->copy()->addDays(75),
                'rent' => 1500, 'due_day' => 1, 'deposit' => 1700,
                'pets' => self::DEFAULT_PETS_CLAUSE, 'notice_months' => 1,
                'notes' => 'Tenant relocating for work; notice given in writing per Section 8.1.',
                'end_reason' => 'Tenant relocating for work — 1 month written notice given, effective end of tenancy term.',
            ],
            // Active commercial unit, lease ends in 60 days -> Lease Expiry 60 Days signal
            [
                'property' => $properties[7], 'status' => 'active',
                'tenant_name' => 'Kingswood Design Studio Ltd', 'tenant_email' => 'accounts@kingswooddesign.example.test', 'tenant_phone' => '0117 900 0118',
                'start' => $today->copy()->subMonths(10), 'end' => $today->copy()->addDays(60),
                'rent' => 2200, 'due_day' => 1, 'deposit' => 4400,
                'pets' => 'Not applicable — commercial unit.', 'notice_months' => 2,
                'notes' => 'Shared loading bay access with adjacent unit; deliveries before 9am preferred.',
            ],
            // Ended historical tenancy on property 0, before Chloe Bennett moved in
            [
                'property' => $properties[0], 'status' => 'ended',
                'tenant_name' => 'Old Tenant A', 'tenant_email' => 'archive.tenant.a@example.test', 'tenant_phone' => null,
                'start' => $today->copy()->subYears(3), 'end' => $today->copy()->subMonths(15),
                'rent' => 875, 'due_day' => 1, 'deposit' => 1000,
                'pets' => self::DEFAULT_PETS_CLAUSE, 'notice_months' => 1,
                'notes' => 'Historical record only.', 'end_reason' => 'Tenant relocated at end of fixed term.',
                'skip_lease' => true,
            ],
            // Ended historical tenancy on the commercial unit, before Kingswood Design Studio
            [
                'property' => $properties[7], 'status' => 'ended',
                'tenant_name' => 'Previous Tenant Co', 'tenant_email' => 'archive.tenant.b@example.test', 'tenant_phone' => null,
                'start' => $today->copy()->subYears(3), 'end' => $today->copy()->subMonths(12),
                'rent' => 2000, 'due_day' => 1, 'deposit' => 4000,
                'pets' => 'Not applicable — commercial unit.', 'notice_months' => 2,
                'notes' => 'Historical record only.', 'end_reason' => 'Business closed the unit at end of lease term.',
                'skip_lease' => true,
            ],
        ];

        $tenancies = [];
        $ingestion = app(LeaseIngestionService::class);

        foreach ($defs as $def) {
            $tenancy = Tenancy::create([
                'property_id' => $def['property']->id,
                'tenant_name' => $def['tenant_name'],
                'tenant_email' => $def['tenant_email'],
                'tenant_phone' => $def['tenant_phone'] ?? null,
                'lease_start' => $def['start']->toDateString(),
                'lease_end' => $def['end']->toDateString(),
                'monthly_rent' => $def['rent'],
                'payment_due_day' => $def['due_day'],
                'deposit_amount' => $def['deposit'],
                'status' => $def['status'],
                'end_reason' => $def['end_reason'] ?? null,
                'created_by' => $creator->id,
            ]);

            if (empty($def['skip_lease'])) {
                $pdfBinary = Pdf::loadView('seed.lease', [
                    'tenantName' => $def['tenant_name'],
                    'propertyAddress' => $def['property']->fullAddress(),
                    'leaseStart' => $tenancy->lease_start->format('d M Y'),
                    'leaseEnd' => $tenancy->lease_end->format('d M Y'),
                    'monthlyRent' => number_format($def['rent'], 2),
                    'depositAmount' => number_format($def['deposit'], 2),
                    'paymentDueDay' => $def['due_day'],
                    'petsClause' => $def['pets'],
                    'tenantNoticeMonths' => $def['notice_months'],
                    'extraNotes' => $def['notes'],
                ])->output();

                $storedName = 'tenancy-'.$tenancy->id.'-'.Str::random(8).'.pdf';
                Storage::disk('leases')->put($storedName, $pdfBinary);
                $tenancy->forceFill([
                    'lease_file_path' => $storedName,
                    'lease_original_filename' => Str::slug($def['tenant_name']).'-lease-agreement.pdf',
                    'lease_uploaded_at' => $tenancy->created_at,
                ])->save();

                $ingestion->ingest($tenancy, Storage::disk('leases')->path($storedName));
            }

            $tenancies[] = $tenancy;
        }

        return $tenancies;
    }
}
