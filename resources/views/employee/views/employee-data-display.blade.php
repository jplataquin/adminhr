<div>
    <div class="p-6 space-y-6">
            <form action="#">      
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-6">
                        <x-image-input name="photo" disabled="true" class="editable" value="{{$employee->photo}}"></x-image-input>
                    </div>
                </div>
                
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Prefix" id="prefix" value="{{$employee->prefix}}" data-value="{{$employee->prefix}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Birth Date" id="birthdate" value="{{$employee->birthdate}}" data-value="{{$employee->birthdate}}" disabled="true" class="editable"></x-text-input>
                    </div>
                

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Firstname" id="firstname" value="{{$employee->firstname}}" data-value="{{$employee->firstname}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Middlename" id="middlename" value="{{$employee->middlename}}" data-value="{{$employee->middlename}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Lastname" id="lastname" value="{{$employee->lastname}}" data-value="{{$employee->lastname}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Suffix" id="suffix" value="{{$employee->suffix}}" data-value="{{$employee->suffix}}" disabled="true" class="editable"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Employment Start Date" id="employment_start_date"  value="{{$employee->employment_start_date}}" data-value="{{$employee->employment_start_date}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Employment End Date" id="employment_end_date"  value="{{$employee->employment_end_date}}" data-value="{{$employee->employment_end_date}}" disabled="true" class="editable"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Employment Status" id="employment_status" data-value="{{$employee->employment_status}}" disabled="true" class="editable">
                            @foreach($employee->employment_status_options() as $val=>$text)
                                <option value="{{$val}}" @if($employee->employment_status == $val) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Duty Status" id="duty_status" data-value="{{$employee->duty_status}}" disabled="true" class="editable">
                            @foreach($employee->duty_status_options() as $val=>$text)
                                <option value="{{$val}}" @if($employee->duty_status == $val) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Division" id="division" data-value="{{$employee->division}}" disabled="true" class="editable">
                            @foreach($employee->division_options() as $val=>$text)
                                <option value="{{$val}}" @if($employee->division == $val) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Department" id="department" data-value="{{$employee->department}}" dependon="#division" disabled="true" class="editable">
                            @foreach($employee->department_options_grouped() as $group=>$options)
                                @foreach($options as $val=>$text)
                                    <option group="{{$group}}" value="{{$val}}" @if($employee->department == $val) selected @endif >{{$text}}</option>
                                @endforeach
                            @endforeach
                        </x-select-input>
                    </div>

                    <div class="col-span-full">
                        <x-select-input label="Position" id="position" data-value="{{$employee->position}}" disabled="true" class="editable">
                            @foreach($employee->position_options() as $val=>$text)
                                <option value="{{$val}}" @if($employee->position == $val) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>

                    <div class="col-span-full">
                        <hr class="border-gray-800 dark:border-white "/>
                    </div>


                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Email" id="email" value="{{$employee->email}}" data-value="{{$employee->email}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Mobile No." id="mobile_no" value="{{$employee->mobile_no}}" data-value="{{$employee->mobile_no}}" disabled="true" class="editable"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Educational Attainment" id="educational_attainment" data-value="{{$employee->educational_attainment}}" disabled="true" class="editable">
                            @foreach($employee->educational_attainment_options() as $val=>$text)
                                <option value="{{$val}}" @if($employee->educational_attainment == $val) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="School/University" id="school_university" value="{{$employee->school_university}}" data-value="{{$employee->school_university}}" disabled="true" class="editable"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Degree" id="degree"  value="{{$employee->degree}}" data-value="{{$employee->degree}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Gender" id="gender" data-value="{{$employee->gender}}" disabled="true" class="editable">
                            @foreach($employee->gender_options() as $val=>$text)
                                <option value="{{$val}}" @if($employee->gender == $val) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Marital Status" id="marital_status" data-value="{{$employee->marital_status}}" disabled="true" class="editable">
                            @foreach($employee->marital_status_options() as $val=>$text)
                                <option value="{{$val}}" @if($employee->marital_status == $val) selected @endif>{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Religion" id="religion"  value="{{$employee->religion}}" data-value="{{$employee->religion}}" disabled="true" class="editable"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Emergency Contact Person" id="emergency_contact_person"  value="{{$employee->emergency_contact_person}}" data-value="{{$employee->emergency_contact_person}}" disabled="true" class="editable"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Emergency Contact No." id="emergency_contact_no"  value="{{$employee->emergency_contact_no}}" data-value="{{$employee->emergency_contact_no}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    
                    <div class="col-span-full">
                        <x-textarea-input label="Current Address" id="current_address" data-value="{{$employee->current_address}}" disabled="true" class="editable">
                            {{$employee->current_address}}
                        </x-textarea-input>
                    </div>                    
                    <div class="col-span-full">
                        <x-textarea-input label="Permanent Address" id="permanent_address" data-value="{{$employee->permanent_address}}" disabled="true" class="editable">
                            {{$employee->permanent_address}}
                        </x-textarea-input>
                    </div>
                    
                        
                        
                    <div class="col-span-full">
                        <hr class="border-gray-800 dark:border-white "/>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="SSS" id="sss"  value="{{$employee->sss}}" data-value="{{$employee->sss}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Philhealth" id="philhealth" value="{{$employee->philhealth}}" data-value="{{$employee->philhealth}}" disabled="true" class="editable"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Pag IBIG" id="pagibig" value="{{$employee->pagibig}}" data-value="{{$employee->pagibig}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="TIN" id="tin" value="{{$employee->tin}}" data-value="{{$employee->tin}}" disabled="true" class="editable"></x-text-input>
                    </div>


                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Passport No." id="passport_no" value="{{$employee->passport_no}}" data-value="{{$employee->passport_no}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Driver's License No." id="drivers_license_no" value="{{$employee->drivers_license_no}}" data-value="{{$employee->drivers_license_no}}" disabled="true" class="editable"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Bank Name" id="bank_name" value="{{$employee->bank_name}}" data-value="{{$employee->bank_name}}" disabled="true" class="editable"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Bank Account No." id="bank_account_no" value="{{$employee->bank_account_no}}" data-value="{{$employee->bank_account_no}}" disabled="true" class="editable"></x-text-input>
                    </div>

                </div>
            </form>
            
            
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root">
            <div class="float-right">
                <x-primary-button class="me-2" id="editBtn">Edit</x-primary-button>
                <x-primary-button class="hidden me-2" id="updateBtn">Update</x-primary-button>
                <x-secondary-button class="me-2" id="cancelBtn">Cancel</x-secondary-button>
                
            </div>
        </div>

        <script type="module">
            import {$q} from '/adarna.js';

            const editBtn   = $id('editBtn');
            const updateBtn = $id('updateBtn');
            const cancelBtn = $id('cancelBtn');

            $dateOnlyInput([
                birthdate,
                employment_start_date,
                employment_end_date
            ]);
            
            cancelBtn.onclick = (e)=>{
                window.$back();
            }

            editBtn.onclick = (e)=>{

                $q('.editable').items().map(item=>{

                    //for div
                    if(typeof item.disabled == 'undefined'){
                        item.setAttribute('disabled',false);
                    }else{
                        item.disabled = false;
                    }
                });

                editBtn.classList.add('hidden');
                updateBtn.classList.remove('hidden');

                cancelBtn.onclick = (e)=>{
                    e.preventDefault();

                    editBtn.classList.remove('hidden');
                    updateBtn.classList.add('hidden');

                    $q('.editable').items().map(item=>{
                        
                        //for div
                        if(typeof item.disabled == 'undefined'){
                            item.setAttribute('disabled',true);
                        }else{
                            item.disabled = true;
                            item.value = item.getAttribute('data-value');
                        }
                        
                    });

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    cancelBtn.onclick = ()=>{
                        window.$back();
                    }
                }
            }

            updateBtn.onclick = (e)=>{
                
                $ui.blockUI();

                $_POST('/api/employee/update',{
                    id                       :'{{$employee->id}}',
                    photo                    : data_photo.value,
                    prefix                   : prefix.value,
                    birthdate                : birthdate.value,
                    firstname                : firstname.value,
                    middlename               : middlename.value,
                    lastname                 : lastname.value,
                    suffix                   : suffix.value,
                    email                    : email.value,
                    mobile_no                : mobile_no.value,
                    educational_attainment   : educational_attainment.value,
                    school_university        : school_university.value,
                    degree                   : degree.value,
                    gender                   : gender.value,
                    marital_status           : marital_status.value,
                    religion                 : religion.value,
                    current_address          : current_address.value,
                    permanent_address        : permanent_address.value,
                    employment_start_date    : employment_start_date.value,
                    employment_end_date      : employment_end_date.value,
                    employment_status        : employment_status.value,
                    duty_status              : duty_status.value,
                    division                 : division.value,
                    department               : department.value,
                    position                 : position.value,
                    sss                      : sss.value,
                    philhealth               : philhealth.value,
                    pagibig                  : pagibig.value,
                    tin                      : tin.value,
                    passport_no              : passport_no.value,
                    drivers_license_no       : drivers_license_no.value,
                    bank_name                : bank_name.value,
                    bank_account_no          : bank_account_no.value,
                    emergency_contact_person : emergency_contact_person.value,
                    emergency_contact_no     : emergency_contact_no.value
                }).then(reply=>{

                    $ui.unblockUI();

                    if(reply.status <= 0){
                        
                        $ui.showError(reply);

                        return false;
                    }

                    window.$url('/employee/'+reply.data.id);
                });
            }
        </script>
</div>