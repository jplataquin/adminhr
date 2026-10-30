<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('MCP Service Accounts & Tokens') }}
            </h2>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <a href="{{ route('admin.tokens.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Create Service Account
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('plainTextToken'))
                <div class="card border-success mb-4 shadow-sm">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-shield-lock-fill me-1"></i> New Service Account Token Generated: {{ session('tokenName') }}
                    </div>
                    <div class="card-body">
                        <p class="card-text text-danger fw-bold">
                            Make sure to copy your new bearer token now. You won't be able to see it again!
                        </p>
                        <div class="input-group">
                            <input type="text" id="plainTextToken" class="form-control font-monospace" value="{{ session('plainTextToken') }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('plainTextToken').value); alert('Token copied to clipboard!');">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Active MCP Service Accounts</h5>
                    <small class="text-white-50">Tokens are authenticated as independent machine service accounts</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Service Account Name</th>
                                    <th>Description</th>
                                    <th>Created By</th>
                                    <th>Last Used</th>
                                    <th>Created At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tokens as $token)
                                    @php
                                        $serviceAccount = $token->tokenable;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-robot text-primary fs-5"></i>
                                                <div>
                                                    <span class="fw-bold d-block">{{ $serviceAccount ? $serviceAccount->name : $token->name }}</span>
                                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Service Account</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">
                                                {{ $serviceAccount && $serviceAccount->description ? $serviceAccount->description : 'No description provided' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($serviceAccount && $serviceAccount->creator)
                                                {{ $serviceAccount->creator->name }}
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $serviceAccount->creator->email }}</small>
                                            @else
                                                <span class="text-muted">System / Admin</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                                        </td>
                                        <td>
                                            {{ $token->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke this service account token? The client integration will immediately lose access.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Revoke
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-shield-x fs-3 d-block mb-2 text-muted"></i>
                                            No MCP service accounts have been created yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($tokens->hasPages())
                    <div class="card-footer bg-body border-0 py-3">
                        {{ $tokens->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
