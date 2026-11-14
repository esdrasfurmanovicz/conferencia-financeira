<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">
            Meu perfil
        </h1>
    </x-slot>

    <div class="">
        <div class="space-y-6">
            <div class="panel p-6 sm:p-9" data-tour="profile-info">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="panel p-6 sm:p-9" data-tour="profile-password">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="panel p-6 sm:p-9" data-tour="profile-delete">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
