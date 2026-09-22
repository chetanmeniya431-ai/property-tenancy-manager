<?php

namespace App\Livewire;

use App\Models\ContactRequest;
use Livewire\Attributes\On;
use Livewire\Component;

class DemoContactModal extends Component
{
    public bool $open = false;
    public bool $submitted = false;

    public string $name = '';
    public string $email = '';
    public string $company = '';
    public string $phone = '';
    public string $message = '';

    #[On('show-demo-modal')]
    public function show(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function submit(): void
    {
        $this->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:200',
            'message' => 'required|string|max:2000',
        ]);

        ContactRequest::create([
            'name'    => $this->name,
            'email'   => $this->email,
            'company' => $this->company,
            'phone'   => $this->phone,
            'message' => $this->message,
            'project' => 'property-tenancy-manager',
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.demo-contact-modal');
    }
}
