<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlertDocumentType extends Model
{
    use SoftDeletes;

    protected $table = 'alert_document_types';

    protected $fillable = [
        'name',
    ];
}
