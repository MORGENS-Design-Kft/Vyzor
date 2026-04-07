<div>
    <form wire:submit="save">
        <div class="space-y-4">
            <x-ui.field required>
                <x-ui.label>Name</x-ui.label>
                <x-ui.input placeholder="Name..." wire:model="name" :invalid="$errors->has('name')" />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field required>
                <x-ui.label>Email</x-ui.label>
                <x-ui.input placeholder="E-mail..." type="email" wire:model="email" :invalid="$errors->has('email')" />
                <x-ui.error name="email" />
            </x-ui.field>

            <x-ui.field required>
                <x-ui.label>Password</x-ui.label>
                <x-ui.input placeholder="Password..." type="password" wire:model="password" :invalid="$errors->has('password')" />
                <x-ui.error name="password" />
            </x-ui.field>

            <x-ui.field required>
                <x-ui.label>Confirm Password</x-ui.label>
                <x-ui.input placeholder="Confirm password..." type="password" wire:model="password_confirmation" />
            </x-ui.field>

            <div x-data="{
                suggested: '',
                copied: false,
                generate() {
                    const chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
                    let pwd = '';
                    const array = new Uint32Array(16);
                    crypto.getRandomValues(array);
                    for (let i = 0; i < 16; i++) pwd += chars[array[i] % chars.length];
                    this.suggested = pwd;
                    this.copied = false;
                },
                use() {
                    $wire.set('password', this.suggested);
                    $wire.set('password_confirmation', this.suggested);
                },
                copy() {
                    navigator.clipboard.writeText(this.suggested);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            }" x-init="generate()" class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50 p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Suggested Password</span>
                    <button type="button" @click="generate()" class="text-xs text-blue-500 hover:text-blue-700 dark:hover:text-blue-400 cursor-pointer">
                        Regenerate
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <code x-text="suggested" class="flex-1 text-sm font-mono bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded px-2 py-1.5 select-all"></code>
                    <x-ui.button type="button" size="sm" variant="outline" @click="copy()" x-text="copied ? 'Copied!' : 'Copy'" />
                    <x-ui.button type="button" size="sm" variant="primary" @click="use()">Use</x-ui.button>
                </div>
            </div>

            <x-ui.field>
                <x-ui.button type="submit" variant="primary">Create User</x-ui.button>
            </x-ui.field>
        </div>
    </form>
</div>
