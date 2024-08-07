<script setup>
    import { computed, onMounted, ref } from 'vue';

    const props = defineProps({
        blockId: String,
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
        showPagination: Boolean,
        buttonLinkText: String
    })

    const pages = ref(null)
    const pageListTitle = computed(() => {
        return '<'+props.titleFormat+'>'+props.title+'</'+props.titleFormat+'>'
    })

    onMounted(() => {
        pages.value = null
        fetch('/api/page_list/'+props.blockId)
        .then((res) => (res.json()))
        .then((data) => (pages.value = data.results))
        .catch((error) => (console.error(error.message)))
    })

</script>
<template>
    <div v-if="props.displayEmptyMessage" class="ccm-edit-mode-disabled-item" v-text="emptyBlockMessage"></div>

    <template v-else>
        <div v-if="pages && pages.length > 0" class="ccm-block-page-list-wrapper">
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