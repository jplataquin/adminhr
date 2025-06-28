import Technologia from '/technologia.js';
import {Template,$q} from '/adarna.js';



export default function(root){


    let tabs = $q('c-tab',root).items();

    let tab_arr           = [];
    let default_tab       = '';

    let active_class      = 'cursor-pointer inline-block p-4 text-blue-600 bg-gray-100 rounded-t-lg active dark:bg-gray-800 dark:text-blue-500';
    let inactive_class    = 'cursor-pointer inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300';

    let tab_scope = root.getAttribute('tab-scope');

    tabs.map(tab => {

        if(tab.hasAttribute('default')){
            default_tab = tab.innerText;
        }

        tab_arr.push({
            name: tab.innerText,
            target: tab.getAttribute('target')
        });

        tab.remove();
    });

    const t = new Template();

    const ul = t.ul({
        class:'flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400'
    });


    tab_arr.map(tab_item => {

        const tab_el = t.li({
            class:'me-2'
        },()=>{

            let a = t.a({
                tab_name:tab_item.name,
                class: inactive_class
            },tab_item.name);

            if(default_tab == tab_item.name){
                a.className = active_class;
            }

            a.onclick = (e)=>{
                
                e.preventDefault();

                $q('a',ul).apply(item=>{
                    item.className = inactive_class;
                });

                a.className = active_class;
                
                $q(tab_scope).apply(target=>{
                    target.classList.add('hidden');
                });

                $q(tab_item.target).apply(target=>{
                    target.classList.remove('hidden');
                });
            }
        });

        ul.appendChild(tab_el);
    });


    root.appendChild(ul);
}