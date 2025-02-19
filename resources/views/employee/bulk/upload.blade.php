<x-app-layout>
    <div class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold text-white">
            Employee
            </h3>
        </div>

        <div class="p-6 space-y-6">
            
            <label class="text-sm font-medium text-white block mb-2">Choose CSV File</label>

            <input type="file" id="file_csv" class='border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full p-2.5'/>

        </div>

        <form id="form" method="POST" action="/employee/bulk/review">
            <input type="hidden" id="form_data" name="data" value=""/>
            @csrf
        </form>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root">
            <button disabled="true" class="float-right text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center" id="submitBtn">Submit</button>
        </div>
    </div>

    <script type="module">
        

        file_csv.onchange = ()=>{

            submitBtn.disabled = true;

            let fr = new FileReader();

            fr.onload = function () {
                
                form_data.value = fr.result;

                submitBtn.disabled = false;
            
            }

            fr.readAsText(file_csv.files[0]);
        }


        submitBtn.onclick = ()=>{
            
            form.submit();
        }
    </script>
</x-app-layout>