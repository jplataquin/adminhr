<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {   
        $validator = Validator::make($request->all(),[
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,pdf'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages()
            ]);
        }
        
        $user_id    = Auth::user()->id;
        $image      = $request->file('file');
        $extension  = $image->extension();
        $file_name  = time().'_'.$user_id.'.'.$extension;

        try{  

            Storage::disk('local')->put('temp_uploads/'.$file_name, file_get_contents($image));

            return response()->json([
                'status'    => 1,
                'message'   => '',
                'data'      => [
                    'file' => $file_name
                ]
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status'    => 0,
                'message'   => $er->getMessages(),
                'data'      => []
            ]);
        }

    }
}
