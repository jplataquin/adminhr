import {Template} from 'adarna';



window.$t = {};

const t = new Template();

$t.label = (text)=>{
    return t.label({class:"text-sm font-medium text-white block mb-2"},text);
}

$t.text_input = (val = '')=>{
    
    return t.input({
        value:val,
        class:'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full p-2.5'
    });    
}

$t.textarea = (val = '') =>{
    return t.textarea({class:"border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full p-2.5"},val);

}


$t.button = (text = '')=>{
    return t.button({class:'text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center'},text);
}

$t.select = (obj,val = null) =>{

    return t.select({class:'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full p-2.5'},(el)=>{

        for(let key in obj){

            let option = t.option({value:key},obj[key]);

            if(val == key){
                option.selected = true;
            }

            el.append(option);
        }
    });
}

