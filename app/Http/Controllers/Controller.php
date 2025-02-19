<?php

namespace App\Http\Controllers;

class Controller
{
    protected function format_in($obj){

        return implode(',',array_keys(get_object_vars($obj)));
    }
}
