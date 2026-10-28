<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Register New Alert') }}
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('alerts.index') }}">Alerts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Register</li>
                </ol>
            </nav>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <!-- Display validation errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('alerts.store') }}">
                                @csrf

                                <!-- Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label small font-weight-bold">Title / Item Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required placeholder="e.g. Driver's License Renewal, Server SSL Certificate">
                                    <div class="form-text small text-muted">A clear, descriptive name of what is being tracked.</div>
                                </div>

                                <div class="row mb-3">
                                    <!-- Document Type (Dropdown) -->
                                    <div class="col-md-6">
                                        <label for="document_type" class="form-label small font-weight-bold">Document / Item Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="document_type" name="document_type" required>
                                            <option value="">-- Select Type --</option>
                                            @foreach ($documentTypes as $type)
                                                <option value="{{ $type }}" {{ old('document_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text small text-muted">Select from the configured document/item types.</div>
                                    </div>

                                    <!-- Employee Link (Searchable) -->
                                    @php
                                        $employeeOptions = $employees->map(function ($emp) {
                                            return [
                                                'id' => (string) $emp->id,
                                                'name' => $emp->lastname . ', ' . $emp->firstname,
                                            ];
                                        })->values();
                                    @endphp
                                    <div class="col-md-6" 
                                         x-data="employeeSearchSelect({
                                             selectedId: @js(old('employee_id', '')),
                                             employees: @js($employeeOptions)
                                         })"
                                         @click.outside="closeDropdown()">
                                        <label for="employee_search_input" class="form-label small font-weight-bold">
                                            Linked Employee <span class="text-muted">(Optional)</span>
                                        </label>

                                        <!-- Hidden input for standard form submission -->
                                        <input type="hidden" name="employee_id" id="employee_id" :value="selectedId">

                                        <div class="position-relative">
                                            <div class="input-group">
                                                <span class="input-group-text bg-body-secondary text-muted">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                                <input type="text"
                                                       class="form-control"
                                                       id="employee_search_input"
                                                       placeholder="Type to search employee..."
                                                       autocomplete="off"
                                                       x-ref="searchInput"
                                                       x-model="search"
                                                       @focus="open = true"
                                                       @input="onInput()"
                                                       @keydown.arrow-down.prevent="onKeyDownDown()"
                                                       @keydown.arrow-up.prevent="onKeyDownUp()"
                                                       @keydown.enter.prevent="onKeyDownEnter()"
                                                       @keydown.escape.prevent="closeDropdown()">

                                                <button class="btn btn-outline-secondary" 
                                                        type="button" 
                                                        x-show="selectedId" 
                                                        @click="clear()" 
                                                        title="Clear selection">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" 
                                                        type="button" 
                                                        @click="toggleDropdown()" 
                                                        tabindex="-1"
                                                        title="Toggle dropdown">
                                                    <i class="bi bi-chevron-down"></i>
                                                </button>
                                            </div>

                                            <!-- Dropdown menu -->
                                            <div x-show="open"
                                                 x-transition
                                                 class="dropdown-menu show w-100 p-1 shadow-lg mt-1"
                                                 style="max-height: 250px; overflow-y: auto; z-index: 1060; left: 0; top: 100%;"
                                                 x-ref="dropdownList">

                                                <!-- None / General Option -->
                                                <button type="button" 
                                                        class="dropdown-item rounded py-1 px-2 small text-muted d-flex justify-content-between align-items-center"
                                                        :class="{ 'active': !selectedId, 'bg-body-secondary': highlightedIndex === -1 }"
                                                        @click="select(null)">
                                                    <span><em>-- None (General Alert) --</em></span>
                                                    <i class="bi bi-check-lg" x-show="!selectedId"></i>
                                                </button>

                                                <div class="dropdown-divider my-1"></div>

                                                <!-- Filtered Employee List -->
                                                <template x-for="(emp, index) in filteredEmployees" :key="emp.id">
                                                    <button type="button" 
                                                            class="dropdown-item rounded py-1 px-2 small d-flex justify-content-between align-items-center"
                                                            :class="{ 
                                                                'active': selectedId === emp.id, 
                                                                'bg-body-secondary active-highlight': highlightedIndex === index && selectedId !== emp.id 
                                                            }"
                                                            @mouseenter="highlightedIndex = index"
                                                            @click="select(emp)">
                                                        <span x-text="emp.name"></span>
                                                        <i class="bi bi-check-lg" x-show="selectedId === emp.id"></i>
                                                    </button>
                                                </template>

                                                <!-- Empty state -->
                                                <div x-show="filteredEmployees.length === 0" class="p-2 text-center text-muted small">
                                                    No employees matching "<span x-text="search"></span>"
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-text small text-muted">Type to filter and link a specific employee.</div>

                                        <noscript>
                                            <select class="form-select mt-2" name="employee_id">
                                                <option value="">-- None (General Alert) --</option>
                                                @foreach ($employees as $emp)
                                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                                        {{ $emp->lastname }}, {{ $emp->firstname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </noscript>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <!-- Expiry Date -->
                                    <div class="col-md-6">
                                        <label for="expiry_date" class="form-label small font-weight-bold">Expiry Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}" required>
                                        <div class="form-text small text-muted">The final expiration date of the document or item.</div>
                                    </div>

                                    <!-- Alert Days Before -->
                                    <div class="col-md-6">
                                        <label for="alert_days_before" class="form-label small font-weight-bold">Warning Threshold (Days Before) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="alert_days_before" name="alert_days_before" value="{{ old('alert_days_before', 30) }}" min="0" required>
                                        <div class="form-text small text-muted">Number of days before expiry to trigger a Warning state.</div>
                                    </div>
                                </div>

                                <!-- Reference -->
                                <div class="mb-3">
                                    <label for="reference" class="form-label small font-weight-bold">Reference <span class="text-muted">(Optional)</span></label>
                                    <input type="text" class="form-control" id="reference" name="reference" value="{{ old('reference') }}" placeholder="e.g. Passport number, contract reference, transaction ID">
                                    <div class="form-text small text-muted">A code, identifier, or serial number related to this alert (max 250 characters).</div>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="description" class="form-label small font-weight-bold">Description / Additional Notes</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter any extra details, instructions, or renewal reference links...">{{ old('description') }}</textarea>
                                </div>

                                <!-- Form Actions -->
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('alerts.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                    <button type="submit" class="btn btn-primary btn-sm">Register Alert</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function employeeSearchSelect(config) {
            return {
                open: false,
                search: '',
                selectedId: config.selectedId ? String(config.selectedId) : '',
                employees: config.employees || [],
                highlightedIndex: -1,

                init() {
                    if (this.selectedId) {
                        const match = this.employees.find(e => String(e.id) === String(this.selectedId));
                        if (match) {
                            this.search = match.name;
                        }
                    }
                },

                get filteredEmployees() {
                    if (!this.search.trim() || this.isSelectedName(this.search)) {
                        return this.employees;
                    }
                    const q = this.search.toLowerCase().trim();
                    return this.employees.filter(e => e.name.toLowerCase().includes(q));
                },

                isSelectedName(text) {
                    if (!this.selectedId) return false;
                    const match = this.employees.find(e => String(e.id) === String(this.selectedId));
                    return match && match.name.toLowerCase() === text.toLowerCase().trim();
                },

                select(emp) {
                    if (emp) {
                        this.selectedId = String(emp.id);
                        this.search = emp.name;
                    } else {
                        this.selectedId = '';
                        this.search = '';
                    }
                    this.open = false;
                    this.highlightedIndex = -1;
                },

                clear() {
                    this.selectedId = '';
                    this.search = '';
                    this.open = false;
                    this.highlightedIndex = -1;
                    this.$nextTick(() => {
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    });
                },

                toggleDropdown() {
                    this.open = !this.open;
                    if (this.open) {
                        this.$nextTick(() => {
                            if (this.$refs.searchInput) {
                                this.$refs.searchInput.focus();
                            }
                        });
                    }
                },

                closeDropdown() {
                    this.open = false;
                    this.highlightedIndex = -1;
                    if (this.selectedId) {
                        const match = this.employees.find(e => String(e.id) === String(this.selectedId));
                        if (match) {
                            this.search = match.name;
                        }
                    } else if (!this.isSelectedName(this.search)) {
                        this.search = '';
                    }
                },

                onInput() {
                    this.open = true;
                    this.highlightedIndex = 0;
                    if (this.selectedId) {
                        const match = this.employees.find(e => String(e.id) === String(this.selectedId));
                        if (!match || match.name.toLowerCase() !== this.search.toLowerCase().trim()) {
                            this.selectedId = '';
                        }
                    }
                },

                onKeyDownDown() {
                    if (!this.open) {
                        this.open = true;
                        return;
                    }
                    const total = this.filteredEmployees.length;
                    if (this.highlightedIndex < total - 1) {
                        this.highlightedIndex++;
                        this.scrollIntoView();
                    }
                },

                onKeyDownUp() {
                    if (this.highlightedIndex > -1) {
                        this.highlightedIndex--;
                        this.scrollIntoView();
                    }
                },

                onKeyDownEnter() {
                    if (this.open) {
                        if (this.highlightedIndex === -1 && !this.search.trim()) {
                            this.select(null);
                        } else if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredEmployees.length) {
                            this.select(this.filteredEmployees[this.highlightedIndex]);
                        } else if (this.filteredEmployees.length === 1) {
                            this.select(this.filteredEmployees[0]);
                        }
                    }
                },

                scrollIntoView() {
                    this.$nextTick(() => {
                        if (!this.$refs.dropdownList) return;
                        const el = this.$refs.dropdownList.querySelector('.active-highlight');
                        if (el) {
                            el.scrollIntoView({ block: 'nearest' });
                        }
                    });
                }
            };
        }
    </script>
</x-app-layout>
