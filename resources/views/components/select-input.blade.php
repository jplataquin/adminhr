@props([
    'disabled'      => false,
    'label'         => '',
    'id'            => '',
    'dependon'      => '',
    'name'          => ''
])

@if($label != '')
<label for="{{$id}}" class="form-label">{{$label}}</label>
@endif

<select @disabled($disabled) name="{{$name}}" id="{{$id}}" dependon="{{$dependon}}" {{ $attributes->merge(['class' => 'form-select']) }}>
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

            if(opt.selected == false){
                opt.style.display   = 'none';
            }
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
