<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Generate MCP Service Account Token') }}
            </h2>
            <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Service Accounts
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-body py-3">
                            <h5 class="card-title mb-0">Service Account Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-info border-0 mb-4">
                                <div class="d-flex gap-2">
                                    <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                                    <div>
                                        <strong>Independent Service Account:</strong> This token will act as a standalone service identity for MCP integrations (e.g. Cursor, Claude Desktop, automated agents) and will <strong>not</strong> be tied to any human user credentials.
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('admin.tokens.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">Service Account / Client Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., Claude Desktop, Cursor Agent, Internal Analytics" required autofocus>
                                    <div class="form-text">A unique identifier for this MCP integration.</div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label">Description / Purpose (Optional)</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="e.g., Read-only access for organizational ledger and employee lookup in local IDE">{{ old('description') }}</textarea>
                                    <div class="form-text">Optional notes describing the purpose, hosting environment, or machine for this service account.</div>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-shield-lock me-1"></i> Generate Service Account Token
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
