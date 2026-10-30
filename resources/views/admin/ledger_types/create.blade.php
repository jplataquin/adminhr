<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Add Ledger Type') }}
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.ledger-types.index') }}">Ledger Types</a></li>
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

                            <form method="POST" action="{{ route('admin.ledger-types.store') }}">
                                @csrf

                                <div class="mb-4">
                                    <label for="text" class="form-label small font-weight-bold">Ledger Type Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="text" name="text" value="{{ old('text') }}" required placeholder="e.g. Travel Reimbursement, Equipment Purchase" autofocus>
                                    <div class="form-text small text-muted">Enter a unique descriptive name for this ledger type.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="prototype" class="form-label small font-weight-bold">Prototype <span class="text-danger">*</span></label>
                                    <select class="form-select @error('prototype') is-invalid @enderror" id="prototype" name="prototype" required>
                                        <option value="Accounts Receivable" {{ old('prototype', 'Accounts Receivable') === 'Accounts Receivable' ? 'selected' : '' }}>Accounts Receivable</option>
                                        <option value="Accounts Payable" {{ old('prototype') === 'Accounts Payable' ? 'selected' : '' }}>Accounts Payable</option>
                                    </select>
                                    @error('prototype')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small text-muted">Select whether this ledger type defines an Accounts Receivable or Accounts Payable prototype.</div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.ledger-types.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
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
