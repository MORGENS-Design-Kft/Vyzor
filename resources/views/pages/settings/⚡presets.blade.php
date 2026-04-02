<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use App\Models\LLMContextPreset;

new #[Layout('layouts.app')] class extends Component {

    // Form fields for create/edit
    public ?int $editingId = null;
    public string $formName = '';
    public string $formDescription = '';
    public string $formLabelColor = '#3b82f6';
    public string $formIcon = 'file-text';
    public string $formContext = '';
    public int $formSortOrder = 0;
    public bool $formIsActive = true;

    public bool $showForm = false;

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editPreset(int $id): void
    {
        $preset = LLMContextPreset::findOrFail($id);
        $this->editingId = $preset->id;
        $this->formName = $preset->name;
        $this->formDescription = $preset->description ?? '';
        $this->formLabelColor = $preset->label_color;
        $this->formIcon = $preset->icon;
        $this->formContext = $preset->context;
        $this->formSortOrder = $preset->sort_order;
        $this->formIsActive = $preset->is_active;
        $this->showForm = true;
    }

    public function savePreset(): void
    {
        $this->validate([
            'formName' => 'required|string|max:255',
            'formDescription' => 'nullable|string|max:500',
            'formLabelColor' => 'required|string|max:7',
            'formIcon' => 'required|string|max:100',
            'formContext' => 'required|string',
            'formSortOrder' => 'integer|min:0',
        ]);

        $data = [
            'name' => $this->formName,
            'description' => $this->formDescription ?: null,
            'label_color' => $this->formLabelColor,
            'icon' => $this->formIcon,
            'context' => $this->formContext,
            'sort_order' => $this->formSortOrder,
            'is_active' => $this->formIsActive,
        ];

        if ($this->editingId) {
            LLMContextPreset::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Preset updated successfully.');
        } else {
            LLMContextPreset::create($data);
            session()->flash('success', 'Preset created successfully.');
        }

        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $preset = LLMContextPreset::findOrFail($id);
        $preset->update(['is_active' => !$preset->is_active]);
    }

    public function deletePreset(int $id): void
    {
        LLMContextPreset::findOrFail($id)->delete();
        session()->flash('success', 'Preset deleted.');
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formName = '';
        $this->formDescription = '';
        $this->formLabelColor = '#3b82f6';
        $this->formIcon = 'file-text';
        $this->formContext = '';
        $this->formSortOrder = 0;
        $this->formIsActive = true;
        $this->showForm = false;
        $this->resetValidation();
    }

    public function with(): array
    {
        return [
            'presets' => LLMContextPreset::ordered()->get(),
        ];
    }
};
?>

