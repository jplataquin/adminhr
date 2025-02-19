import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';



window.axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').getAttribute('content')
};


const abort_controller = new AbortController();

window.$_POST = (url,data) =>{

    return new Promise((resolve,reject)=>{
        
        axios.post(url, data,{
            signal:abort_controller.signal
        }).then(reply=>{

            resolve(reply.data);

        }).catch(err=>{

            reject(err);

            if(401 == err.status){

                abort_controller.abort();
                
                Swal.fire({
                    icon: "error",
                    title: "Unauthenticated",
                    text: "Please login, and try again.",
                });
                
                return false;
            }

            if(404 == err.status){

                abort_controller.abort();
                
                Swal.fire({
                    icon: "error",
                    title: "Resource Not Found",
                    text: "Something went wrong",
                });
                
                return false;
            }


            if(500 == err.status){
                
                Swal.fire({
                    icon: "error",
                    title: "Server Error",
                    text: "Something went wrong.",
                });
                
                return false;
            }


        });
    
    });
}


window.$_GET = (url,data) =>{

    return new Promise((resolve,reject)=>{
        
        axios.get(url, {params:data},{
            signal:abort_controller.signal
        }).then(reply=>{

            resolve(reply.data);

        }).catch(err=>{

            reject(err);

            if(401 == err.status){

                abort_controller.abort();
                
                Swal.fire({
                    icon: "error",
                    title: "Unauthenticated",
                    text: "Please login, and try again.",
                });
                
                return false;
            }


            if(404 == err.status){

                abort_controller.abort();
                
                Swal.fire({
                    icon: "error",
                    title: "Resource Not Found",
                    text: "Something went wrong",
                });
                
                return false;
            }


            if(500 == err.status){
                
                Swal.fire({
                    icon: "error",
                    title: "Server Error",
                    text: "Something went wrong.",
                });
                
                return false;
            }


        });
    
    });
}