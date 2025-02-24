@props([
    'namespace'      => '',
    'status'         => 'PEND'
])

<div class="float-root" id="{{$namespace}}controls">
    <div class="float-left">
        @if($status == 'PEND')
            <x-primary-button class="me-2 hidden" id="{{$namespace}}deleteBtn">Delete</x-primary-button>
        @endif
        
        @if($status == 'APRV')
            <x-primary-button class="me-2" id="{{$namespace}}requestDeleteBtn">Request Delete</x-primary-button>
        @endif


        @if(isset($left))
            {{$left}}
        @endif
    </div>
    <div class="float-right">
        @if(isset($right))
            {{$right}}
        @endif

        @if($status == 'PEND')
            <x-primary-button class="me-2" id="{{$namespace}}editBtn">Edit</x-primary-button>
            <x-primary-button class="hidden me-2" id="{{$namespace}}updateBtn">Update</x-primary-button>
        @endif
        <x-secondary-button class="" id="{{$namespace}}cancelBtn">Cancel</x-secondary-button>
    </div>
</div>
<script type="module">
    
    let {{$namespace}}editBtn           = $id('{{$namespace}}editBtn',document.createElement('button'));
    let {{$namespace}}updateBtn         = $id('{{$namespace}}updateBtn',document.createElement('button'));
    let {{$namespace}}deleteBtn         = $id('{{$namespace}}deleteBtn',document.createElement('button'));
    let {{$namespace}}requestDeleteBtn  = $id('{{$namespace}}requestDeleteBtn',document.createElement('button'));

    let callback = [];
    let state = 'edit';

    function changeState(type,action){
        state = type;
        callback.map(f=>{
            f(type,action);
        });
    }

    {{$namespace}}controls.onCancelClick                = ()=>{};
    {{$namespace}}controls.onUpdateCancel               = ()=>{};
    {{$namespace}}controls.onDeleteClick                = ()=>{};
    {{$namespace}}controls.onEditClick                  = ()=>{};
    {{$namespace}}controls.onUpdateClick                = ()=>{};
    {{$namespace}}controls.onRequestDeleteClick         = ()=>{};

    {{$namespace}}editBtn.addEventListener('click',()=>{
        
        {{$namespace}}editBtn.classList.add('hidden');
        {{$namespace}}updateBtn.classList.remove('hidden');
        {{$namespace}}deleteBtn.classList.remove('hidden');
        
        changeState('update','update:start');

        {{$namespace}}controls.onEditClick();
    });

    {{$namespace}}updateBtn.addEventListener('click',()=>{
         
         changeState('update','update:click');

        {{$namespace}}controls.onUpdateClick();
    });


    {{$namespace}}requestDeleteBtn.addEventListener('click',()=>{

        changeState('request_delete','request_delete:click');

        {{$namespace}}controls.onRequestDeleteClick();
    });

    {{$namespace}}cancelBtn.addEventListener('click',()=>{

        if(state == 'update'){
            {{$namespace}}editBtn.classList.remove('hidden');
            {{$namespace}}updateBtn.classList.add('hidden');
            {{$namespace}}deleteBtn.classList.add('hidden');
            
            changeState('edit','cancel:update');

            
            {{$namespace}}controls.onUpdateCancel();

            return true;
        }

        changeState(state,'cancel:edit');
        
        {{$namespace}}controls.onCancelClick();

    });

    {{$namespace}}deleteBtn.addEventListener('click',()=>{

        changeState(state,'delete:click');
        
        {{$namespace}}controls.onDeleteClick();
    });

    {{$namespace}}controls.getState = ()=>{
        return state;
    }

    {{$namespace}}controls.onStateChange = (f)=>{
        callback.push(f);

        return callback.length-1;
    }

    {{$namespace}}controls.removeCallback = (index)=>{
        callback = callback.splice(index, 1);
    }
    

</script>