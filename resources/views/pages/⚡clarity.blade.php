<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Artisan;

new #[Layout('layouts.app')] class extends Component {

    public ?string $error = null;

    public function fetchInfo(): void
    {
        $projectId = session('current_project_id');

        if (!$projectId) {
            $this->error = 'No project selected. Please select a project first.';
            return;
        }

        $exitCode = Artisan::call('app:fetch-clarity', [
            'project' => $projectId,
        ]);

        if ($exitCode !== 0) {
            $this->error = trim(Artisan::output());
            return;
        }

        $this->redirect(route('clarity'), navigate: true);
    }
};
?>

<div class="p-6 space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">Clarity</h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Microsoft Clarity insights for the current project.</p>
    </div>

    @if ($error)
        <x-ui.card>
            <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
        </x-ui.card>
    @endif

    <x-ui.button variant="primary" icon="arrow-clockwise" wire:click="fetchInfo" wire:loading.attr="loading">
        Fetch info
    </x-ui.button>
</div>
