
import { createApp } from 'vue'

// Import all the block SFCs here
import PageList from '../blocks/PageList.vue';

// This code creates a Vue instance for each custom block on a given page
const vueBlocks = document.querySelectorAll('.vue3-block');
for(let i=0; i<vueBlocks.length; i++){

    const tmpApp = createApp({})

    switch(vueBlocks[i].className){
        case 'vue3-block page-list':
            tmpApp.component('PageList', PageList)
            break;
    }
    
    tmpApp.mount(vueBlocks[i])
}
