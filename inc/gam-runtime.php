<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Normalize the GAM/GPT snippet stored in pv_v7_head_code at render time.
 *
 * The saved admin value is intentionally left untouched. This keeps rollback
 * simple while fixing two runtime issues:
 * - GPT may finish loading before body slot divs exist, causing display() calls
 *   to be skipped permanently on that page load.
 * - legacy GPT APIs currently emit deprecation warnings.
 */
function pv_child_normalize_gam_head_code( $code ) {
    if ( ! is_string( $code ) || strpos( $code, 'googletag' ) === false ) {
        return $code;
    }

    $code = str_replace(
        ".setTargeting('pos','inarticle')",
        ".setConfig({targeting:{pos:'inarticle'}})",
        $code
    );

    $code = str_replace(
        ".setTargeting('pos','inbanner')",
        ".setConfig({targeting:{pos:'inbanner'}})",
        $code
    );

    $code = str_replace(
        'googletag.pubads().enableSingleRequest();',
        'googletag.setConfig({singleRequest:true});',
        $code
    );

    $pattern = '~(\[\s*[\'\"]div-gpt-970x250-masthead[\'\"][\s\S]*?[\'\"]div-gpt-video-sticky[\'\"]\s*\])\.forEach\(function\(id\)\{\s*if\(document\.getElementById\(id\)\)\s*googletag\.display\(id\);\s*\}\);~';

    $replacement = <<<'JS'
var pvGptDisplayIds = $1;
        var pvDisplayGptSlots = function(){
          pvGptDisplayIds.forEach(function(id){
            if(document.getElementById(id)) googletag.display(id);
          });
        };
        if(document.readyState === 'loading'){
          document.addEventListener('DOMContentLoaded', pvDisplayGptSlots, {once:true});
        } else {
          pvDisplayGptSlots();
        }
JS;

    $code = preg_replace( $pattern, $replacement, $code, 1 );

    return $code;
}
add_filter( 'option_pv_v7_head_code', 'pv_child_normalize_gam_head_code', 20 );
