
import { createApp } from 'vue'

// Import all the block SFCs here
import ContentBlock from '../blocks/ContentBlock.vue'
import HorizontalRule from '../blocks/HorizontalRule.vue'

// This code creates a Vue instance for each custom block on a given page
const vueBlocks = document.querySelectorAll('.vue3-block');
for(let i=0; i<vueBlocks.length; i++){

    const tmpApp = createApp({})

    switch(vueBlocks[i].className){
        case 'vue3-block content':
            tmpApp.component('ContentBlock', ContentBlock)
            break;
        case 'vue3-block horizontal-rule':
            tmpApp.component('HorizontalRule', HorizontalRule)
            break;
    }
    
    tmpApp.mount(vueBlocks[i])
}
