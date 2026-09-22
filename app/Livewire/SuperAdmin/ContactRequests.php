<?php

namespace App\Livewire\SuperAdmin;

use App\Models\ContactRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layout')]
class ContactRequests extends Component
{
    use WithPagination;

    public string $filter = 'new';
    public ?int $markingId = null;
    public string $notes = '';

    public function filterBy(string $value): void
    {
        $this->filter = $value;
        $this->resetPage();
    }

    public function startRespond(int $id): void
    {
        $this->markingId = $id;
        $this->notes = '';
    }

    public function saveResponse(): void
    {
        ContactRequest::findOrFail($this->markingId)->update([
            'status' => 'responded',
            'notes'  => $this->notes,
        ]);
        $this->markingId = null;
        $this->notes = '';
    }

    public function cancelRespond(): void
    {
        $this->markingId = null;
        $this->notes = '';
    }

    public function render()
    {
        $query = ContactRequest::query()->orderByDesc('created_at');

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return view('livewire.super-admin.contact-requests', [
            'requests' => $query->paginate(20),
            'newCount' => ContactRequest::where('status', 'new')->count(),
        ]);
    }
}
