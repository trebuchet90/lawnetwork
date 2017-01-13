//member types screen
if (typeof jq == "undefined")
    var jq = jQuery;

jq(document).ready(function () {

    //Member post type validation
    jq('#post').submit(function () {
        
        jq('#title').css({border: "none"});
        jq('.bmt-label-name').css({border: "none"});
        jq('.bmt-singular-name').css({border: "none"});

        var p_title = jq('#title').val();
        var p_plural_name = jq('.bmt-label-name').val();
        var p_singular_name = jq('.bmt-singular-name').val();

        if (p_title.length == 0) {
            jq('#title').css({"border-color": "#d54e21",
                "border-width": "1px",
                "border-style": "solid"});

        }
        if (p_plural_name.length == 0) {
            jq('.bmt-label-name').css({"border-color": "#d54e21",
                "border-width": "1px",
                "border-style": "solid"});
        }
        if (p_singular_name.length == 0) {
            jq('.bmt-singular-name').css({"border-color": "#d54e21",
                "border-width": "1px",
                "border-style": "solid"});
        }

        if ( p_title.length == 0 || p_plural_name.length == 0 || p_singular_name.length == 0 ) {
            return false;
        }
        
        return true;

    });

});