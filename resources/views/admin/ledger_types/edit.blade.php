<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Edit Ledger Type') }}
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

                            <form method="POST" action="{{ route('admin.ledger-types.update', $ledgerType->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label for="text" class="form-label small font-weight-bold">Ledger Type Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="text" name="text" value="{{ old('text', $ledgerType->text) }}" required placeholder="e.g. Travel Reimbursement, Equipment Purchase" @if($ledgerType->isProtected()) readonly @endif autofocus>
                                    <div class="form-text small text-muted">
                                        @if($ledgerType->isProtected())
                                            <span class="text-warning"><i class="bi bi-lock-fill"></i> This default ledger type is protected and its name cannot be altered.</span>
                                        @else
                                            Enter a unique descriptive name for this ledger type.
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="prototype" class="form-label small font-weight-bold">Prototype <span class="text-danger">*</span></label>
                                    <select class="form-select @error('prototype') is-invalid @enderror" id="prototype" name="prototype" required @if($ledgerType->isProtected()) disabled @endif>
                                        <option value="Accounts Receivable" {{ old('prototype', $ledgerType->prototype ?? 'Accounts Receivable') === 'Accounts Receivable' ? 'selected' : '' }}>Accounts Receivable</option>
                                        <option value="Accounts Payable" {{ old('prototype', $ledgerType->prototype) === 'Accounts Payable' ? 'selected' : '' }}>Accounts Payable</option>
                                    </select>
                                    @if($ledgerType->isProtected())
                                        <input type="hidden" name="prototype" value="{{ $ledgerType->prototype ?? 'Accounts Receivable' }}">
                                    @endif
                                    @error('prototype')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small text-muted">
                                        @if($ledgerType->isProtected())
                                            <span class="text-warning"><i class="bi bi-lock-fill"></i> This default ledger type is protected and its prototype cannot be altered.</span>
                                        @else
                                            Select whether this ledger type defines an Accounts Receivable or Accounts Payable prototype.
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.ledger-types.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                    @if(!$ledgerType->isProtected())
                                        <button type="submit" class="btn btn-primary btn-sm">Update Type</button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
