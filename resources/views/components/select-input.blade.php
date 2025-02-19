@props([
    'disabled'      => false,
    'label'         => '',
    'id'            => '',
    'dependon'      => '',
    'name'          => ''
])

@if($label != '')
<label class="text-sm font-medium text-white block mb-2">{{$label}}</label>
@endif

<select @disabled($disabled) name="{{$name}}" id="{{$id}}" dependon="{{$dependon}}" {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full p-2.5']) }}>
{{$slot}}
</select>

@if($dependon != '' && $id != '')
<script type="text/javascript">

    let target  = document.querySelector('{{$dependon}}');
    let elem    = document.querySelector('#{{$id}}');

    function change(){

        let val     = target.value;
        let options = elem.querySelectorAll('option');

        options.forEach(opt=>{
            opt.style.display   = 'none';
            opt.selected        = false;
        });

        options.forEach(opt=>{

            if(opt.getAttribute('group') == val){
                opt.style.display   = 'inline';
            }

        });
    }
    
    target.addEventListener('change',change);

    change();
</script>
@endif
