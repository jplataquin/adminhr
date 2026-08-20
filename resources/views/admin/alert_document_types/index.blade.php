<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Alert Document Types') }}
            </h2>
            <a href="{{ route('admin.alert-document-types.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Add New Type
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}">Master Data</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Alert Document Types</li>
                </ol>
            </nav>

            <!-- Success Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- List Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4" style="width: 80px;">ID</th>
                                    <th>Document Type Name</th>
                                    <th>Created At</th>
                                    <th class="text-end px-4" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($types as $type)
                                    <tr>
                                        <td class="px-4 text-muted">{{ $type->id }}</td>
                                        <td>
                                            <strong class="text-dark">{{ $type->name }}</strong>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $type->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="text-end px-4">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('admin.alert-document-types.edit', $type->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <form method="POST" action="{{ route('admin.alert-document-types.destroy', $type->id) }}" onsubmit="return confirm('Are you sure you want to delete this document type?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <div class="fs-4 mb-2"><i class="bi bi-info-circle"></i></div>
                                            No alert document types are configured yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($types->hasPages())
                        <div class="card-footer bg-white border-0 py-3">
                            {{ $types->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
