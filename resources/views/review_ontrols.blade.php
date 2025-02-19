@props([
    'namespace'      => ''
])

<div class="float-root" id="{{$namespace}}controls">
    <div class="float-left">
        
        @if($record->status != 'PEND')
            <x-primary-button class="me-2" id="{{$namespace}}rejectBtn">Reject</x-primary-button>
        @else
            <x-primary-button class="me-2 hidden" id="{{$namespace}}rejectBtn">Reject</x-primary-button>
        @endif

        @if(isset($right))
            {{$right}}
        @endif
    </div>
    <div class="float-right">
        @if(isset($left))
            {{$left}}
        @endif
        <x-primary-button class="me-2 @if($status != 'PEND') d-none @endif" id="{{$namespace}}approveBtn">Approve</x-primary-button>
        <x-secondary-button class="" id="{{$namespace}}cancelBtn">Cancel</x-secondary-button>
    </div>
</div>
<script type="module">
    
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
