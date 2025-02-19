import './bootstrap';
import './template';
import Alpine from 'alpinejs';

import {Template} from 'adarna';


window.Alpine = Alpine;

Alpine.start();


window.$ui = {};

window.$ui.showError = (reply) =>{

    let t = new Template();
    
    if(reply.status == -2){

        Swal.fire({
            icon: "warning",
            title: 'Validation Error',
            html: t.div({style:{
                textAlign:'left',
            }},()=>{
    
                if(reply.data){
                    for(let name in reply.data){
                        let msgs = reply.data[name];
                        
                        
                        t.label({style:{fontWeight:'bold'}},name);

                        t.div({style:{
                            paddingLeft:'1rem',
                            marginBottom:'1rem'
                        }},()=>{
                            t.ul(()=>{
                                
                                msgs.map(msg =>{
                                    t.li(msg);
                                });
        
                            });
                        });
        
                    }//for
                }//if
            }),
           
        });

        return false;
    }


    if(reply.status == 0){

        Swal.fire({
            icon: "error",
            title: 'Error',
            html: t.div({style:{
                textAlign:'left',
            }},()=>{
    
                t.p(reply.message);
            }),
           
        });

        return false;
    }
    
};

window.$ui.blockUI = ()=>{

}

window.$ui.unblockUI = () =>{
    
}



window.$ui.drawerModalBackground = document.querySelector('.drawer_modal_background');
window.$ui.drawerModal           = document.querySelector('.drawer_modal');
window.$ui.drawerModalBody       = document.querySelector('.drawer_modal_body');
window.$ui.drawerModalTitle      = document.querySelector('.drawer_modal_title');


/** Drawer Modal */
if(window.$ui.drawerModalBackground && window.$ui.drawerModal && window.$ui.drawerModalBody && window.$ui.drawerModalTitle ){

    window.$drawerModal = {
        isOpen: false,
        open:function(){
            window.$ui.drawerModalBackground.classList.add('drawer_modal_open');
            window.$ui.drawerModal.classList.add('drawer_modal_open');
            this.isOpen = true;
            return this;
        },
        close:function(){
            window.$ui.drawerModalBackground.classList.remove('drawer_modal_open');
            window.$ui.drawerModal.classList.remove('drawer_modal_open');
            this.isOpen = false;
            window.$ui.drawerModalBody.innerHTML = '';
            return this;
        },
        content:function(title,elem){
            window.$ui.drawerModalBody.innerHTML  = '';
            window.$ui.drawerModalTitle.innerHTML = title;
            window.$ui.drawerModalBody.appendChild(elem);
            return this;
        }
    };

    window.$ui.drawerModalBackground.onclick = ()=>{
        window.$drawerModal.close();
    }
}

window.$ui.confirm = (msg) =>{
    return Swal.fire({
        title: "Are you sure?",
        text: msg,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes"
    });
}

window.$url = (url)=>{

    window.location.href = url;
}

window.$reload = (url)=>{

    window.location.reload();
}


//Math

window.$roundUp = function(num,decimalPlaces = 0){
   
    if (num < 0)
        return -round(-num, decimalPlaces);
    var p = Math.pow(10, decimalPlaces);
    var n = num * p;
    var f = n - Math.floor(n);
    var e = Number.EPSILON * n;

    // Determine whether this fraction is a midpoint value.
    return (f >= .5 - e) ? Math.ceil(n) / p : Math.floor(n) / p;
}


window.$numberFormat = function(val,fractionDigits){

    if(!fractionDigits){
        fractionDigits = 2;
    }

    return (new Intl.NumberFormat('en-US', {
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits
    })).format(val);
   
}

window.$pureNumber = function(val,fractionDigits = null){

    val = String(val).replace(/[^\d.-]/g,'');

    val = parseFloat(val);

    if( isNaN(val) ){
        val = 0.0;
    }
    
    if(fractionDigits != null){
        val = window.$roundUp(val,fractionDigits);
    }

    return val;
}

window.$pureDecimal = function(val,precision){

    val = window.$pureNumber(val,precision);

    val = val + '';

    let arr = val.split('.');

    if(typeof arr[1] == 'undefined'){
        
        val = val + '.';

        for(let i = 1; i <= precision; i++){
            val = val + '0';
        }

    }else{

        let limit = precision - arr[1].length; 

        for(let k = 1; k <= limit; k++){
            val = val + '0';
        }
    }

    return val;

}


