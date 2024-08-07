<?php
namespace Concrete\Package\AbwdVue3\Controller\Api;

use BlockType;
use CollectionAttributeKey;
use Concrete\Core\Attribute\Key\CollectionKey;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Block\View\BlockView;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Feature\Features;
use Concrete\Core\Feature\UsesFeatureInterface;
use Concrete\Core\Html\Service\Seo;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Package\Offline\Exception;
use Concrete\Core\Page\Feed;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Tree\Node\Type\Topic;
use Core;
use Concrete\Core\Url\SeoCanonical;
use Database;
use Page;
use PageList;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Block\Block;

class ApiPageListController
{
    protected $list;
    protected $block;
    protected $settings;

    /**
     * The API page list controller will construct itself around the PageList Block ID that's passed in
     */
    public function __construct($bID = null)
    {
        $b = Block::getByID($bID);
        if(!is_object($b)){
            return new JsonResponse([
                'message'=>'A block was not found matching the given ID.'
            ], 400);
        }

        // Instantiating the block controller will pull all its settings from the DB
        $this->settings = $b->getController();

        $this->list = new PageList();
        $this->list->disableAutomaticSorting();
        $this->list->setNameSpace('b' . $bID);
        $expr = $this->list->getQueryObject()->expr(); // Get Query Expression Object

        $cArray = [];

        switch ($this->settings->orderBy) {
            case 'display_asc':
                $this->list->sortByDisplayOrder();
                break;
            case 'display_desc':
                $this->list->sortByDisplayOrderDescending();
                break;
            case 'chrono_asc':
                $this->list->sortByPublicDate();
                break;
            case 'modified_desc':
                $this->list->sortByDateModifiedDescending();
                break;
            case 'random':
                $this->list->sortBy('RAND()');
                break;
            case 'alpha_asc':
                $this->list->sortByName();
                break;
            case 'alpha_desc':
                $this->list->sortByNameDescending();
                break;
            default:
                $this->list->sortByPublicDateDescending();
                break;
        }

        $now = Core::make('helper/date')->toDB();
        $end = $start = null;

        switch ($this->settings->filterDateOption) {
            case 'now':
                $start = date('Y-m-d') . ' 00:00:00';
                $end = $now;
                break;

            case 'past':
                $end = $now;

                if ($this->settings->filterDateDays > 0) {
                    $past = date('Y-m-d', strtotime("-{$this->settings->filterDateDays} days"));
                    $start = "$past 00:00:00";
                }
                break;

            case 'future':
                $start = $now;

                if ($this->settings->filterDateDays > 0) {
                    $future = date('Y-m-d', strtotime("+{$this->settings->filterDateDays} days"));
                    $end = "$future 23:59:59";
                }
                break;

            case 'between':
                $start = "{$this->settings->filterDateStart} 00:00:00";
                $end = "{$this->settings->filterDateEnd} 23:59:59";
                break;

            case 'all':
            default:
                break;
        }

        if ($start) {
            $this->list->filterByPublicDate($start, '>=');
        }
        if ($end) {
            $this->list->filterByPublicDate($end, '<=');
        }

        $c = Page::getCurrentPage();
        if (is_object($c)) {
            $this->settings->cID = $c->getCollectionID();
            $this->settings->cPID = $c->getCollectionParentID();
        }

        if ($this->settings->displayFeaturedOnly == 1) {
            $cak = CollectionAttributeKey::getByHandle('is_featured');
            if (is_object($cak)) {
                $this->list->filterByIsFeatured(1);
            }
        }
        if ($this->settings->displayAliases) {
            $this->list->includeAliases();
        }
        if ($this->settings->displaySystemPages) {
            $this->list->includeSystemPages();
        }
        if (isset($this->settings->ignorePermissions) && $this->settings->ignorePermissions) {
            $this->list->ignorePermissions();
        }
        if ($this->settings->excludeCurrentPage) {
            $ID = Page::getCurrentPage()->getCollectionID();
            $this->list->getQueryObject()->andWhere($expr->neq('p.cID', $ID));
        }

        $this->list->filter('cvName', '', '!=');

        if ($this->settings->ptID) {
            $this->list->filterByPageTypeID($this->settings->ptID);
        }

        if ($this->settings->filterByRelated) {
            $ak = CollectionKey::getByHandle($this->settings->relatedTopicAttributeKeyHandle);
            if (is_object($ak)) {
                $topics = $c->getAttribute($ak->getAttributeKeyHandle());
                if (is_array($topics) && count($topics) > 0) {
                    $topic = $topics[array_rand($topics)];
                    $this->list->filter('p.cID', $c->getCollectionID(), '<>');
                    $this->list->filterByTopic($topic);
                }
            }
        }

        if ($this->settings->filterByCustomTopic) {
            $ak = CollectionKey::getByHandle($this->settings->customTopicAttributeKeyHandle);
            if (is_object($ak)) {
                $topic = Node::getByID($this->settings->customTopicTreeNodeID);
                if ($topic) {
                    $ak->getController()->filterByAttribute($this->list, $this->settings->customTopicTreeNodeID);
                }
            }
        }

        $this->list->filterByExcludePageList(false);

        if ((int) ($this->settings->cParentID) != 0) {
            $cParentID = ($this->settings->cThis) ? $this->settings->cID : (($this->settings->cThisParent) ? $this->settings->cPID : $this->settings->cParentID);
            if ($this->settings->includeAllDescendents) {
                $this->list->filterByPath(Page::getByID($cParentID)->getCollectionPath());
            } else {
                $this->list->filterByParentID($cParentID);
            }
        }

        /*if ($this->settings->paginate) {
            $paging = $this->settings->request->request($this->list->getQueryPaginationPageParameter());
            if ($paging && $paging >= 2) { // Canonicalize page 2 and greater only
                // @var SeoCanonical $seoCanonical 
                $seoCanonical = $this->app->make(SeoCanonical::class);
                $seoCanonical->addIncludedQuerystringParameter($this->list->getQueryPaginationPageParameter());
            }
        }*/
    }

