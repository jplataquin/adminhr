<x-app-layout>

    <div id="employee_record_display" class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold text-white">
                Employee Record
            </h3>
        </div>

        <x-tab-controls tab_scope=".tabs">
            <c-tab default target="#employee_data_display_view">Data</c-tab>
            <c-tab target="#employee_options_display_view">Options</c-tab>
        </x-tab-contols>

        <div class="tabs" id="employee_data_display_view">
            {!! $employee_data_display_view !!}
        </div>

        <div class="tabs hidden p-5" id="employee_options_display_view">
            
            <x-primary-button id="generate_id_btn">Generate ID</x-primary-button>
        </div>
    </div>

    <script type="module">
        import Technologia from '/technologia.js';
        import {$q} from '/adarna.js';

        const employee_record_display = $q('#employee_record_display').first();
        const generate_id_btn         = $q('#generate_id_btn').first();
        
        Technologia.init(employee_record_display);

        generate_id_btn.onclick = (e)=>{
            window.$url('/employee/template_id/{{$employee->id}}');
        }

    </script>
</x-app-layout>
