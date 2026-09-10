<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Positions') }}
            </h2>
            <a href="{{ route('admin.positions.create') }}" class="btn btn-warning btn-sm text-dark font-weight-bold">
                <i class="bi bi-plus-circle me-1"></i> Add New Position
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
                    <li class="breadcrumb-item active" aria-current="page">Positions</li>
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
                                    <th>Division</th>
                                    <th>Department</th>
                                    <th>Position Code</th>
                                    <th>Position Title</th>
                                    <th>Created At</th>
                                    <th class="text-end px-4" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($positions as $position)
                                    <tr>
                                        <td class="px-4 text-white-50">{{ $position->id }}</td>
                                        <td>
                                            <span class="text-white">{{ $position->department->division->name }}</span>
                                        </td>
                                        <td>
                                            <span class="text-white">{{ $position->department->name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark font-monospace">{{ $position->code }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-white">{{ $position->name }}</strong>
                                        </td>
                                        <td class="small text-white-50">
                                            {{ $position->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="text-end px-4">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('admin.positions.edit', $position->id) }}" class="btn btn-sm btn-outline-warning py-1 px-2">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <form method="POST" action="{{ route('admin.positions.destroy', $position->id) }}" onsubmit="return confirm('Are you sure you want to delete this position?');" class="d-inline">
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
                                        <td colspan="7" class="text-center py-5 text-white-50">
                                            <div class="fs-4 mb-2"><i class="bi bi-info-circle"></i></div>
                                            No positions are configured yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($positions->hasPages())
                        <div class="card-footer bg-transparent border-0 py-3">
                            {{ $positions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
