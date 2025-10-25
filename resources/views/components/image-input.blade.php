@props([
    'name'          => '',
    'value'         => '',
   
    'displayWidth'  => '300',
    'displayHeight' => '300',
    'disabled'      => 'false'
])

<div id="image_upload_card_{{$name}}" disabled="{{$disabled}}"  {{ $attributes->merge(['class' => 'grid sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-2 gap-4 p-4']) }}>
    <div id="canvas_container_{{$name}}" class="grid grid-cols-1">
        <div class="flex justify-center">
            <canvas class="" id="canvas_{{$name}}" width="{{$displayWidth}}px" height="{{$displayHeight}}px"></canvas>
        </div>
        <div class="flex justify-center mt-3">
            <input class="" type="range" min="1" max="200" value="1" id="zoom_{{$name}}">
        </div>
    </div>

    <div id="image_upload_controls_{{$name}}">
        <div>
            <input type="hidden" name="{{$name}}" value="{{$value}}" id="data_{{$name}}"/>
            <input type="file" id="input_{{$name}}" accept="image/jpeg;capture=enviroment" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"/>
        </div>
        <div class="text-center mt-6">
            <button id="clear_data_{{$name}}" class="w-[30%] text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm py-2.5 text-center">Clear</button>
            <button id="upload_data_{{$name}}" class="w-[30%] text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm py-2.5 text-center">Upload</button>
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

    const img           = document.createElement('img');
    const ctx           = canvas.getContext('2d');
    
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
            controls.classList.remove('hidden');
            range.classList.remove('hidden');
            container.classList.add('lg:grid-cols-2');
        },
        disabled:()=>{
            Mode.value  = 'disabled';
            disabled    = true;
            controls.classList.add('hidden');
            range.classList.add('hidden');
            container.classList.remove('lg:grid-cols-2');
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
                
                container.classList.add('lg:grid-cols-2');
                
                img.src = "{{asset('storage/employee/photos/'.$value)}}";
            }
        }
    }

    

    function clearCanvas(){
        ctx.fillStyle = "#FFFFFF";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    async function drawImg(){

        let resizeWidth = img.width * ratio;
        let resizeHeight = img.height * ratio;

        let bitmap = await createImageBitmap(img, {
            resizeWidth: resizeWidth, 
            resizeHeight: resizeHeight,
            resizeQuality: 'pixelated'
        });

        ctx.drawImage(
            bitmap, 
            (currentX * -1), 
            (currentY * -1) 
            // img.width, 
            // img.height, 
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

                        Mode.imageChange();
                    }
                    

                    img.src = evt.target.result;

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