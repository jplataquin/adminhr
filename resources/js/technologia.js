

function getAllElementVariable(elem,elements = {}){
    
    
    const direct_child = Array.from(elem.children);
    
    direct_child.map(child => { if(!child.getAttribute('data-controller')){
            
            let data_el = child.getAttribute('data-el');

            if(data_el){
                elements[data_el] = child;
            }

            if(child.children){
                elements = getAllElementVariable(child,elements);
            }
    
    }});


    return elements;
}


function init(elem,d = {}){

    const elem_with_controllers = Array.from( elem.querySelectorAll('[data-controller]') );

    for(let i = 0; i <= elem_with_controllers.length - 1; i++){
        
        const target = elem_with_controllers[i];

        
        if(target.__flag_controlled) continue;

        let controller_name = target.getAttribute('data-controller');

        
        const sub_elements = getAllElementVariable(target);

        
        target.__flag_controlled = true;

        if(typeof d[controller_name] == 'undefined'){

            import(controller_name).then((mod)=>{
                
                mod.default(target,sub_elements);


            }).catch(err=>{
                console.error(err);
                target.__flag_controlled = false;
            });
           
        }else{

            if(typeof d[controller_name] == 'function'){
                d[controller_name](target,sub_elements);
            }else{
                console.error(controller_name+' is not a function',d[controller_name]);
                target.__flag_controlled = false;
            }

        }

        
    }
}

class DomFilter {
  constructor(dom = document.createElement('div')) {
    this.dom = dom;
    this.results = [];
  }

  filterOrFail(query){

    let results = Array.from( this.dom.querySelectorAll(query) );
        
    if(!results){
        return false;
    }

    this.results = results;

    let div = document.createElement('div');

    results.map(el => {
        div.append(el);
    });

    
    this.dom = div;

    return this;
  }

  filter(query,_fail = ()=>{}) {
    
    let results = Array.from( this.dom.querySelectorAll(query) );
    
    
    this.results = results;

    if(!results){
        _fail(this.dom);
        return this;
    }

    let div = document.createElement('div');

    results.map(el => {
        div.append(el);
    });

    
    this.dom = div;

    return this;
  }

  all() {
    return this.results;
  }

  first() {

    if(!this.results) return null;

    return this.results[0];
  }

  last() {
    if(!this.results) return null;
    return this.results[this.results.length - 1];
  }

  nth(i) {
     if(!this.results) return null;

    if(!this.results[i]) return null;
    
    return this.results[i];
  }

  apply(callback){

    this.results.map(item=>{
        callback(item);
    });
    
  }
}

const Technologia = {

    init: (elem,d = {}) => {
        
        init(elem,d);

        return {
            observe:()=>{
                const observer = new MutationObserver( (mutationsList, observer) => {
                    for (const mutation of mutationsList) {
                        // Process each mutation
                        if (mutation.type === 'childList') {
                            
                            init(elem,d);
                        } 
                    }
                });

                observer.observe(elem,{
                    childList: true,
                    subtree: true,
                    attributes: false,
                    characterData: false
                });

                return observer;
            }
        }
    },

    dom:(dom)=>{

        return new DomFilter(dom);

    }   
}


export default Technologia;