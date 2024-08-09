<script setup>
import { computed, onMounted, ref } from 'vue'

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
  buttonLinkText: String,
})

const pages = ref(null)
const totalPages = ref(1)
const currentPage = ref(1)
const pageListTitle = computed(() => {
  return (
    '<' + props.titleFormat + '>' + props.title + '</' + props.titleFormat + '>'
  )
})

// Gets the list of pages from the API
// @param int|null pageNum - Page number of results (if they are paginated)
const getPages = (pageNum) => {
  fetch(`/api/page_list/${props.blockId}/${pageNum}`)
    .then((res) => res.json())
    .then((data) => {
      pages.value = data.results
      totalPages.value = data.total_pages
      currentPage.value = data.current_page
    })
    .catch((error) => console.error(error.message))
}
onMounted(() => {
  getPages(props.showPagination ? 1 : null)
})
</script>
<template>
  <div
    v-if="props.displayEmptyMessage"
    class="ccm-edit-mode-disabled-item"
    v-text="emptyBlockMessage"
  ></div>

  <template v-else>
    <div v-if="pages && pages.length > 0" class="ccm-block-page-list-wrapper">
      <div
        v-if="props.title"
        class="ccm-block-page-list-header"
        v-html="pageListTitle"
      ></div>
      <a
        v-if="props.rssUrl"
        :href="props.rssUrl"
        target="_blank"
        class="ccm-block-page-list-rss-feed"
      >
        <i class="fas fa-rss"></i>
      </a>
      <div class="ccm-block-page-list-pages">
        <template v-for="page in pages">
          <div :class="page.entry_classes">
            <div
              class="ccm-block-page-list-page-entry-thumbnail"
              v-if="page.thumbnail != false"
              v-html="page.thumbnail"
            ></div>
            <template v-if="props.includeEntryText">
              <div class="ccm-block-page-list-page-entry-text">
                <div class="ccm-block-page-list-title" v-if="props.includeName">
                  <a
                    v-if="!props.useButtonForLink"
                    :href="page.url"
                    :target="page.target"
                    >{{ page.title }}</a
                  >
                  <template v-else>{{ page.title }}</template>
                </div>
                <div
                  class="ccm-block-page-list-date"
                  v-if="props.includeDate"
                  v-html="page.date"
                ></div>
                <div
                  class="ccm-block-page-list-description"
                  v-if="props.includeDescription"
                  v-html="page.description"
                ></div>
                <div
                  v-if="props.useButtonForLink"
                  class="ccm-block-page-list-page-entry-read-more"
                >
                  <a
                    :href="page.url"
                    :target="page.target"
                    class="ccm-block-page-list-read-more"
                    >{{ props.buttonLinkText }}</a
                  >
                </div>
              </div>
            </template>
          </div>
        </template>
      </div>
    </div>

    <div
      v-else
      class="ccm-block-page-list-no-pages"
      v-text="props.noResultsMessage"
    ></div>

    <template v-if="props.showPagination">
      <div class="ccm-pagination-wrapper">
        <ul class="pagination">
          <li
            :class="{
              'page-item': true,
              prev: true,
              disabled: currentPage == 1,
            }"
          >
            <template v-if="currentPage == 1">
              <span class="page-link"
                ><i class="fa fa-arrow-left" aria-hidden="true"></i>
                Previous</span
              >
            </template>
            <template v-else>
              <button class="page-link" @click="getPages(currentPage - 1)">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Previous
              </button>
            </template>
          </li>
          <template v-for="n in totalPages">
            <li :class="{ 'page-item': true, active: n == currentPage }">
              <template v-if="n == currentPage">
                <span class="page-link"
                  >{{ n }}
                  <span class="sr" v-if="n == currentPage"
                    >(Current)</span
                  ></span
                >
              </template>
              <template v-else>
                <button class="page-link" @click="getPages(n)">{{ n }}</button>
              </template>
            </li>
          </template>
          <li
            :class="{
              'page-item': true,
              next: true,
              disabled: currentPage == totalPages,
            }"
          >
            <template v-if="currentPage == totalPages">
              <span class="page-link"
                >Next <i class="fa fa-arrow-right" aria-hidden="true"></i
              ></span>
            </template>
            <template v-else>
              <button class="page-link" @click="getPages(currentPage + 1)">
                Next <i class="fa fa-arrow-right" aria-hidden="true"></i>
              </button>
            </template>
          </li>
        </ul>
      </div>
    </template>
  </template>
</template>
<style scoped>
/* This just matches what the <a> tags have on the default pagination */
.pagination > li > button,
.pagination > li > span {
  position: relative;
  float: left;
  padding: 12px;
  margin-left: -1px;
  font-size: 14px;
  line-height: 1.42857143;
  text-decoration: none;
  background: none;
  background-color: #fff;
  border: 1px solid #ddd;
  transition: all 300ms ease;
}
.pagination > .prev > span {
  border-right: 1px solid #ddd;
}
.pagination > .next > span {
  border-left: 1px solid #ddd;
}

div.ccm-page .pagination > li.active > span,
.pagination > li.active > button {
  color: white;
  background: var(--pagination-primary);
}

.pagination > li > button:hover,
.pagination > li > span:hover,
.pagination > li > button:focus,
.pagination > li > span:focus {
  background: #333;
  color: white;
}
.pagination > li.disabled > button:hover,
.pagination > li.disabled > span:hover,
.pagination > li.disabled > button:focus,
.pagination > li.disabled > span:focus {
  background: #fff;
  color: black;
}

div.ccm-page .pagination > li.prev button {
  border-left: 1px solid #d4efbd;
  border-top-left-radius: 25px;
  border-bottom-left-radius: 25px;
  padding-left: 25px;
}
div.ccm-page .pagination > li.next button {
  border-right: 1px solid #d4efbd;
  border-top-right-radius: 25px;
  border-bottom-right-radius: 25px;
  padding-right: 25px;
}

.sr {
  border: none;
  clip: rect(0, 0, 0, 0);
  height: 1px;
  margin: -1px;
  overflow: hidden;
  width: 1px;
  position: absolute;
  padding: 0;
}
</style>
<style>
:root {
  --pagination-primary: #337ab7;
}
</style>