    /**
     * Gets and returns an array of page data based on block id and page (for paginated lists)
     * @param int|null $pageNum - The page number of the results (for paginated results, optional)
     * 
     */
    public function loadPages($pageNum)
    {
        $dh = Core::make('helper/date');

        $includeEntryText = (
            (isset($this->settings->includeName) && $this->settings->includeName) ||
            (isset($this->settings->includeDescription) && $this->settings->includeDescription) ||
            (isset($this->settings->useButtonForLink) && $this->settings->useButtonForLink)
        );

        // Handle pagination
        if ($this->settings->num > 0) {
            
            $this->list->setItemsPerPage($this->settings->num);
            $pagination = $this->list->getPagination();

            // If page number is out of bounds, default it to 1
            // After, set the current page to the page number
            $totalPages = $pagination->getTotalPages();
            $currentPage = (is_null($pageNum) || ($pageNum > $totalPages) || ($pageNum < 1)) ? 1 : $pageNum;
            $pagination->setCurrentPage(intval($currentPage));

            $pages = $pagination->getCurrentPageResults();
        } else {
            $totalPages = 1;
            $currentPage = 1;
            $pages = $list->getResults();
        }

        $pageProps = array();
        
        foreach ($pages as $k=>$page) {

            // Initialize the page object with defaults
            $pageProps[$k] = array(
                'title' => h($page->getCollectionName()),
                'target' => h('_self'),
                'description' => h($page->getCollectionDescription()),
                'thumbnail' => false,
                'entry_classes' => 'ccm-block-page-list-page-entry',
                'date' => h($dh->formatDateTime($page->getCollectionDatePublic(), true))
            );

            // Get the page URL - if external link, set to open in a new tab
            if ($page->getCollectionPointerExternalLink() != '') {
                $pageProps[$k]['url'] = $page->getCollectionPointerExternalLink();
                if ($page->openCollectionPointerExternalLinkInNewWindow()) {
                    $pageProps[$k]["target"] = h('_blank');
                }
            } else {
                $pageProps[$k]['url'] = h($page->getCollectionLink());
                $pageProps[$k]['target'] = h($page->getAttribute('nav_target'));
            }

            // If description is set to truncate after 'n' chars, limit it
            if($this->settings->truncateSummaries) $pageProps[$k]['description'] = h($th->wordSafeShortText($description, $this->settings->truncateChars));

            // Image creation if needed
            // Because of how CCMS image creation works (with image theme settings, thumbnail generation, etc.)
            // we generate the <img> tag here and pass it as a string into the props
            if ($this->settings->displayThumbnail) {
                $thumbnail = $page->getAttribute('thumbnail');
                $img = Core::make('html/image', ['f' => $thumbnail, 'options' => array('usePictureTag'=>false)]);
                if(isset($img)) {
                    $t = $img->getTag();
                    if(!is_null($t)){
                        $t->addClass('page-list-image');
                        /** OUTPUT BUFFER - we need to do this to grab the generated <img> **/
                        ob_start();
                        echo $t;
                        $tag = ob_get_clean();
                        $pageProps[$k]['thumbnail'] = $tag;
                        // Add special classes on the entry text for thumbnails
                        if ($includeEntryText) $pageProps[$k]['entry_classes'] = h($pageProps[$k]['entry_classes'].' ccm-block-page-list-page-entry-horizontal');
                    }
                }
            }

            //Other useful page data...

            //$last_edited_by = $page->getVersionObject()->getVersionAuthorUserName();

            /* DISPLAY PAGE OWNER NAME
                * $page_owner = UserInfo::getByID($page->getCollectionUserID());
                * if (is_object($page_owner)) {
                *     echo $page_owner->getUserDisplayName();
                * }
                */

            /* CUSTOM ATTRIBUTE EXAMPLES:
                * $example_value = $page->getAttribute('example_attribute_handle', 'display');
                *
                * When you need the raw attribute value or object:
                * $example_value = $page->getAttribute('example_attribute_handle');
                */
        }

        return new JsonResponse([
            'results'=>$pageProps,
            'total_pages'=>intval($totalPages),
            'current_page'=>intval($currentPage)
        ]);
    }




