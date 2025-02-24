@props([
    'namespace'      => ''
])

<div class="float-root" id="{{$namespace}}controls">
    <div class="float-left">
        
        @if($record->status == 'PEND')
            <x-primary-button class="me-2" id="{{$namespace}}rejectBtn">Reject</x-primary-button>
        @endif

        
        @if($record->status == 'RDEL')
            <x-primary-button class="me-2" id="{{$namespace}}approveDeleteBtn">Approve Delete</x-primary-button>
        @endif

        @if(isset($right))
            {{$right}}
        @endif
    </div>
    <div class="float-right">
        @if(isset($left))
            {{$left}}
        @endif

        @if($record->status == 'PEND')
            <x-primary-button class="me-2" id="{{$namespace}}approveBtn">Approve</x-primary-button>
        @endif

        
        @if($record->status == 'RDEL')
            <x-primary-button class="me-2" id="{{$namespace}}rejectDeleteBtn">Reject Delete</x-primary-button>
        @endif
        <x-secondary-button class="" id="{{$namespace}}cancelBtn">Cancel</x-secondary-button>
    </div>
</div>
<script type="module">
    
    let approveDeleteBtn = $id('{{$namespace}}approveDeleteBtn',document.createElement('button'));
    let rejectDeleteBtn  = $id('{{$namespace}}rejectDeleteBtn',document.createElement('button'));
    let approveBtn       = $id('{{$namespace}}approveBtn',document.createElement('button'));
    let rejectBtn        = $id('{{$namespace}}rejectBtn',document.createElement('button'));
    
    let callback = [];
    let state = 'review';

    function changeState(type,action){
        state = type;
        callback.map(f=>{
            f(type,action);
        });
    }

    {{$namespace}}controls.onApproveClick        = ()=>{};
    {{$namespace}}controls.onRejectClick         = ()=>{};
    {{$namespace}}controls.onCancelClick         = ()=>{};
    
    {{$namespace}}controls.onApproveDeleteClick        = ()=>{};
    {{$namespace}}controls.onRejectDeleteClick         = ()=>{};
    

    approveDeleteBtn.addEventListener('click',()=>{
        
        changeState('delete','delete:approve');

        {{$namespace}}controls.onApproveDeleteClick();
    });


    rejectDeleteBtn.addEventListener('click',()=>{
        
        changeState('delete','delete:reject');

        {{$namespace}}controls.onRejectDeleteClick();
    });

    {{$namespace}}approveBtn.addEventListener('click',()=>{
        
        changeState('update','update:approve');

        {{$namespace}}controls.onApproveClick();
    });

    {{$namespace}}rejectBtn.addEventListener('click',()=>{
        
        changeState('update','update:reject');

        {{$namespace}}controls.onRejectClick();
    });

    {{$namespace}}cancelBtn.addEventListener('click',()=>{
        
        {{$namespace}}controls.onCancelClick();
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
