import {Template, $q} from '/adarna.js';

export default function(root){
    let tabs = $q('c-tab', root).items();
    let tab_scope = root.getAttribute('tab-scope');
    let anchorElements = [];

    tabs.map(tab => {
        let isDefault = tab.hasAttribute('default');
        let targetSelector = tab.getAttribute('target');
        let classes = tab.getAttribute('class') || '';

        let a = document.createElement('a');
        a.href = '#';
        a.className = classes;
        a.innerHTML = tab.innerHTML;

        anchorElements.push(a);

        a.onclick = (e) => {
            e.preventDefault();

            anchorElements.forEach(item => {
                item.classList.remove('active');
            });

            a.classList.add('active');

            $q(tab_scope).apply(target => {
                target.classList.add('d-none');
            });

            $q(targetSelector).apply(target => {
                target.classList.remove('d-none');
            });
        };

        tab.parentNode.replaceChild(a, tab);

        // Synchronize initial state
        if (isDefault) {
            $q(targetSelector).apply(target => {
                target.classList.remove('d-none');
            });
        } else {
            $q(targetSelector).apply(target => {
                target.classList.add('d-none');
            });
        }
    });
}
