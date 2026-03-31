<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use App\Models\User;
use App\Models\Project;
use App\ProjectStatusEnum;
use App\UserTypeEnum;

new #[Layout('layouts.app')] class extends Component {
    public Project $project;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('required|exists:users,id')]
    public ?string $customer_id = null;

    #[Validate('required|string')]
    public string $status = 'active';

    #[Validate('required|url|max:255')]
    public string $domain = '';

    #[Validate('nullable|string')]
    public string $clarity_api_key = '';

    public function mount(Project $project): void
    {
        abort_unless(auth()->user()->isAdmin() || $project->owner_id === auth()->id(), 403);

        $this->project = $project;
        $this->name = $project->name;
        $this->description = $project->description ?? '';
        $this->customer_id = (string) $project->customer_id;
        $this->status = $project->status->value;
        $this->domain = $project->domain;
        $this->clarity_api_key = $project->clarity_api_key ?? '';
    }

    public function updatedDomain(): void
    {
        $trimmed = trim($this->domain);

        if ($trimmed !== '' && !preg_match('#^https?://#i', $trimmed)) {
            $this->domain = 'https://' . $trimmed;
        }
    }

    public function updateProject(): void
    {
        $this->updatedDomain();
        $this->validate();

        $this->project->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'domain' => $this->domain,
            'clarity_api_key' => $this->clarity_api_key ?: null,
        ]);

        $this->redirect(route('projects'), navigate: true);
    }

    public function with(): array
    {
        return [
            'customers' => User::where('type', \App\UserTypeEnum::CUSTOMER)->get(),
            'statuses' => ProjectStatusEnum::cases(),
        ];
    }
};
?>

<div>
    <div class="flex items-center justify-center p-6">
        <form wire:submit="updateProject">
            <x-ui.fieldset label="Edit Project" class="w-150">
                <x-ui.field required>
                    <x-ui.label>Project Name</x-ui.label>
                    <x-ui.input wire:model.blur="name" placeholder="Project name..." :invalid="$errors->has('name')" />
                    <x-ui.error name="name" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Description</x-ui.label>
                    <x-ui.input wire:model.blur="description" placeholder="Project description..." />
                    <x-ui.error name="description" />
                </x-ui.field>

                <x-ui.field required>
                    <x-ui.label>Customer</x-ui.label>
                    <x-ui.select wire:model="customer_id" placeholder="Choose a customer..." searchable>
                        @foreach ($customers as $customer)
                            <x-ui.select.option :value="$customer->id">{{ $customer->name }}</x-ui.select.option>
                        @endforeach
                    </x-ui.select>

                </x-ui.field>

                <x-ui.field required>
                    <x-ui.label>Status</x-ui.label>
                    <x-ui.radio.group wire:model.blur="status" variant="segmented" direction="horizontal">
                        @foreach ($statuses as $statusOption)
                            <x-ui.radio.item :value="strtolower($statusOption->name)" :label="$statusOption->label()"
                                :color="$statusOption->hex()" />
                        @endforeach
                    </x-ui.radio.group>
                    <x-ui.error name="status" />
                </x-ui.field>

                <x-ui.field required>
                    <x-ui.label>Domain</x-ui.label>
                    <x-ui.input wire:model.blur="domain" placeholder="example.com" :invalid="$errors->has('domain')" />
                    <x-ui.error name="domain" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Clarity API Key</x-ui.label>
                    <x-ui.input wire:model.blur="clarity_api_key" placeholder="Paste Clarity API token..." :invalid="$errors->has('clarity_api_key')" />
                    <x-ui.error name="clarity_api_key" />
                </x-ui.field>

                <x-ui.separator class="my-4" hidden horizontal />

                <x-ui.field class="mt-4">
                    <x-ui.button type="submit" variant="primary" color="blue" icon="floppy-disk">Save
                        Changes</x-ui.button>
                </x-ui.field>
            </x-ui.fieldset>
        </form>
    </div>
</div>