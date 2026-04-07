<div>
    <form wire:submit="save">
        <div class="space-y-4">
            <x-ui.field required>
                <x-ui.label>Company Name</x-ui.label>
                <x-ui.input placeholder="Company name..." wire:model="company_name" :invalid="$errors->has('company_name')" />
                <x-ui.error name="company_name" />
            </x-ui.field>

            <x-ui.field required>
                <x-ui.label>Email</x-ui.label>
                <x-ui.input placeholder="E-mail..." type="email" wire:model="email" :invalid="$errors->has('email')" />
                <x-ui.error name="email" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Phone</x-ui.label>
                <x-ui.input placeholder="Phone..." type="tel" wire:model="phone" />
                <x-ui.error name="phone" />
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
                <x-ui.button type="submit" variant="primary">Create Customer</x-ui.button>
            </x-ui.field>
        </div>
    </form>
</div>
