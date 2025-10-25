

export default function(root,elem){


    let tin         = elem.tin.value.trim() || 'n/a';
    let sss         = elem.sss.value.trim() || 'n/a';
    let pag_ibig    = elem.pag_ibig.value.trim() || 'n/a';
    

    const canvas = elem.canvas;

    canvas.width    = (300*2);
    canvas.height   = (477*2);

    canvas.style.width  = '300px';
    canvas.style.height = '477px';
    
    const dpi = window.devicePixelRatio;
  
    const ctx    = canvas.getContext('2d');
    
    ctx.scale(dpi, dpi);
    
    ctx.imageSmoothingEnabled = true;

    const backImg = new Image();

    backImg.onload = ()=>{
        // set size proportional to image
        let pheight = canvas.width * (backImg.height / backImg.width);
        ctx.drawImage(backImg, 0, 0,canvas.width,pheight); 


        ctx.fillStyle           = 'grey'; // Set fill color for the text
        ctx.font                = "14px Arial";

        tin = 'TIN: '+tin;
        let tin_metrics    = ctx.measureText(tin);
        let tin_x = (canvas.width / 2) - (tin_metrics.width / 2);
        ctx.fillText(tin,tin_x,300)

        sss = 'SSS: '+sss;
        let sss_metrics    = ctx.measureText(sss);
        let sss_x = (canvas.width / 2) - (sss_metrics.width / 2);
        ctx.fillText(sss,sss_x,320);

        pag_ibig = 'Pag-IBIG: '+pag_ibig;
        let pag_ibig_metrics    = ctx.measureText(pag_ibig);
        let pag_ibig_x = (canvas.width / 2) - (pag_ibig_metrics.width / 2);
        ctx.fillText(pag_ibig,pag_ibig_x,340);


        ctx.fillStyle           = 'Black'; // Set fill color for the text
        ctx.font                = "14px Arial";

        const today                 = new Date(); // Get the current date and time
        const currentYear           = today.getFullYear(); // Get the current year (e.g., 2025)
        const yearTwoYearsFromNow   = currentYear + 2;

        let expiry              = 'EXP: '+yearTwoYearsFromNow
        let expiry_metrics      = ctx.measureText(expiry);
        let expiry_x            = (canvas.width / 2) - (expiry_metrics.width / 2);
        ctx.fillText(expiry,expiry_x,380);
    };

    backImg.src    = '/employee/id_template/back';
    
  
    
}