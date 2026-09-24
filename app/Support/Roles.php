<?php

namespace App\Support;

class Roles
{
    public const OWNER = 'property_owner';

    public const MANAGER = 'property_manager';

    public const MAINTENANCE_COORDINATOR = 'maintenance_coordinator';

    public const CONTRACTOR = 'contractor';

    public const TENANT = 'tenant';

    public const SUPER_ADMIN = 'Super Admin';

    public const ALL = [
        self::SUPER_ADMIN,
        self::OWNER,
        self::MANAGER,
        self::MAINTENANCE_COORDINATOR,
        self::CONTRACTOR,
        self::TENANT,
    ];

    public const LABELS = [
        self::SUPER_ADMIN => 'Super Admin',
        self::OWNER => 'Property Owner',
        self::MANAGER => 'Property Manager',
        self::MAINTENANCE_COORDINATOR => 'Maintenance Coordinator',
        self::CONTRACTOR => 'Contractor',
        self::TENANT => 'Tenant',
    ];

    /** Roles that can see financial data and lease documents. */
    public const BACK_OFFICE = [
        self::SUPER_ADMIN,
        self::OWNER,
        self::MANAGER,
    ];

    /** Roles that operate across all properties (not scoped to one owner/tenancy). */
    public const STAFF = [
        self::SUPER_ADMIN,
        self::OWNER,
        self::MANAGER,
        self::MAINTENANCE_COORDINATOR,
    ];
}
