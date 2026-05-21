<x-app-layout>
    <div id="employee_record_display" class="card shadow-sm border-0 m-4">

        <div class="card-header bg-body py-3">
            <h3 class="h5 mb-0">
                Employee Record
            </h3>
        </div>

        <div class="card-header bg-body border-top-0 p-0">
            <x-tab-controls tab_scope=".tabs">
                <ul class="nav nav-tabs px-3 border-bottom-0">
                    <li class="nav-item">
                        <c-tab default target="#employee_data_display_view" class="nav-link active">Data</c-tab>
                    </li>
                    <li class="nav-item">
                        <c-tab target="#employee_options_display_view" class="nav-link">Options</c-tab>
                    </li>
                </ul>
            </x-tab-controls>
        </div>

        <div class="tabs" id="employee_data_display_view">
            {!! $employee_data_display_view !!}
        </div>

        <div class="tabs d-none p-4" id="employee_options_display_view">
            <div class="card bg-body-tertiary">
                <div class="card-body">
                    <h5 class="card-title">Administrative Actions</h5>
                    <div class="mt-3">
                        <x-primary-button id="generate_id_btn">Generate ID</x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import Technologia from '/technologia.js';
        import {$q} from '/adarna.js';

        const employee_record_display = $q('#employee_record_display').first();
        const generate_id_btn         = $q('#generate_id_btn').first();
        
        Technologia.init(employee_record_display);

        generate_id_btn.onclick = (e)=>{
            window.$tab('/employee/template_id/{{$employee->id}}','Company ID');
        }

    </script>
</x-app-layout>
