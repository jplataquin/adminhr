<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Ledger;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerAccount extends Model
{
    
    use SoftDeletes;

    protected $table = 'ledger_accounts';

    public function Ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class);
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
