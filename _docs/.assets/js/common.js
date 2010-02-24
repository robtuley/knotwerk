/**
 * Navigation.
 */
$(document).ready(function(){

    // create sitemap at bottom of page from nav,
    // balanced between two cols
    var li = $('#nav .inner > ul > li').clone();
    var half = Math.ceil($('#nav li').length/2);
    var target = $('#footer .primary');
    var so_far = 0;
    var ul = $('<ul></ul>').addClass('first');
    li.each(function () {
        so_far = so_far+1+$('li',this).length;
        ul.append(this);
        if (so_far>half) {
            target.append(ul);
            ul = $('<ul></ul>');
            so_far = 0;
        }
    });
    target.append(ul);

});

/**
 * IE :last-child fix for breadcrumbs
 */
$(document).ready(function(){ $('#crumbs li:last-child').addClass('last'); })