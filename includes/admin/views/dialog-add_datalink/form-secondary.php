<?php

    /**
     * @var $link_spec
     *
     * The goal here is to get all the possible settings this post can have.
     *
     *  If it's a post, for example, we can put a bunch of taxonomies on there.
     *      Get all the different taxonomies
     *          Render the taxonomy as a header
     *          Get the terms
     *              Render the terms as a list of checkboxes
     *
     *  We use Data Fetcher for this.
     *      Basic will get the standard Wordpress taxonomies
     *          category, post_tag, format,
     *      Woocommerce will the standard Woocommerce taxonomies
     *          product type (but shouldnt add it here, since we already put it in the primary)
     *          product visibility
     *          product cat
     *
     * ACF should be leading in this
     *   So we should add:
     *      -List of specific posts
     *      -post statuses
     *      -post templates
     *
     *
     * So concretely, what we do here is to apply a filter first. Provide the link type and link spec.
     */
?>
<h2>
    Secondary
    <div class="tooltip"><i class="fa fa-info"></i>
        <span class="tooltiptext"><?php _e("These values are not required, but are helpful in generating more specific data fields. Try to be as specific as you need.", "mailcat"); ?></span>
    </div>

</h2>


<div id="dialog_add_datalink_secondary_container">

</div>


