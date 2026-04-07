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

            <x-ui.field>
                <x-ui.button type="submit" variant="primary">Create User</x-ui.button>
            </x-ui.field>
        </div>
    </form>
</div>
