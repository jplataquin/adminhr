<x-app-layout>
    <div class="container py-4" x-data="bulkUpdateHandler()">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-body d-flex align-items-center justify-content-between py-3">
                <h3 class="h5 mb-0">Bulk Update Employees</h3>
                <div>
                    <a href="{{ route('employees') }}" class="btn btn-outline-secondary btn-sm me-2">Back to List</a>
                    <button class="btn btn-primary btn-sm" @click="downloadTemplate()">
                        <i class="bi bi-download me-1"></i> Download Template
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Initial State: Upload Form -->
                <div x-show="state === 'initial'">
                    <div class="p-5 border-2 border-dashed rounded text-center bg-body-tertiary">
                        <i class="bi bi-file-earmark-excel h1 text-success mb-3 d-block"></i>
                        <h5>Upload Employee Masterlist</h5>
                        <p class="text-secondary small mb-4">
                            Download the template containing current employee records, update details in Excel, then upload the file here.<br>
                            <strong>Note:</strong> Photo updates are excluded from this process. Keep the first row (headers) intact.
                        </p>
                        
                        <div class="col-md-6 mx-auto">
                            <div class="input-group">
                                <input type="file" class="form-control" id="excelFile" accept=".xlsx, .xls" @change="onFileChange($event)">
                                <button class="btn btn-success" type="button" :disabled="!fileSelected || loading" @click="uploadFile()">
                                    <span x-show="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Upload & Preview
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview State -->
                <div x-show="state === 'preview'">
                    <div class="alert d-flex align-items-center mb-4" :class="hasErrors ? 'alert-danger' : 'alert-success'">
                        <i class="bi h4 mb-0 me-3" :class="hasErrors ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'"></i>
                        <div>
                            <span x-text="hasErrors ? 'Validation Errors Detected: Please review the errors below and correct them in your Excel file before uploading again.' : 'Validation Successful! All records are valid and ready to be committed.'"></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="h6 mb-0">Parsed Rows Preview (<span x-text="rows.length"></span> rows)</h4>
                        <div>
                            <button class="btn btn-secondary btn-sm me-2" @click="resetState()" :disabled="loading">
                                <i class="bi bi-arrow-left me-1"></i> Cancel & Upload New
                            </button>
                            <button class="btn btn-success btn-sm" @click="commitUpdates()" :disabled="hasErrors || loading">
                                <span x-show="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                <i class="bi bi-check-lg me-1" x-show="!loading"></i> Commit Updates
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width: 80px;">Row</th>
                                    <th style="width: 100px;">ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Division / Dept</th>
                                    <th>Validation Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in rows" :key="row.row_num">
                                    <tr :class="Object.keys(row.errors).length > 0 ? 'table-danger' : ''">
                                        <td x-text="row.row_num" class="fw-bold"></td>
                                        <td x-text="row.data.id || 'N/A'"></td>
                                        <td>
                                            <span x-text="(row.data.firstname || '') + ' ' + (row.data.lastname || '')"></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary" x-text="row.data.employment_status"></span>
                                        </td>
                                        <td>
                                            <span x-text="row.data.division"></span>
                                            <template x-if="row.data.department">
                                                <span x-text="' / ' + row.data.department" class="text-secondary small"></span>
                                            </template>
                                        </td>
                                        <td>
                                            <template x-if="Object.keys(row.errors).length > 0">
                                                <div class="text-danger small">
                                                    <template x-for="[field, msgs] in Object.entries(row.errors)">
                                                        <div class="mb-1">
                                                            <strong class="text-capitalize" x-text="field.replace(/_/g, ' ')"></strong>: 
                                                            <span x-text="msgs.join(', ')"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="Object.keys(row.errors).length === 0">
                                                <span class="text-success small fw-bold">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Valid
                                                </span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bulkUpdateHandler() {
            return {
                state: 'initial',
                loading: false,
                fileSelected: false,
                file: null,
                rows: [],
                hasErrors: false,

                onFileChange(e) {
                    this.file = e.target.files[0];
                    this.fileSelected = !!this.file;
                },

                downloadTemplate() {
                    window.location.href = '/employees/export/excel';
                },

                resetState() {
                    this.state = 'initial';
                    this.fileSelected = false;
                    this.file = null;
                    this.rows = [];
                    this.hasErrors = false;
                    const fileInput = document.getElementById('excelFile');
                    if (fileInput) fileInput.value = '';
                },

                async uploadFile() {
                    if (!this.file) return;

                    this.loading = true;
                    const formData = new FormData();
                    formData.append('file', this.file);

                    try {
                        const response = await fetch('/employees/bulk-update/preview', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.status === 1) {
                            this.rows = result.rows;
                            this.hasErrors = result.has_errors;
                            this.state = 'preview';
                        } else if (result.status === -2) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Validation Failed',
                                text: result.message || 'File validation failed. Ensure it is a valid Excel file.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'An error occurred while uploading.'
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Could not connect to the server. Please try again.'
                        });
                    } finally {
                        this.loading = false;
                    }
                },

                async commitUpdates() {
                    if (this.hasErrors) return;

                    const confirm = await Swal.fire({
                        title: 'Are you sure?',
                        text: `You are about to update ${this.rows.length} employee records directly in the database. This action cannot be undone.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Commit Changes'
                    });

                    if (!confirm.isConfirmed) return;

                    this.loading = true;
                    const payload = {
                        rows: this.rows.map(r => r.data)
                    };

                    try {
                        const response = await fetch('/employees/bulk-update/commit', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();

                        if (result.status === 1) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: result.message
                            });
                            window.location.href = '/employees';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to commit updates.'
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Could not connect to the server. Please try again.'
                        });
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
