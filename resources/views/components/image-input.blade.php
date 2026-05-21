@props([
    'name'          => '',
    'value'         => '',
   
    'displayWidth'  => '300',
    'displayHeight' => '300',
    'disabled'      => 'false'
])

<div id="image_upload_card_{{$name}}" disabled="{{$disabled}}"  {{ $attributes->merge(['class' => 'row g-4 p-4']) }}>
    <div id="canvas_container_{{$name}}" class="col-lg-6">
        <div class="d-flex justify-content-center">
            <canvas class="border shadow-sm bg-white" id="canvas_{{$name}}" width="{{$displayWidth}}px" height="{{$displayHeight}}px"></canvas>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <input class="form-range" type="range" min="1" max="200" value="1" id="zoom_{{$name}}">
        </div>
    </div>

    <div id="image_upload_controls_{{$name}}" class="col-lg-6">
        <div class="mb-3">
            <input type="hidden" name="{{$name}}" value="{{$value}}" id="data_{{$name}}"/>
            <input type="file" id="input_{{$name}}" accept="image/jpeg;capture=enviroment" class="form-control form-control-sm"/>
        </div>
        <div class="d-flex justify-content-around mt-4">
            <button id="clear_data_{{$name}}" class="btn btn-secondary w-25">Clear</button>
            <button id="upload_data_{{$name}}" class="btn btn-primary w-25">Upload</button>
        </div>
    </div>
</div>

