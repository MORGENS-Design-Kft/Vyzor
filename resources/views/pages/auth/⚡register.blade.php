<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use App\Models\User;
use App\UserTypeEnum;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component {
    public string $type = 'web';

    // User fields
    #[Validate('required_if:type,web|string|max:255')]
    public string $name = '';

    // Shared fields
    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('required|string|min:8')]
    public string $password_confirmation = '';

    // Customer fields
    #[Validate('required_if:type,customer|string|max:255')]
    public string $company_name = '';

    #[Validate('nullable|string|max:255')]
    public string $phone = '';

    public function register(): void
    {
        $this->validate();

        DB::transaction(function () {
            $user = User::create([
                'type' => UserTypeEnum::from($this->type),
                'name' => $this->type === UserTypeEnum::WEB->value ? $this->name : $this->company_name,
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if ($this->type === UserTypeEnum::CUSTOMER->value) {
                $user->customerProfile()->create([
                    'company_name' => $this->company_name,
                    'phone' => $this->phone ?: null,
                ]);
            } else {
                $user->userProfile()->create();
            }

        });

        $this->redirect(route('users'), navigate: true);
    }
};
?>

<div>
    <div class="h-screen flex items-center justify-center">
        <form wire:submit="register">
            <x-ui.fieldset label="Register" class="w-100">
                <x-ui.field>
                    <x-ui.radio.group wire:model.live="type" direction="horizontal">
                        <x-ui.radio.item value="web" label="User" />
                        <x-ui.radio.item value="customer" label="Customer" />
                    </x-ui.radio.group>
                </x-ui.field>

                @if ($type === 'web')
                    <x-ui.field required>
                        <x-ui.label>Name</x-ui.label>
                        <x-ui.input placeholder="Name..." wire:model="name" />
                        <x-ui.error name="name" />
                    </x-ui.field>
                @else
                    <x-ui.field required>
                        <x-ui.label>Company Name</x-ui.label>
                        <x-ui.input placeholder="Company name..." wire:model="company_name" />
                        <x-ui.error name="company_name" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label>Phone</x-ui.label>
                        <x-ui.input placeholder="Phone..." type="tel" wire:model="phone" />
                        <x-ui.error name="phone" />
                    </x-ui.field>
                @endif

                <x-ui.field required>
                    <x-ui.label>Email</x-ui.label>
                    <x-ui.input placeholder="E-mail..." type="email" wire:model="email" />
                    <x-ui.error name="email" />
                </x-ui.field>

                <x-ui.field required>
                    <x-ui.label>Password</x-ui.label>
                    <x-ui.input placeholder="Password..." type="password" wire:model="password" />
                    <x-ui.error name="password" />
                </x-ui.field>

                <x-ui.field required>
                    <x-ui.label>Confirm Password</x-ui.label>
                    <x-ui.input placeholder="Confirm password..." type="password" wire:model="password_confirmation" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.button type="submit">Register</x-ui.button>
                </x-ui.field>
            </x-ui.fieldset>
        </form>
    </div>
</div>
