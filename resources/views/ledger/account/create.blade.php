<x-app-layout>

    <div class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold text-white">
            Create Ledger Account
            </h3>
        </div>

        <div class="p-6 space-y-6">
            <form id="form">
                <div class="col-span-12 sm:col-span-12 mb-3">
                    <x-text-input label="Name" name="name" id="name"></x-text-input>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root">
            <button class="float-right text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center" id="submitBtn">Submit</button>
        </div>

    </div>


    <script type="module">
        
        submitBtn.onclick = (e) =>{
            
            $_POST('/api/ledger/account/create',form).then(reply=>{

                if(reply.status <= 0){
                    $ui.showError(reply);
                    return false;
                }

                $url('/ledger/account/'+reply.data.id);
            });
        }
    </script>
</x-app-layout>