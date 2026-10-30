<x-app-layout :containerClass="'container-fluid px-lg-4'">
    @isset($header)
        <x-slot name="header">
            {{ $header }}
        </x-slot>
    @endisset

    <div class="row g-3 py-3">
        <!-- Persistent Sidebar -->
        <div class="col-md-4 col-lg-3 col-xl-3">
            <x-ledger-sidebar />
        </div>

        <!-- Main Content Pane -->
        <div class="col-md-8 col-lg-9 col-xl-9">
            {{ $slot }}
        </div>
    </div>
</x-app-layout>
