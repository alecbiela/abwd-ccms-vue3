<?php
    /**
     * All this block view really does is invoke the custom element
     * created by the Vue compilation and pass any props we need.
     * (e.g. any block-specific settings loaded in the block controller)
     */
    defined('C5_EXECUTE') or die('Access Denied');
?>
<div class="vue3-block example-block">
    <example-block block-id="<?= $bID; ?>"></example-block>
</div>