    // public function view()
    // {
    //     $list = $this->list;
    //     $nh = Core::make('helper/navigation');
    //     $this->set('nh', $nh);

    //     if ($this->pfID) {
    //         $this->requireAsset('css', 'font-awesome');
    //         $feed = Feed::getByID($this->pfID);
    //         if (is_object($feed)) {
    //             $this->set('rssUrl', $feed->getFeedURL());
    //             $link = $feed->getHeadLinkElement();
    //             $this->addHeaderItem($link);
    //         }
    //     }

    //     //Pagination...
    //     $showPagination = false;
    //     if ($this->num > 0) {
    //         $list->setItemsPerPage($this->num);
    //         $pagination = $list->getPagination();
    //         $pages = $pagination->getCurrentPageResults();
    //         if ($pagination->haveToPaginate() && $this->paginate) {
    //             $showPagination = true;
    //             $pagination = $pagination->renderDefaultView();
    //             $this->set('pagination', $pagination);
    //         }
    //     } else {
    //         $pages = $list->getResults();
    //     }

    //     if ($showPagination) {
    //         $this->requireAsset('css', 'core/frontend/pagination');
    //     }
    //     $this->set('pages', $pages);
    //     $this->set('list', $list);
    //     $this->set('showPagination', $showPagination);
    // }

    // public function action_filter_by_topic($treeNodeID = false, $topic = false)
    // {
    //     if ($treeNodeID) {
    //         $topicObj = Topic::getByID((int) $treeNodeID);
    //         if (is_object($topicObj) && $topicObj instanceof Topic) {
    //             $this->list->filterByTopic((int) $treeNodeID);

    //             /** @var Seo $seo */
    //             $seo = $this->app->make('helper/seo');
    //             $seo->addTitleSegment($topicObj->getTreeNodeDisplayName());

    //             /** @var SeoCanonical $canonical */
    //             $canonical = $this->app->make(SeoCanonical::class);
    //             $canonical->setPathArguments(['topic', $treeNodeID, $topic]);
    //         }
    //     }
    //     $this->view();
    // }

    // public function action_filter_by_tag($tag = false)
    // {
    //     /** @var Seo $seo */
    //     $seo = $this->app->make('helper/seo');
    //     $seo->addTitleSegment($tag);

    //     /** @var SeoCanonical $canonical */
    //     $canonical = $this->app->make(SeoCanonical::class);
    //     $canonical->setPathArguments(['tag', $tag]);

    //     $this->list->filterByTags(h($tag));
    //     $this->view();
    // }

    // public function action_search_keywords($bID)
    // {
    //     if ($bID == $this->bID) {
    //         $keywords = h($this->request->query->get('keywords'));
    //         $this->list->filterByKeywords($keywords);
    //         $this->view();
    //     }
    // }

    // public function action_filter_by_date($year = false, $month = false, $timezone = 'user')
    // {
    //     if (is_numeric($year)) {
    //         $year = (($year < 0) ? '-' : '') . str_pad(abs($year), 4, '0', STR_PAD_LEFT);
    //         if ($month) {
    //             $month = str_pad($month, 2, '0', STR_PAD_LEFT);
    //             $lastDayInMonth = date('t', strtotime("$year-$month-01"));
    //             $start = "$year-$month-01 00:00:00";
    //             $end = "$year-$month-$lastDayInMonth 23:59:59";
    //         } else {
    //             $start = "$year-01-01 00:00:00";
    //             $end = "$year-12-31 23:59:59";
    //         }
    //         $dh = Core::make('helper/date');
    //         /* @var $dh \Concrete\Core\Localization\Service\Date */
    //         if ($timezone !== 'system') {
    //             $start = $dh->toDB($start, $timezone);
    //             $end = $dh->toDB($end, $timezone);
    //         }
    //         $this->list->filterByPublicDate($start, '>=');
    //         $this->list->filterByPublicDate($end, '<=');

    //         /** @var Seo $seo */
    //         $seo = $this->app->make('helper/seo');
    //         $date = ucfirst(\Punic\Calendar::getMonthName($month, 'wide', '', true) . ' ' . $year);
    //         $seo->addTitleSegment($date);

    //         /** @var SeoCanonical $canonical */
    //         $canonical = $this->app->make(SeoCanonical::class);
    //         $canonical->setPathArguments([$year, $month]);
    //     }
    //     $this->view();
    // }

    // public function isBlockEmpty()
    // {
    //     $pages = $this->get('pages');
    //     if (isset($this->pageListTitle) && $this->pageListTitle) {
    //         return false;
    //     }
    //     if (empty($pages)) {
    //         if ($this->noResultsMessage) {
    //             return false;
    //         }

    //         return true;
    //     }
    //     if ($this->includeName || $this->includeDate || $this->displayThumbnail
    //             || $this->includeDescription || $this->useButtonForLink
    //         ) {
    //         return false;
    //     }

    //     return true;
    // }
}
