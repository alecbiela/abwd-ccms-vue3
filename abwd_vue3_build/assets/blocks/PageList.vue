<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        title: String,
        titleFormat: String,
        rssUrl: String,
        includeEntryText: Boolean,
        includeName: Boolean,
        includeDate: Boolean,
        includeDescription: Boolean,
        useButtonForLink: Boolean,
        noResultsMessage: String,
        emptyBlockMessage: String,
        displayEmptyMessage: Boolean,
        pages: String,
        showPagination: Boolean,
        buttonLinkText: String
    })

    const pages = computed(()=>{return JSON.parse(props.pages)})
    const pageListTitle = computed(() => {
        return '<'+props.titleFormat+'>'+props.title+'</'+props.titleFormat+'>'
    })

</script>
<template>
    <div v-if="props.displayEmptyMessage" class="ccm-edit-mode-disabled-item" v-text="emptyBlockMessage"></div>

    <template v-else>
        <div v-if="pages.length > 0" class="ccm-block-page-list-wrapper">
            <div v-if="props.title" class="ccm-block-page-list-header" v-html="pageListTitle"></div>
            <a v-if="props.rssUrl" :href="props.rssUrl" target="_blank" class="ccm-block-page-list-rss-feed">
                <i class="fas fa-rss"></i>
            </a>
            <div class="ccm-block-page-list-pages">
                <template v-for="page in pages">
                    <div :class="page.entry_classes">
                        <div class="ccm-block-page-list-page-entry-thumbnail" v-if="(page.thumbnail != false)" v-html="page.thumbnail">
                        </div>
                        <template v-if="props.includeEntryText">
                            <div class="ccm-block-page-list-page-entry-text">
                                <div class="ccm-block-page-list-title" v-if="props.includeName">
                                    <a v-if="!props.useButtonForLink" :href="page.url" :target="page.target">{{ page.title }}</a>
                                    <template v-else>{{ page.title }}</template>
                                </div>
                                <div class="ccm-block-page-list-date" v-if="props.includeDate" v-html="page.date"></div>
                                <div class="ccm-block-page-list-description" v-if="props.includeDescription" v-html="page.description"></div>
                                <div v-if="props.useButtonForLink" class="ccm-block-page-list-page-entry-read-more">
                                    <a :href="page.url" :target="page.target"
                                        class="ccm-block-page-list-read-more">{{ props.buttonLinkText }}</a>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <div v-else class="ccm-block-page-list-no-pages" v-text="props.noResultsMessage"></div>

        <template v-if="props.showPagination">
            The pagination wrapper will go here.
        </template>
    </template>

</template>