<script type="module">
    const container     = document.querySelector('div[id="image_upload_card_{{$name}}"]');
    const input         = container.querySelector('input[id="input_{{$name}}"]');
    const data          = container.querySelector('input[id="data_{{$name}}"]');
    const canvas        = container.querySelector('canvas[id="canvas_{{$name}}"]');
    const div           = container.querySelector('div[id="canvas_container_{{$name}}"]');
    const range         = container.querySelector('input[id="zoom_{{$name}}"]');
    const uploadBtn     = container.querySelector('button[id="upload_data_{{$name}}"]');
    const clearBtn      = container.querySelector('button[id="clear_data_{{$name}}"]');
    const controls      = container.querySelector('div[id="image_upload_controls_{{$name}}"]');

    const original_file = '{{$value}}';    
    const name          = '{{$name}}';

    let img           = document.createElement('img');
    const ctx           = canvas.getContext('2d');

    let og_width = 0;
    let og_height = 0;
    
    ctx.imageSmoothingEnabled       = true; // Standard property
 
    let disabled = {{$disabled}};

    //Set display width/height
    canvas.style.width  = '{{$displayWidth}}px';
    canvas.style.height = '{{$displayHeight}}px';

    //Set resolition width/height
    canvas.width        = {{$displayWidth}};
    canvas.height       = {{$displayHeight}};
    
    //ctx.scale(dpr, dpr);

    let currentX    = 0;
    let currentY    = 0;
    let ratio       = range.value * 0.01;
    let dragFlag    = false;
    let curXpos     = 0;
    let curYpos     = 0;


    let Mode = {
        value:'',
        dry:{
            empty:()=>{
                canvas.style.border = 'none';
                uploadBtn.disabled  = false;
                clearBtn.disabled   = false;
                input.value         = '';
                canvas.classList.remove('shadow-loading');
            }
        },
        uploadSuccess: ()=>{
            
            Mode.value  = 'uploaded_success';

            canvas.style.border = 'solid 3px #008000';

            canvas.classList.remove('shadow-loading');
            

            input.value = '';

            uploadBtn.disabled = false;
            clearBtn.disabled  = false;
        },
        uploading: ()=>{

            Mode.value = 'uploaing';
            
            canvas.style.border = 'solid 3px rgb(210, 217, 10)';
            canvas.classList.add('shadow-loading');

            uploadBtn.disabled = true;
            clearBtn.disabled  = true;

        },
        imageChange:()=>{
            Mode.value = 'image_change';
            canvas.style.border = 'solid 3px rgb(210, 217, 10)';
            canvas.classList.remove('shadow-loading');
        },
        clearedImage:()=>{
            Mode.value = 'cleared_image';
            Mode.dry.empty();
            canvas.style.border = 'solid 3px rgb(210, 217, 10)';
            canvas.classList.remove('shadow-loading');
            
        },
        noImage:()=>{
            Mode.value = 'no_image';
            Mode.dry.empty();
        },
        enabled:()=>{
            Mode.value = 'enabled';
            disabled    = false;
            controls.classList.remove('d-none');
            range.classList.remove('d-none');
            div.classList.remove('col-lg-12');
            div.classList.add('col-lg-6');
        },
        disabled:()=>{
            Mode.value  = 'disabled';
            disabled    = true;
            controls.classList.add('d-none');
            range.classList.add('d-none');
            div.classList.remove('col-lg-6');
            div.classList.add('col-lg-12');
        },
        default: ()=>{

            disabled = {{$disabled}};

            //Set display width/height
            canvas.style.width  = '{{$displayWidth}}px';
            canvas.style.height = '{{$displayHeight}}px';

            //Set resolition width/height
            canvas.width        = {{$displayWidth}};
            canvas.height       = {{$displayHeight}};
            
            
            currentX    = 0;
            currentY    = 0;
            ratio       = range.value * 0.01;
            dragFlag    = false;
            curXpos     = 0;
            curYpos     = 0;

            clearCanvas();

            //asset('storage/employee/photos/'.$value)
            if(original_file == ''){
                Mode.dry.empty();
            }else{
                img.onload = ()=>{
                    let hRatio = canvas.width / img.width;
                    let vRatio = canvas.height / img.height;
                    
                    ratio = Math.min(hRatio, vRatio);

                    let min = ratio * 100;

                    range.setAttribute('min', min);
                    range.value = min;

                    currentX = 0;
                    currentY = 0;

                    clearCanvas()

                    drawImg();
                    Mode.uploadSuccess();
                }
                
                img.src = "{{asset('storage/employee/photos/'.$value)}}";
            }
        }
    }

    

    function clearCanvas(){
        ctx.fillStyle = "#FFFFFF";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    async function drawImg(){

        
        let resizeWidth;
        let resizeHeight;

        
        if(og_width == 0 || og_height == 0){
          
            og_width        = img.width;
            og_height       = img.height;
            resizeWidth     = img.width * ratio;
            resizeHeight    = img.height * ratio;
        }else{
          
            resizeWidth     = og_width * ratio;
            resizeHeight    = og_height * ratio;
        }
        
        img.width  = resizeWidth;
        img.height = resizeHeight;


        ctx.drawImage(
            img, 
            (currentX * -1), 
            (currentY * -1), 
             img.width, 
             img.height, 
            // (currentX * -1), 
            // (currentY * -1),
            // img.width * ratio, 
            // img.height * ratio
        );
    }

    async function getBlob(){
        return new Promise((resolve,reject)=>{


            canvas.toBlob(function(blob) {        // get content as JPEG blob
                const file = new File( [ blob ], "mycanvas.jpeg" );
                resolve(file);
            }, "image/jpeg", 1);
            
        });
    }


    Mode.default();

    range.oninput = (e)=>{

        ratio = range.value * 0.01;
        
        clearCanvas();

        drawImg();
       
    }

    input.oninput = (evt)=>{

        let files = evt.target.files; // FileList object

        if(!files.length){
            return false;
        }

	    let file = files[0];

        if(file.type.match('image.*')) {
	        let reader = new FileReader();
	        // Read in the image file as a data URL.
	        reader.readAsDataURL(file);
	    	reader.onload = function(evt){
	    		if( evt.target.readyState == FileReader.DONE) {
	    			
                    let n_img = document.createElement('img');
                    n_img.onload = ()=>{
                        
                        

                        let hRatio = canvas.width / n_img.width;
                        let vRatio = canvas.height / n_img.height;
                        
                        og_width   = 0;
                        og_height  = 0;
                        

                        ratio = Math.min(hRatio, vRatio);


                        let min = ratio * 100;

                        range.setAttribute('min', min);
                        range.value = min;

                        currentX = 0;
                        currentY = 0;
                        img = n_img;
                        clearCanvas()

                        drawImg();

                        Mode.imageChange();
                    }
                    

                    
                    n_img.src = evt.target.result;

                }
	    	}    

	    } else {
	        alert("not an image");
	    }
    }

    canvas.ontouchstart = function(e) {

        e.preventDefault();
        

        if(disabled) return false;

        dragFlag    = true;
        curXpos     = e.touches[0].clientX;
        curYpos     = e.touches[0].clientY;
    
    };

    canvas.ontouchmove = function(e) {
        
        e.preventDefault();

        if(disabled) return  false;
        
        if (dragFlag) {
        
            let x = e.touches[0].clientX;
            let y = e.touches[0].clientY;
            

            if(curXpos > x){
                //increase
                currentX = currentX + (curXpos - x);
                
            }else{
                //decrease
                currentX = currentX - (x - curXpos);
            }

            if(curYpos > y){
                //increase
                currentY = currentY + (curYpos - y);
                
            }else if(curYpos < y){
                //decrease
                currentY = currentY - (y - curYpos);
            }

            curXpos = x;
            curYpos = y;

            clearCanvas()
            drawImg();
            Mode.imageChange();
        }

    };

    canvas.ontouchend = function(e) {

        e.preventDefault();
        
        
        if(disabled) return false;
        
        dragFlag = false;
    };

    canvas.onmousedown = function(e) {

        if(disabled) return false;

        dragFlag    = true;
        curXpos     = e.pageX;
        curYpos     = e.pageY;
    
    };
    
    canvas.onmousemove = function(e) {
        
        if(disabled) return  false;
        
        if (dragFlag) {
        
            let x = e.pageX;
            let y = e.pageY;
            

            if(curXpos > x){
                //increase
                currentX = currentX + (curXpos - x);
                
            }else{
                //decrease
                currentX = currentX - (x - curXpos);
            }

            if(curYpos > y){
                //increase
                currentY = currentY + (curYpos - y);
                
            }else if(curYpos < y){
                //decrease
                currentY = currentY - (y - curYpos);
            }

            curXpos = x;
            curYpos = y;

            clearCanvas()
            drawImg();
            Mode.imageChange();
        }

    };

    canvas.onmouseup = function(e) {

        if(disabled) return false;
        
        dragFlag = false;
    };
    
    canvas.onmouseout = function(e) {

        if(disabled) return false;
        
        dragFlag = false;
    };

    uploadBtn.onclick = async (e)=>{
        
        e.preventDefault();

        let blob = await getBlob();

        Mode.uploading();

        window.$_FILE('/api/upload',{
            file:blob
        }).then(reply=>{
            
            if(reply.status <= 0){
                $ui.showError(reply);
                return false;
            }
        
            data.value = reply.data.file;

            Mode.uploadSuccess();

        });
    }


    clearBtn.onclick = (e)=>{
        e.preventDefault();
        
        if(original_file != ''){
            clearCanvas();
            Mode.clearedImage();
            
        }
    }


    $attrChange(container,'disabled',(val)=>{

        if(val == 'true'){
            
            Mode.default();
            Mode.disabled();

        }else{

            Mode.default();
            Mode.enabled();
        }
    });
    
</script>
