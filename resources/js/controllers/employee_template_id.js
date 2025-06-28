export default function(root,elem){

    const canvas = elem.canvas;
   // const width  = 100;
   // const height = 100;
    const ctx    = canvas.getContext('2d');

    ctx.imageSmoothingEnabled = true;
    // ctx.strokeStyle = "purple";
    // ctx.lineWidth = 3;
    // //ctx.strokeRect(50.5, 50.5, 20, 20);
    // ctx.rect(50.5, 50.5, 150, 80);
    // //ctx.strokeRect(100, 50, 20, 20);


    ctx.beginPath();
    ctx.lineWidth = "10";
    ctx.strokeStyle = "blue";
    ctx.rect(50, 50, 150, 80);
    ctx.stroke();
}