<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Edit Division') }}
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.divisions.index') }}">Divisions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
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

                            <form method="POST" action="{{ route('admin.divisions.update', $division->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label for="code" class="form-label small font-weight-bold">Division Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control font-monospace text-uppercase" id="code" name="code" value="{{ old('code', $division->code) }}" required placeholder="e.g. ADMNHR, CONOPS, ACCFIN" autofocus>
                                    <div class="form-text small text-muted">A short unique code identifier for the division.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="name" class="form-label small font-weight-bold">Division Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $division->name) }}" required placeholder="e.g. Administrative & Human Resource, Construction">
                                    <div class="form-text small text-muted">Enter a descriptive name for this division.</div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.divisions.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                    <button type="submit" class="btn btn-success btn-sm">Update Division</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
