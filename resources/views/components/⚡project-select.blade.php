<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Project;

new class extends Component {

    public $selectedProject = '';

    public function mount()
    {
        $this->selectedProject = (string) session('current_project_id', '');
    }

    #[Computed]
    public function projects()
    {
        return Project::with('customer')->where('owner_id', auth()->id())->get()->groupBy(fn($project) => $project->customer?->name ?? 'No Customer');
    }

    public function updatedSelectedProject($value)
    {
        if (blank($value)) {
            // Restore previous selection — prevent deselection
            $this->selectedProject = (string) session('current_project_id', '');
            return;
        }

        session(['current_project_id' => (int) $value]);
        $this->dispatch('current-project-changed', projectId: (int) $value);
    }

    #[On('current-project-changed')]
    public function refreshSelectedProject($projectId)
    {
        $this->selectedProject = (string) $projectId;
    }

};
?>

<div class="w-72">
    <x-ui.select placeholder="Select a project..." wire:model.live="selectedProject">
        @foreach ($this->projects as $customerName => $customerProjects)
            <x-ui.select.group :label="$customerName">
                @foreach ($customerProjects as $project)
                    <x-ui.select.option :value="$project->id" :label="$project->name" allowCustomSlots>
                        <div class="flex flex-col py-1">
                            <span class="text-neutral-950 dark:text-neutral-50">{{ $project->name }}</span>
                            <span class="text-xs text-neutral-400 dark:text-neutral-500">{{ $customerName }}</span>
                        </div>
                    </x-ui.select.option>
                @endforeach
            </x-ui.select.group>
        @endforeach
    </x-ui.select>
</div>
