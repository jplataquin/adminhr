<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Generate MCP Access Token') }}
            </h2>
            <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Tokens
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-body py-3">
                            <h5 class="card-title mb-0">Token Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.tokens.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">Token Name / Client Identifier</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., Claude Desktop, Cursor, Internal Script" required autofocus>
                                    <div class="form-text">A descriptive name to identify which MCP client or agent will use this token.</div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="user_id" class="form-label">Assign to User</label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                        <option value="" disabled selected>Select a user to bind the token to...</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id', auth()->id()) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }}) {{ $user->is_admin ? '[Admin]' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">The token will inherit this user's permissions when accessing organizational data.</div>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-key"></i> Generate Token
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
