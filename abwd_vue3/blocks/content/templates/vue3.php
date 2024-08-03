<?php
    defined('C5_EXECUTE') or die('Access Denied.');
    /** @var \Concrete\Block\Content\Controller $controller */
    /** @var string $content */

    $c = \Concrete\Core\Page\Page::getCurrentPage(); 
    $this->requireAsset('abwd-vue3-core');
?>
    <div class="vue3-block content">
        <?php if (!$content && is_object($c) && $c->isEditMode()) {
            ?>
            <content-block class="vue3-block content ccm-edit-mode-disabled-item"><?=t('Empty Content Block.')?></content-block>
        <?php } else { ?>
            <content-block class="vue3-block content"><?= $content; ?></content-block>
        <?php } ?>
    </div>


