<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                {{ __("You're logged in!") }}
            </div>
        </div>
    </div>

    <div class="mt-4" data-controller="/test.js">
        <button class="btn btn-primary" data-el="submit" data-val="a">a</button>
    </div>
    <div class="mt-2" data-controller="/test.js">
        <div class="mb-3">
            <button class="btn btn-secondary" data-el="submit" data-val="b">b</button>
        </div>
        
        <div data-controller="/test.js">
            <div>
                <button class="btn btn-info" data-el="submit" data-val="c">c</button>
            </div>
        </div>
    </div>
    <script type="module">
        import Teknologia from '/technologia.js';

        let d = {
            test: (root,el)=>{

                el.submit.onclick = ()=>{
                    
                    alert(el.submit.innerHTML);
                }
             
            }
        } 

        Teknologia.init(document.body,d).observe();


    </script>
</x-app-layout>
