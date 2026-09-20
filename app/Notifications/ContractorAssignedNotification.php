<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractorAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(protected MaintenanceRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'property_address' => $this->request->property->fullAddress(),
            'category' => MaintenanceRequest::CATEGORIES[$this->request->category] ?? $this->request->category,
            'urgency' => MaintenanceRequest::URGENCIES[$this->request->urgency] ?? $this->request->urgency,
            'message' => "You've been assigned a {$this->request->urgency} maintenance request at {$this->request->property->fullAddress()}.",
        ];
    }
}
