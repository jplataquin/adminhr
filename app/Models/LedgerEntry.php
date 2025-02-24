<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Ledger;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    
    use SoftDeletes;

    protected $table = 'ledger_entries';

    public function Ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function amount(){
        return ($this->quantity * $this->unit_amount);
    }

    public function tag_options($key = null){
         
        $opt = [
            'REGU' => 'Regular',
            'FLOA' => 'Floating'
        ];

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        return (object) $opt;
    }

    public function type_options($key = null){

        
        $opt = [
            'CRED' => 'Credit (+)',
            'DEBI' => 'Debit (-)'
        ];

        if($key != null){
            return isset($opt[$key]) ? $opt[$key] : '';
        }

        return (object) $opt;
    }


    public function CreatedByUser(){   

        $user = User::find($this->created_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }


    public function UpdatedByUser(){   

        $user = User::find($this->updated_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    
    public function ApprovedByUser(){   

        $user = User::find($this->approved_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function RejectedByUser(){   

        $user = User::find($this->rejected_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function RequestDeleteByUser(){   

        $user = User::find($this->request_delete_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function RejectDeleteByUser(){   

        $user = User::find($this->reject_request_delete_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }

    public function ApproveDeleteByUser(){   

        $user = User::find($this->approve_request_delete_by);

        if(!$user){
            return User::defaultAttirbutes();
        }

        return $user;
    }
}
