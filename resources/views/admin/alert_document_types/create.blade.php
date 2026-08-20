<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Add Alert Document Type') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}">Master Data</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.alert-document-types.index') }}">Alert Document Types</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add New</li>
                </ol>
            </nav>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <!-- Validation Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.alert-document-types.store') }}">
                                @csrf

                                <div class="mb-4">
                                    <label for="name" class="form-label small font-weight-bold">Document Type Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Driver's License, Vehicle Registration, Contract" autofocus>
                                    <div class="form-text small text-muted">Enter a unique descriptive name for this document/item type.</div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.alert-document-types.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                    <button type="submit" class="btn btn-primary btn-sm">Create Type</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
