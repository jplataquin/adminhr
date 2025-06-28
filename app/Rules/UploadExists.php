<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;


class UploadExists implements ValidationRule
{

    public function __construct(string $file_extensions = '')
    {
        $this->file_extensions = $file_extensions;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $ext_arr = explode(',',$this->file_extensions);

        $exists = Storage::disk('local')->exists('temp_uploads/'.$value) || Storage::disk('public')->exists('employee/photos/'.$value);

        if($value == ''){
        
            $fail('No file detected');
        
        }else if(!$exists){

            $fail('File does not exists');
            
        }else{

            if($ext_arr){
                
                $ext_flag       = false;
                $file_ext_arr   = explode('.', $value);
                $file_ext       = end($file_ext_arr);

                foreach($ext_arr as $ext){
                    if($ext == $file_ext){
                        $ext_flag = true;
                    }
                }
                
                if(!$ext_flag){
                    $fail('File type invalid (accepts: '.$this->file_extensions.')');
                }
            }

        }

    }
}
