@props([
    'record' => null,
])

<div class="dark:text-white text-xs">
    <div>
        @if($record)
            @if($record->created_by && $record->created_at)
                    <div>
                        Createdy By {{$record->CreatedByUser()->name}} at {{$record->created_at}}
                    </div>
            @endif

            @if($record->updated_by && $record->updated_at)
                    <div>
                        Updated By {{$record->UpdatedByUser()->name}} at {{$record->updated_at}}
                    </div>
            @endif

            @if($record->request_delete_by && $record->request_delete_at)
                    <div>
                        Updated By {{$record->RequestDeleteByUser()->name}} at {{$record->request_delete_at}}
                    </div>
            @endif

            @if($record->deleted_by && $record->deleted_at)
                    <div>
                        Deleted By {{$record->DeletedByUser()->name}} at {{$record->deleted_at}}
                    </div>
            @endif


            @if($record->approved_by && $record->approved_at)
                    <div>
                        Approved By {{$record->ApprovedByUser()->name}} at {{$record->approved_at}}
                    </div>
            @endif

        @endif
       
    </div>
</div>