window.$numbersOnlyInput = function(arr,options){

    if(!Array.isArray(arr)){
        arr = [arr];
    }

    let negativeFlag    = options.negative ?? false;
    let decimalPlaces   = options.precision ?? 0;

    arr.map(el => {

        el.addEventListener('keypress',(evt)=>{
            
            let charCode = (evt.which) ? evt.which : evt.keyCode;
            

             //do not allow negative sign at the start
            if(negativeFlag && charCode == 45){


                if (el.value.indexOf('-') === -1 && el.value == '') {        
                    return true;
                } else {
                    evt.preventDefault();
                    return false;
                }
            }


            //point
            if (charCode == 46) {
                

                //Check if the text already contains the . character
                if (el.value.indexOf('.') === -1 && decimalPlaces != 0) {
                    return true;
                } else {
                    evt.preventDefault();
                    return false;
                }

            }else if (charCode > 31 && (charCode < 48 || charCode > 57)){
                
                evt.preventDefault();
                return false;    
            }


             //if one is true then it's good
            if(decimalPlaces){
                
                if(el.value == '-') return true;
                
                let r = "^-?\\d+\\.\\d{0,"+(decimalPlaces)+"}$";
                
                
                let a = (new RegExp(r,'gi')).test(el.value);

                let b = /^-?\d+$/.test(el.value);
                let c = /^-?\d+\.$/.test(el.value);

                if(!a && !b && !c && el.value != ''){

                    evt.preventDefault();
                    return false;
                }
            }
            
            return true;

        }); //keypress


         el.addEventListener('keyup',(evt)=>{
            
            if(decimalPlaces){
                  
                let r = "^-?\\d+\\.\\d{0,"+(decimalPlaces)+"}$";
                let a = (new RegExp(r,'gi')).test(el.value);
                let b = /^-?\d+$/.test(el.value);


                if(!a && !b && el.value != ''){
                    el.value = el.value.slice(0, -1); 
                }
            }

         });


         el.addEventListener('blur',()=>{
            el.value = window.$pureNumber(el.value);
         });

         el.addEventListener('paste',(evt)=>{
            setTimeout(()=>{
                let val = window.$pureNumber(el.value);

                if(decimalPlaces){
                    let r = "^-?\\d+\\.\\d{0,"+(decimalPlaces)+"}$";
                    let a = (new RegExp(r,'gi')).test(val);
                    let b = /^-?\d+$/.test(val);
                    
                    if(!a && !b){
                        val = 0;
                    }
                }

                el.value = val;
            },0);
         });

    });
}



window.$isValidDate = function(dateString){

    // First check for the pattern
    let regex_date = /^\d{4}\-\d{2}\-\d{2}$/;

    if(!regex_date.test(dateString) ){
        return false;
    }

    // Parse the date parts to integers
    let parts   = dateString.split("-");
    let day     = parseInt(parts[2], 10);
    let month   = parseInt(parts[1], 10);
    let year    = parseInt(parts[0], 10);

    // Check the ranges of month and year
    if(year < 1000 || year > 3000 || month == 0 || month > 12)
    {
        return false;
    }

    var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

    // Adjust for leap years
    if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
    {
        monthLength[1] = 29;
    }

    // Check the range of the day
    return day > 0 && day <= monthLength[month - 1];
}

window.$dateOnlyInput = function(arr){

    if(!Array.isArray(arr)){
        arr = [arr];
    }

    arr.map((elem)=>{

        elem.onkeyup = (e) =>{
            
            let val = elem.value;

            if(val.length == 7){

                val = val+'-01';
                
                if(!window.$isValidDate(val)){
                    
                    let arr_a = val.split('-');
    
                    elem.value = arr_a[0];
                }

            }else if(val.length >= 10){
                

                if(!window.$isValidDate(val)){
                    
                    let arr_b = val.split('-');

                    elem.value = arr_b[0]+'-'+arr_b[1];
                }
            }
        }

        elem.onkeydown = (e)=>{
            
            let val = elem.value;


            if(val == '' || e.keyCode == 8){
                return true;
            }

            if(val.length >= 10 || e.keyCode == 173){
                return false;
            }

            if(/^[0-9]{1,4}$/.test(val)){

                if(val.length == 4){
                    elem.value = val+'-';
                }
                
                return true;
            }


            if(/^[0-9]{4}-$/.test(val)){

                return true;
            }


            if(/^[0-9]{4}-[0-9]{1,2}$/.test(val)){

                if(val.length == 7){
                    elem.value = val+'-';
                }

                return true;
            }

            if(/^[0-9]{4}-[0-9]{2}-$/.test(val)){

                return true;
            }


            if(/^[0-9]{4}-[0-9]{2}-[0-9]{1,2}$/.test(val)){

                return true;
            }


            if(/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(val)){

                return true;
            }

            
            return false;
        }

        elem.onpaste = ()=>{

            setTimeout(()=>{
                
                if(!window.$isValidDate(elem.value)){
                    elem.value = '';
                }

            },0);
        }

        elem.onblur = ()=>{
            
            if(!window.$isValidDate(elem.value)){
                elem.value = '';
            }
        }
    });
};