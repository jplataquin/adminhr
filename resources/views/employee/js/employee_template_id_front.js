
function uppercaseFirstLetter(val) {
    return String(val).charAt(0).toUpperCase() + String(val).slice(1);
}

function middlenameToInitial(val){
    return String(val).charAt(0).toUpperCase()+'.';
}

export default function(root,elem){


    let firstname   = elem.firstname.value;
    let middlename  = elem.middlename.value;
    let lastname    = elem.lastname.value;
    let suffix      = elem.suffix.value;
    let employee_id = elem.employee_id.value;
    let position    = elem.position.value;
    let test = elem.test;
    
    let name    = uppercaseFirstLetter(firstname)+' '+middlenameToInitial(middlename)+' '+uppercaseFirstLetter(lastname)+' '+uppercaseFirstLetter(suffix);
    name        = name.trim();

    const canvas = elem.canvas;
    
    canvas.width    = 1276 * 0.55;
    canvas.height   = 2032 * 0.55;

    canvas.style.width  = `${canvas.width}px`;
    canvas.style.height = `${canvas.height}px`;
    

    // const dpi = window.devicePixelRatio;
    
    // canvas.setAttribute('width', 300 * dpi);
    // canvas.setAttribute('height', 477 * dpi);

    const ctx    = canvas.getContext('2d');

    /***
    
    // Get the DPR and size of the canvas
        const dpr = window.devicePixelRatio;
        const rect = canvas.getBoundingClientRect();

        // Set the "actual" size of the canvas
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;

        // Scale the context to ensure correct drawing operations
        ctx.scale(dpr, dpr);

        // Set the "drawn" size of the canvas
        canvas.style.width = `${rect.width}px`;
        canvas.style.height = `${rect.height}px`;
**/
//    ctx.imageSmoothingEnabled = true;

    const frontImg = new Image();
    const photoImg = document.createElement('img');
    const qrCodeImg = new Image();
    
    let load_count = 0;

    // frontImg.width = rect.width;
    
    // frontImg.width = rect.height;

    frontImg.onload = ()=>{
        load_count++;
        // set size proportional to image
        //canvas.height 
        let pheight = canvas.width * (frontImg.height / frontImg.width);
        ctx.drawImage(frontImg, 0, 0,canvas.width,pheight); 

        ctx.fillStyle       = 'blue'; // Set fill color for the text
        ctx.font            = "16px Arial";
        let name_metrics    = ctx.measureText(name);
        
        
        let name_x = (canvas.width / 2) - (name_metrics.width / 2);

        ctx.fillText(name,name_x,210);


        ctx.fillStyle           = 'grey'; // Set fill color for the text
        ctx.font                = "14px Arial";
        let position_metrics    = ctx.measureText(position);

        let position_x = (canvas.width / 2) - (position_metrics.width / 2);

        ctx.fillText(position,position_x,225);


        
        photoImg.src    = elem.photo.value;

        qrCodeImg.src   = encodeURI('/generate-qrcode?d='+$base_url+'/public/employee/'+employee_id);

    }

    photoImg.onload = ()=>{
        load_count++;


        ctx.drawImage(photoImg, 43, 60,photoImg.width,photoImg.height);
    }


    qrCodeImg.onload = ()=>{
       
        let qr_x = (canvas.width / 2) - (120 / 2);
        ctx.drawImage(qrCodeImg,qr_x,253,120,120);

        ctx.fillStyle           = 'black'; // Set fill color for the text
        ctx.font                = "14px Arial";

        let id_text                 = String(employee_id).padStart(4, '0');
        let employee_id_metrics     = ctx.measureText(id_text);

        let employee_id_x = (canvas.width / 2) - (employee_id_metrics.width / 2);
        ctx.fillText(id_text,employee_id_x,242);
    }


    
    frontImg.src    = '/employee/id_template/front';
  
    

}