<div class="p-6 space-y-6">
    <div>
        <x-ui.heading level="h1" size="xl">Settings</x-ui.heading>
        <x-ui.description class="mt-1">Manage application settings and LLM context presets.</x-ui.description>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 p-4">
            <div class="flex items-center gap-2">
                <x-ui.icon name="check-circle" class="size-5 text-green-600 dark:text-green-400" />
                <span class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Preset Manager Section --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <x-ui.heading level="h2" size="lg">LLM Context Presets</x-ui.heading>
                <x-ui.description class="mt-1">Configure presets that define what the AI analyses in reports.</x-ui.description>
            </div>
            @if (!$showForm)
                <x-ui.button color="blue" icon="plus" wire:click="openCreateForm">
                    New Preset
                </x-ui.button>
            @endif
        </div>

        {{-- Create / Edit Form --}}
        @if ($showForm)
            <x-ui.card size="full" class="mb-6">
                <div class="flex items-center gap-2 mb-5">
                    <x-ui.icon name="{{ $editingId ? 'pencil-simple' : 'plus-circle' }}" class="size-5 text-blue-500" />
                    <x-ui.heading level="h3" size="md">{{ $editingId ? 'Edit Preset' : 'New Preset' }}</x-ui.heading>
                </div>

                <form wire:submit="savePreset" class="space-y-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        {{-- Left column --}}
                        <div class="space-y-5">
                            <x-ui.field required>
                                <x-ui.label>Name</x-ui.label>
                                <x-ui.input wire:model="formName" placeholder="e.g. Traffic Overview" :invalid="$errors->has('formName')" />
                                <x-ui.error name="formName" />
                            </x-ui.field>

                            <x-ui.field>
                                <x-ui.label>Description</x-ui.label>
                                <x-ui.input wire:model="formDescription" placeholder="Short description of what this preset analyses..." :invalid="$errors->has('formDescription')" />
                                <x-ui.error name="formDescription" />
                            </x-ui.field>

                            <div class="grid grid-cols-3 gap-4">
                                <x-ui.field required>
                                    <x-ui.label>Color</x-ui.label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="color"
                                            wire:model.live="formLabelColor"
                                            class="size-10 rounded-field border border-black/10 dark:border-white/15 cursor-pointer bg-transparent p-0.5"
                                        />
                                        <x-ui.input wire:model.live="formLabelColor" class="font-mono" />
                                    </div>
                                </x-ui.field>

                                <x-ui.field required>
                                    <x-ui.label>Icon</x-ui.label>
                                    <x-ui.input wire:model="formIcon" placeholder="e.g. chart-line-up" :invalid="$errors->has('formIcon')" />
                                    <x-ui.error name="formIcon" />
                                </x-ui.field>

                                <x-ui.field>
                                    <x-ui.label>Sort Order</x-ui.label>
                                    <x-ui.input type="number" wire:model="formSortOrder" min="0" />
                                </x-ui.field>
                            </div>

                            {{-- Preview card --}}
                            <div>
                                <x-ui.label class="mb-2">Preview</x-ui.label>
                                <div
                                    class="flex items-center gap-3 p-3 rounded-box border border-black/10 dark:border-white/10"
                                    style="border-color: {{ $formLabelColor }}; background-color: {{ $formLabelColor }}10; box-shadow: 0 0 0 1px {{ $formLabelColor }}80"
                                >
                                    <div
                                        class="shrink-0 flex items-center justify-center size-9 rounded-field"
                                        style="background-color: {{ $formLabelColor }}15; color: {{ $formLabelColor }}"
                                    >
                                        <x-ui.icon :name="$formIcon" class="size-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $formName ?: 'Preset Name' }}</span>
                                        <span class="block text-xs text-neutral-400 mt-0.5">{{ $formDescription ?: 'Description goes here...' }}</span>
                                    </div>
                                </div>
                            </div>

                            <x-ui.field>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <x-ui.checkbox wire:model="formIsActive" />
                                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Active</span>
                                    <span class="text-xs text-neutral-400">(inactive presets won't appear when creating reports)</span>
                                </label>
                            </x-ui.field>
                        </div>

                        {{-- Right column: Context --}}
                        <div>
                            <x-ui.field required class="h-full flex! flex-col!">
                                <x-ui.label>Context / Prompt</x-ui.label>
                                <x-ui.description class="mb-2">The instructions sent to the AI when this preset is selected. Supports markdown.</x-ui.description>
                                <textarea
                                    wire:model="formContext"
                                    placeholder="# Report Title&#10;&#10;Describe what the AI should analyse...&#10;&#10;## Focus Areas&#10;- Area 1&#10;- Area 2&#10;&#10;## Expected Output&#10;Describe the expected format..."
                                    @class([
                                        'w-full flex-1 min-h-64 rounded-box px-3 py-2 text-sm font-mono text-neutral-800 dark:text-neutral-300 placeholder-neutral-400 bg-white dark:bg-neutral-900 focus:ring-2 focus:outline-none shadow-xs resize-y',
                                        'border border-black/10 dark:border-white/15 focus:ring-neutral-900/15 dark:focus:ring-neutral-100/15 focus:border-black/15 dark:focus:border-white/20' => !$errors->has('formContext'),
                                        'border-2 border-red-600/30 focus:border-red-600/30 focus:ring-red-600/20 dark:border-red-400/30 dark:focus:border-red-400/30 dark:focus:ring-red-400/20' => $errors->has('formContext'),
                                    ])
                                ></textarea>
                                <x-ui.error name="formContext" />
                            </x-ui.field>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <x-ui.button type="button" variant="outline" color="neutral" wire:click="cancelForm">
                            Cancel
                        </x-ui.button>
                        <x-ui.button type="submit" color="blue" icon="floppy-disk">
                            {{ $editingId ? 'Update Preset' : 'Create Preset' }}
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @endif

        {{-- Presets List --}}
        @if ($presets->isEmpty())
            <x-ui.card>
                <x-ui.empty>
                    <x-ui.empty.contents>
                        <x-ui.icon name="file-text" class="size-10 text-neutral-300 dark:text-neutral-600" />
                        <x-ui.text>No presets yet. Create your first one to get started.</x-ui.text>
                        <x-ui.button variant="outline" color="neutral" wire:click="openCreateForm" class="mt-2" icon="plus">
                            Create Preset
                        </x-ui.button>
                    </x-ui.empty.contents>
                </x-ui.empty>
            </x-ui.card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($presets as $preset)
                    <x-ui.card size="full" @class(['opacity-50' => !$preset->is_active])>
                        <div class="flex items-start gap-3">
                            <div
                                class="shrink-0 flex items-center justify-center size-10 rounded-field mt-0.5"
                                style="background-color: {{ $preset->label_color }}15; color: {{ $preset->label_color }}"
                            >
                                <x-ui.icon :name="$preset->icon" class="size-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ $preset->name }}</span>
                                    @if (!$preset->is_active)
                                        <x-ui.badge size="sm" color="neutral">Inactive</x-ui.badge>
                                    @endif
                                </div>
                                @if ($preset->description)
                                    <x-ui.description class="mt-0.5 line-clamp-2">{{ $preset->description }}</x-ui.description>
                                @endif
                                <div class="flex items-center gap-1 mt-2 text-xs text-neutral-400">
                                    <x-ui.icon name="sort-ascending" class="size-3" />
                                    <span>{{ $preset->sort_order }}</span>
                                    <span class="mx-1">-</span>
                                    <span class="font-mono">{{ $preset->slug }}</span>
                                </div>
                            </div>
                        </div>

                        <x-ui.separator class="my-3" />

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <x-ui.button size="xs" variant="outline" color="neutral" icon="pencil-simple" wire:click="editPreset({{ $preset->id }})">
                                    Edit
                                </x-ui.button>
                                <x-ui.button
                                    size="xs"
                                    variant="outline"
                                    :color="$preset->is_active ? 'neutral' : 'blue'"
                                    :icon="$preset->is_active ? 'eye-slash' : 'eye'"
                                    wire:click="toggleActive({{ $preset->id }})"
                                >
                                    {{ $preset->is_active ? 'Disable' : 'Enable' }}
                                </x-ui.button>
                            </div>
                            <button
                                wire:click="deletePreset({{ $preset->id }})"
                                wire:confirm="Are you sure you want to delete '{{ $preset->name }}'? This cannot be undone."
                                class="text-neutral-400 hover:text-red-500 transition-colors p-1"
                            >
                                <x-ui.icon name="trash" class="size-4" />
                            </button>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </div>
</div>
