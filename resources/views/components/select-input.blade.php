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
(function() {
    let target  = document.querySelector('{{$dependon}}');
    let elem    = document.querySelector('#{{$id}}');

    if (!target || !elem) return;

    function change(){

        let val     = target.value;
        let options = elem.querySelectorAll('option');

        options.forEach(opt=>{
            let group = opt.getAttribute('group');

            if(!group){
                opt.style.display   = 'inline';
            }else if(group == val){
                opt.style.display   = 'inline';
            }else{
                opt.style.display   = 'none';
            }
        });

        if(elem.selectedIndex >= 0 && elem.options[elem.selectedIndex].style.display === 'none'){
            let firstVisible = Array.from(options).find(opt => opt.style.display !== 'none');
            elem.value = firstVisible ? firstVisible.value : '';
        }
    }
    
    target.addEventListener('change',change);

    change();
})();
</script>
@endif
