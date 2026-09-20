<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.5; color: #1a1a1a; }
    h1 { font-size: 16px; }
    h2 { font-size: 13px; margin-top: 18px; }
    p { margin: 6px 0; }
</style>
</head>
<body>
<h1>Residential Tenancy Agreement</h1>
<p><strong>Landlord:</strong> Hartwell Property Management, acting as agent for the registered owner.</p>
<p><strong>Tenant(s):</strong> {{ $tenantName }}</p>
<p><strong>Property:</strong> {{ $propertyAddress }}</p>
<p><strong>Tenancy term:</strong> {{ $leaseStart }} to {{ $leaseEnd }}</p>
<p><strong>Rent:</strong> £{{ $monthlyRent }} per calendar month, payable on the {{ $paymentDueDay }} of each month by standing order.</p>
<p><strong>Deposit:</strong> £{{ $depositAmount }}, held in a government-approved tenancy deposit scheme.</p>

<h2>Section 1 — Rent and Rent Review</h2>
<p>1.1 Rent is due monthly in advance. Late payment beyond 14 days of the due date may incur a written reminder and, if unresolved, formal notice.</p>
<p>1.2 The Landlord may review the rent annually on the anniversary of the tenancy start date, giving no less than 30 days' written notice of any increase.</p>

<h2>Section 2 — Use of the Property</h2>
<p>2.1 The property shall be used as a private residential dwelling only and not for business purposes without prior written consent.</p>
<p>2.2 The Tenant shall not sublet the whole or part of the property without the Landlord's written consent.</p>

<h2>Section 3 — Deposit</h2>
<p>3.1 The deposit is held as security against damage, unpaid rent, or breach of this agreement, and will be returned within 10 days of the end of the tenancy less any lawful deductions.</p>

<h2>Section 4 — Repairs and Maintenance</h2>
<p>4.1 The Landlord is responsible for keeping in repair the structure and exterior of the property, including the roof, external walls, windows, and drains.</p>
<p>4.2 The Landlord is responsible for the installation and repair of the boiler, central heating system, gas appliances, electrical wiring, water and sanitary installations (basins, sinks, baths, toilets), unless damage is caused by the Tenant's misuse or negligence.</p>
<p>4.3 The Tenant is responsible for minor day-to-day upkeep such as replacing light bulbs, smoke alarm batteries, and reporting any disrepair to the Landlord promptly in writing.</p>
<p>4.4 Where a pest infestation is caused by the Tenant's housekeeping or storage of food waste, the cost of pest control treatment is the Tenant's responsibility; otherwise it falls to the Landlord.</p>
<p>4.5 The Tenant must report any maintenance issue as soon as reasonably practicable. Emergency issues (e.g. no heating in winter, burst pipes, electrical faults presenting a safety risk) should be reported immediately.</p>

<h2>Section 5 — Alterations</h2>
<p>5.1 The Tenant shall not make any structural alterations or additions to the property without the Landlord's prior written consent.</p>

<h2>Section 6 — Pets</h2>
<p>6.1 {{ $petsClause }}</p>

<h2>Section 7 — Access for Inspection and Repairs</h2>
<p>7.1 The Landlord, or persons authorised by the Landlord (including contractors), may enter the property to inspect its condition or carry out repairs, having given the Tenant at least 24 hours' written notice, except in an emergency.</p>

<h2>Section 8 — Ending the Tenancy</h2>
<p>8.1 The Tenant must give the Landlord no less than {{ $tenantNoticeMonths }} calendar months' written notice to end the tenancy.</p>
<p>8.2 The Landlord must give the Tenant no less than 2 calendar months' written notice to end the tenancy, in accordance with applicable housing legislation.</p>
<p>8.3 At the end of the tenancy the Tenant shall return the property in the same condition as at the start, allowing for reasonable wear and tear, and remove all personal belongings.</p>

<h2>Section 9 — Disputes</h2>
<p>9.1 Any dispute arising from this agreement that cannot be resolved between the parties may be referred to the relevant housing tribunal or an approved alternative dispute resolution scheme.</p>

<h2>Section 10 — Notes Specific to This Tenancy</h2>
<p>{{ $extraNotes }}</p>

</body>
</html>
