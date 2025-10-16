<x-app-layout>

    <div class="border border-1 rounded-lg shadow relative m-10">

        <div class="flex items-start justify-between p-5 border-b rounded-t">
            <h3 class="text-xl font-semibold text-white">
               Create Employee
            </h3>
        </div>

        <div class="p-6 space-y-6">
            <form action="#">
                
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-6">
                        <x-image-input name="photo"></x-image-input>
                    </div>
                </div>
                
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Prefix" id="prefix"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input required="true" label="Birth Date" type="date" id="birthdate"></x-text-input>
                    </div>
                   

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input required="true" label="Firstname" id="firstname"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Middlename" id="middlename"></x-text-input>
                    </div>
                    
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input required="true" label="Lastname" id="lastname"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Suffix" id="suffix"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                            <x-text-input required="true" label="Employment Start Date" type="date" id="employment_start_date"></x-text-input>
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <x-text-input label="Employment End Date" type="date" id="employment_end_date"></x-text-input>
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <x-select-input label="Employment Status" id="employment_status">
                                @foreach($employee->employment_status_options() as $val=>$text)
                                    <option value="{{$val}}">{{$text}}</option>
                                @endforeach
                            </x-select-input>
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <x-select-input label="Duty Status" id="duty_status">
                                @foreach($employee->duty_status_options() as $val=>$text)
                                    <option value="{{$val}}">{{$text}}</option>
                                @endforeach
                            </x-select-input>
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <x-select-input label="Division" id="division">
                                @foreach($employee->division_options() as $val=>$text)
                                    <option value="{{$val}}">{{$text}}</option>
                                @endforeach
                            </x-select-input>
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <x-select-input label="Department" id="department" dependon="#division">
                                @foreach($employee->department_options_grouped() as $group=>$options)
                                    @foreach($options as $val=>$text)
                                        <option group="{{$group}}" value="{{$val}}">{{$text}}</option>
                                    @endforeach
                                @endforeach
                            </x-select-input>
                        </div>

                        <div class="col-span-full">
                            <x-select-input label="Position" id="position">
                                @foreach($employee->position_options() as $val=>$text)
                                    <option value="{{$val}}">{{$text}}</option>
                                @endforeach
                            </x-select-input>
                        </div>
                      
                    
                    <div class="col-span-full">
                        <hr class="border-gray-800 dark:border-white "/>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Email" id="email"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Mobile No." id="mobile_no"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Educational Attainment" id="educational_attainment">
                            @foreach($employee->educational_attainment_options() as $val=>$text)
                                <option value="{{$val}}">{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="School/University" id="school_university"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Degree" id="degree"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Gender" id="gender">
                            @foreach($employee->gender_options() as $val=>$text)
                                <option value="{{$val}}">{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-select-input label="Marital Status" id="marital_status">
                            @foreach($employee->marital_status_options() as $val=>$text)
                                <option value="{{$val}}">{{$text}}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Religion" id="religion"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Emergency Contact Person" id="emergency_contact_person"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Emergency Contact No." id="emergency_contact_no"></x-text-input>
                    </div>
                    
                    <div class="col-span-full">
                        <x-textarea-input label="Current Address" id="current_address" required="true"></x-textarea-input>
                    </div>                    
                    <div class="col-span-full">
                        <x-textarea-input label="Permanent Address" id="permanent_address" required="true"></x-textarea-input>
                    </div>
                    

                        
                    <div class="col-span-full">
                        <hr class="border-gray-800 dark:border-white "/>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="SSS" id="sss"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Philhealth" id="philhealth"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Pag-IBIG" id="pagibig"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="TIN" id="tin"></x-text-input>
                    </div>


                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Passport No." id="passport_no"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Driver's License No." id="drivers_license_no"></x-text-input>
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Bank Name" id="bank_name"></x-text-input>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <x-text-input label="Bank Account No." id="bank_account_no"></x-text-input>
                    </div>

                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 rounded-b flow-root">
            <div class="float-right">
                <x-primary-button class="me-2" id="submitBtn">Submit</x-secondary-button>
                <x-secondary-button class="me-2" id="cancelBtn">Cancel</x-secondary-button>
            </div>
        </div>

    </div>

    <script type="module">
        
        import {$q} from '/adarna.js';

        const submitBtn = $id('submitBtn');
        const cancelBtn = $id('cancelBtn');

        $dateOnlyInput([
            birthdate,
            employment_start_date,
            employment_end_date
        ]);

        cancelBtn.onclick = (e)=>{
            window.$url('/employees');
        }
        
        submitBtn.onclick = (e)=>{
            
            $ui.blockUI();

            $_POST('/api/employee/create',{
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
</x-app-layout>
