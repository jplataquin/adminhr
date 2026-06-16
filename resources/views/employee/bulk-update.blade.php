<x-app-layout>
    <style>
        /* Override Bootstrap container width specifically for this page to make it full screen width and edge-to-edge */
        main > .container {
            max-width: 100% !important;
            width: 100% !important;
            padding-left: 0px !important;
            padding-right: 0px !important;
        }

        /* Custom CSS for frozen columns on horizontal and vertical scroll inside the container */
        .table-responsive {
            position: relative;
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 220px); /* Restrict height to enable vertical scrolling within the container */
        }

        /* Ensure all table headers are sticky and have a correct z-index */
        thead th {
            position: sticky;
            top: 0;
            z-index: 30 !important;
            background-color: #212529 !important; /* Keep the dark header background */
            color: #fff !important;
        }

        /* Ensure sticky columns have correct width and left offsets */
        .col-freeze-1 { position: sticky; left: 0px; min-width: 60px; max-width: 60px; width: 60px; }
        .col-freeze-status { position: sticky; left: 60px; min-width: 60px; max-width: 60px; width: 60px; }
        .col-freeze-2 { position: sticky; left: 120px; min-width: 80px; max-width: 80px; width: 80px; }
        .col-freeze-3 { position: sticky; left: 200px; min-width: 140px; max-width: 140px; width: 140px; }
        .col-freeze-4 { position: sticky; left: 340px; min-width: 140px; max-width: 140px; width: 140px; }
        .col-freeze-5 { position: sticky; left: 480px; min-width: 140px; max-width: 140px; width: 140px; }
        .col-freeze-6 { position: sticky; left: 620px; min-width: 80px; max-width: 80px; width: 80px; }

        /* Headings: Sticky top AND sticky left, highest z-index */
        thead th.col-freeze-1, thead th.col-freeze-status, thead th.col-freeze-2, thead th.col-freeze-3,
        thead th.col-freeze-4, thead th.col-freeze-5, thead th.col-freeze-6 {
            top: 0;
            z-index: 40 !important;
            background-color: #212529 !important;
            color: #fff !important;
        }

        thead th.col-freeze-6 {
            border-right: 2px solid #dee2e6 !important;
        }

        /* Body cells: Sticky left, background-color so scrolled rows go behind them, z-index lower than headings */
        tbody td.col-freeze-1, tbody td.col-freeze-status, tbody td.col-freeze-2, tbody td.col-freeze-3,
        tbody td.col-freeze-4, tbody td.col-freeze-5, tbody td.col-freeze-6 {
            z-index: 20 !important;
        }

        tbody td.col-freeze-1 { background-color: var(--bs-card-bg) !important; color: inherit !important; }
        tbody td.col-freeze-status { background-color: var(--bs-card-bg) !important; color: inherit !important; text-align: center; }
        tbody td.col-freeze-2 { background-color: var(--bs-card-bg) !important; color: inherit !important; }
        tbody td.col-freeze-3 { background-color: var(--bs-card-bg) !important; color: inherit !important; }
        tbody td.col-freeze-4 { background-color: var(--bs-card-bg) !important; color: inherit !important; }
        tbody td.col-freeze-5 { background-color: var(--bs-card-bg) !important; color: inherit !important; }
        tbody td.col-freeze-6 { 
            background-color: var(--bs-card-bg) !important; 
            color: inherit !important;
            border-right: 2px solid #6c757d !important;
        }

        /* Highlight frozen cells on row hover */
        tr:hover td.col-freeze-1, tr:hover td.col-freeze-status, tr:hover td.col-freeze-2, tr:hover td.col-freeze-3,
        tr:hover td.col-freeze-4, tr:hover td.col-freeze-5, tr:hover td.col-freeze-6 {
            background-color: var(--bs-tertiary-bg) !important;
            color: inherit !important;
        }
    </style>

    <div class="container-fluid px-0 py-0" x-data="bulkUpdateHandler()">
        <div class="card shadow-sm border-0 rounded-0 mb-0">
            <div class="card-header bg-body d-flex align-items-center justify-content-between py-3 px-4">
                <h3 class="h5 mb-0">Bulk Update Employees (Table View)</h3>
                <div class="d-flex align-items-center">
                    <a href="{{ route('employees') }}" class="btn btn-outline-secondary btn-sm me-2">Back to List</a>
                    <button class="btn btn-success btn-sm" @click="commitUpdates()" :disabled="loading">
                        <span x-show="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        <i class="bi bi-check-lg me-1" x-show="!loading"></i> Save All Changes
                    </button>
                </div>
            </div>

            <div class="card-body px-0 pb-0">
                <!-- Filtering & Summary Info Bar -->
                <div class="row g-3 mb-4 align-items-center justify-content-between px-4">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Quick search by name or ID..." x-model="searchQuery">
                        </div>
                    </div>
                    <div class="col-md-auto text-end">
                        <span class="badge bg-secondary text-wrap" style="font-size: 0.85rem;">
                            Total Records: <span x-text="employees.length"></span>
                        </span>
                        <template x-if="searchQuery">
                            <span class="badge bg-info text-wrap" style="font-size: 0.85rem;">
                                Filtered: <span x-text="filteredEmployees().length"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Table Container with Horizontal and Vertical Scrolling -->
                <div class="table-responsive border rounded">
                    <table class="table table-sm table-bordered table-striped table-hover align-middle mb-0" style="font-size: 0.85rem; width: max-content; min-width: 100%;">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th class="text-center col-freeze-1">ID</th>
                                <th class="text-center col-freeze-status">Status</th>
                                <th class="col-freeze-2">Prefix</th>
                                <th class="col-freeze-3">First Name *</th>
                                <th class="col-freeze-4">Middle Name</th>
                                <th class="col-freeze-5">Last Name *</th>
                                <th class="col-freeze-6">Suffix</th>
                                <th style="min-width: 150px;">Birth Date *</th>
                                <th style="min-width: 110px;">Gender *</th>
                                <th style="min-width: 130px;">Marital Status *</th>
                                <th style="min-width: 130px;">Religion</th>
                                <th style="min-width: 130px;">Mobile No</th>
                                <th style="min-width: 180px;">Email</th>
                                <th style="min-width: 220px;">Current Address *</th>
                                <th style="min-width: 220px;">Permanent Address *</th>
                                <th style="min-width: 150px;">Emp Start Date *</th>
                                <th style="min-width: 150px;">Emp End Date</th>
                                <th style="min-width: 150px;">Employment Status *</th>
                                <th style="min-width: 130px;">Duty Status *</th>
                                <th style="min-width: 200px;">Division *</th>
                                <th style="min-width: 200px;">Department</th>
                                <th style="min-width: 200px;">Position *</th>
                                <th style="min-width: 140px;">SSS</th>
                                <th style="min-width: 140px;">PhilHealth</th>
                                <th style="min-width: 140px;">Pag-IBIG</th>
                                <th style="min-width: 140px;">TIN</th>
                                <th style="min-width: 140px;">Passport No</th>
                                <th style="min-width: 140px;">Driver's License</th>
                                <th style="min-width: 180px;">Edu Attainment *</th>
                                <th style="min-width: 180px;">School / University</th>
                                <th style="min-width: 150px;">Degree</th>
                                <th style="min-width: 130px;">Bank Name</th>
                                <th style="min-width: 150px;">Bank Account No</th>
                                <th style="min-width: 150px;">Emergency Contact</th>
                                <th style="min-width: 130px;">Emergency No</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(emp, index) in filteredEmployees()" :key="emp.id">
                                <tr :class="emp.errors ? 'table-danger' : ''">
                                    <td class="text-center fw-bold col-freeze-1" x-text="emp.id"></td>
                                    <td class="col-freeze-status text-center">
                                        <template x-if="emp.errors">
                                            <i class="bi bi-exclamation-circle-fill text-danger h5 m-0" 
                                               :title="Object.values(emp.errors).flat().join(' | ')" 
                                               style="cursor: help;"></i>
                                        </template>
                                        <template x-if="!emp.errors">
                                            <i class="bi bi-check-circle-fill text-success h5 m-0"></i>
                                        </template>
                                    </td>
                                    <td class="col-freeze-2">
                                        <input type="text" class="form-control form-control-sm" x-model="emp.prefix" :class="emp.errors?.prefix ? 'is-invalid' : ''" :title="emp.errors?.prefix?.join(' ')">
                                    </td>
                                    <td class="col-freeze-3">
                                        <input type="text" class="form-control form-control-sm" x-model="emp.firstname" required :class="emp.errors?.firstname ? 'is-invalid' : ''" :title="emp.errors?.firstname?.join(' ')">
                                    </td>
                                    <td class="col-freeze-4">
                                        <input type="text" class="form-control form-control-sm" x-model="emp.middlename" :class="emp.errors?.middlename ? 'is-invalid' : ''" :title="emp.errors?.middlename?.join(' ')">
                                    </td>
                                    <td class="col-freeze-5">
                                        <input type="text" class="form-control form-control-sm" x-model="emp.lastname" required :class="emp.errors?.lastname ? 'is-invalid' : ''" :title="emp.errors?.lastname?.join(' ')">
                                    </td>
                                    <td class="col-freeze-6">
                                        <input type="text" class="form-control form-control-sm" x-model="emp.suffix" :class="emp.errors?.suffix ? 'is-invalid' : ''" :title="emp.errors?.suffix?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm" x-model="emp.birthdate" required :class="emp.errors?.birthdate ? 'is-invalid' : ''" :title="emp.errors?.birthdate?.join(' ')">
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" x-model="emp.gender" required :class="emp.errors?.gender ? 'is-invalid' : ''" :title="emp.errors?.gender?.join(' ')">
                                            <template x-for="[key, val] in Object.entries(options.gender)" :key="key">
                                                <option :value="key" x-text="val" :selected="emp.gender === key"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" x-model="emp.marital_status" required :class="emp.errors?.marital_status ? 'is-invalid' : ''" :title="emp.errors?.marital_status?.join(' ')">
                                            <template x-for="[key, val] in Object.entries(options.marital_status)" :key="key">
                                                <option :value="key" x-text="val" :selected="emp.marital_status === key"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.religion" :class="emp.errors?.religion ? 'is-invalid' : ''" :title="emp.errors?.religion?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.mobile_no" :class="emp.errors?.mobile_no ? 'is-invalid' : ''" :title="emp.errors?.mobile_no?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="email" class="form-control form-control-sm" x-model="emp.email" :class="emp.errors?.email ? 'is-invalid' : ''" :title="emp.errors?.email?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.current_address" required :class="emp.errors?.current_address ? 'is-invalid' : ''" :title="emp.errors?.current_address?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.permanent_address" required :class="emp.errors?.permanent_address ? 'is-invalid' : ''" :title="emp.errors?.permanent_address?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm" x-model="emp.employment_start_date" required :class="emp.errors?.employment_start_date ? 'is-invalid' : ''" :title="emp.errors?.employment_start_date?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm" x-model="emp.employment_end_date" :class="emp.errors?.employment_end_date ? 'is-invalid' : ''" :title="emp.errors?.employment_end_date?.join(' ')">
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" x-model="emp.employment_status" required :class="emp.errors?.employment_status ? 'is-invalid' : ''" :title="emp.errors?.employment_status?.join(' ')">
                                            <template x-for="[key, val] in Object.entries(options.employment_status)" :key="key">
                                                <option :value="key" x-text="val" :selected="emp.employment_status === key"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" x-model="emp.duty_status" required :class="emp.errors?.duty_status ? 'is-invalid' : ''" :title="emp.errors?.duty_status?.join(' ')">
                                            <template x-for="[key, val] in Object.entries(options.duty_status)" :key="key">
                                                <option :value="key" x-text="val" :selected="emp.duty_status === key"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" x-model="emp.division" @change="emp.department = ''" required :class="emp.errors?.division ? 'is-invalid' : ''" :title="emp.errors?.division?.join(' ')">
                                            <option value="">- Select -</option>
                                            <template x-for="[key, val] in Object.entries(options.division)" :key="key">
                                                <option :value="key" x-text="val" :selected="emp.division === key"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" x-model="emp.department" :class="emp.errors?.department ? 'is-invalid' : ''" :title="emp.errors?.department?.join(' ')">
                                            <template x-for="dept in getDepartmentOptions(emp.division)" :key="dept.value">
                                                <option :value="dept.value" x-text="dept.label" :selected="emp.department === dept.value"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" x-model="emp.position" required :class="emp.errors?.position ? 'is-invalid' : ''" :title="emp.errors?.position?.join(' ')">
                                            <template x-for="[key, val] in Object.entries(options.position)" :key="key">
                                                <option :value="key" x-text="val" :selected="emp.position === key"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.sss" :class="emp.errors?.sss ? 'is-invalid' : ''" :title="emp.errors?.sss?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.philhealth" :class="emp.errors?.philhealth ? 'is-invalid' : ''" :title="emp.errors?.philhealth?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.pagibig" :class="emp.errors?.pagibig ? 'is-invalid' : ''" :title="emp.errors?.pagibig?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.tin" :class="emp.errors?.tin ? 'is-invalid' : ''" :title="emp.errors?.tin?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.passport_no" :class="emp.errors?.passport_no ? 'is-invalid' : ''" :title="emp.errors?.passport_no?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.drivers_license_no" :class="emp.errors?.drivers_license_no ? 'is-invalid' : ''" :title="emp.errors?.drivers_license_no?.join(' ')">
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" x-model="emp.educational_attainment" required :class="emp.errors?.educational_attainment ? 'is-invalid' : ''" :title="emp.errors?.educational_attainment?.join(' ')">
                                            <template x-for="[key, val] in Object.entries(options.educational_attainment)" :key="key">
                                                <option :value="key" x-text="val" :selected="emp.educational_attainment === key"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.school_university" :class="emp.errors?.school_university ? 'is-invalid' : ''" :title="emp.errors?.school_university?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.degree" :class="emp.errors?.degree ? 'is-invalid' : ''" :title="emp.errors?.degree?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.bank_name" :class="emp.errors?.bank_name ? 'is-invalid' : ''" :title="emp.errors?.bank_name?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.bank_account_no" :class="emp.errors?.bank_account_no ? 'is-invalid' : ''" :title="emp.errors?.bank_account_no?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.emergency_contact_person" :class="emp.errors?.emergency_contact_person ? 'is-invalid' : ''" :title="emp.errors?.emergency_contact_person?.join(' ')">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="emp.emergency_contact_no" :class="emp.errors?.emergency_contact_no ? 'is-invalid' : ''" :title="emp.errors?.emergency_contact_no?.join(' ')">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bulkUpdateHandler() {

            return {
                employees: JSON.parse('{!! addslashes($employeesJson) !!}').map(emp => {
                    // Convert dummy department codes (which equal division code) to empty string on load
                    if (emp.department === emp.division) {
                        emp.department = '';
                    }
                    return emp;
                }),
                options: JSON.parse('{!! addslashes($optionsJson) !!}'),
                searchQuery: '',
                loading: false,

                filteredEmployees() {
                    if (!this.searchQuery) return this.employees;
                    const q = this.searchQuery.toLowerCase();
                    return this.employees.filter(emp => {
                        const first = (emp.firstname || '').toLowerCase();
                        const last = (emp.lastname || '').toLowerCase();
                        const idStr = (emp.id || '').toString();
                        return first.includes(q) || last.includes(q) || idStr.includes(q);
                    });
                },

                getDepartmentOptions(divisionCode) {
                    
                    if (!divisionCode || !this.options.department_grouped[divisionCode]) {
                        return [];
                    }
                    const group = this.options.department_grouped[divisionCode];

                    return Object.entries(group).map(([key, val]) => ({
                        value: val === ' - ' ? '' : key,
                        label: val
                    }));
                },

                async commitUpdates() {


                    const confirm = await Swal.fire({
                        title: 'Are you sure?',
                        text: `You are about to save changes for all ${this.employees.length} employee records. This will update the database directly.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Save All Changes'
                    });

                    if (!confirm.isConfirmed) return;

                    this.loading = true;
                    this.employees.forEach(emp => emp.errors = null);

                    // Clean the data to map to expected parameters
                    const payload = {
                        rows: this.employees.map(emp => ({
                            id: emp.id,
                            prefix: emp.prefix || null,
                            firstname: emp.firstname,
                            middlename: emp.middlename || null,
                            lastname: emp.lastname,
                            suffix: emp.suffix || null,
                            birthdate: emp.birthdate,
                            gender: emp.gender,
                            marital_status: emp.marital_status,
                            religion: emp.religion || null,
                            mobile_no: emp.mobile_no || null,
                            email: emp.email || null,
                            current_address: emp.current_address,
                            permanent_address: emp.permanent_address,
                            employment_start_date: emp.employment_start_date,
                            employment_end_date: emp.employment_end_date || null,
                            employment_status: emp.employment_status,
                            duty_status: emp.duty_status,
                            division: emp.division,
                            department: emp.department || null,
                            position: emp.position,
                            sss: emp.sss || null,
                            philhealth: emp.philhealth || null,
                            pagibig: emp.pagibig || null,
                            tin: emp.tin || null,
                            passport_no: emp.passport_no || null,
                            drivers_license_no: emp.drivers_license_no || null,
                            educational_attainment: emp.educational_attainment,
                            school_university: emp.school_university || null,
                            degree: emp.degree || null,
                            bank_name: emp.bank_name || null,
                            bank_account_no: emp.bank_account_no || null,
                            emergency_contact_person: emp.emergency_contact_person || null,
                            emergency_contact_no: emp.emergency_contact_no || null,
                        }))
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
                        } else if (result.status === -2) {
                            // Validation error: Map errors back to employees
                            if (result.errors) {
                                this.employees.forEach(emp => {
                                    emp.errors = result.errors[emp.id] || null;
                                });
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Failed',
                                text: result.message || 'Please verify the records. Ensure required fields are not empty.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to save changes.'
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