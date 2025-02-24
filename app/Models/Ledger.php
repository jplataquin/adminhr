<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\LedgerEntry;
use App\Models\LedgerAccount;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Ledger extends Model
{
    
    use SoftDeletes;

    protected $table = 'ledgers';

    public function Entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function Account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class,'ledger_account_id');
    }

    public function CreatedByUser(){   

        $user = User::find($this->created_by);

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

    public function ClosedByUser(){   

        $user = User::find($this->closed_by);

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

    public function getTotalCredit(Array $status = [],Array $tag = []){

        $entries = $this->Entries()->where('type','CRED');

        if($status){
            $entries = $entries->whereIn('status',$status);
        }

        if($tag){
            $entries = $entries->whereIn('tag',$tag);
        } 

        return $entries->sum(\DB::raw('quantity * unit_amount'));
    }

    public function getTotalDebit(Array $status = [],Array $tag = []){
        
        $entries = $this->Entries()->where('type','DEBI');
        
        if($status){
            $entries = $entries->whereIn('status',$status);
        }
        
        if($tag){
            $entries = $entries->whereIn('tag',$tag);
        } 

        return $entries->sum(\DB::raw('quantity * unit_amount'));
    }


    public function getTotalQuantity(Array $status = [],Array $tag = []){
        
        $entries = $this->Entries();
        
        if($status){
            $entries = $entries->whereIn('status',$status);
        }
        
        if($tag){
            $entries = $entries->whereIn('tag',$tag);
        } 

        return $entries->sum('quantity');
    }

}
