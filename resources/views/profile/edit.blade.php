<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-body p-4">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-body p-4">
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-body p-4">
                        <div class="max-w-xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
