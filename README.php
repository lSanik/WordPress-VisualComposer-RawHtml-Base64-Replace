# WordPress VisualComposer RawHtml Base64  data base mass Replacing

For developers only!

This is little script for mass changes in wordpress visual composer database. 
You can change all domains, html code, scripts and etc, data that in base64 coded.

MAKE BACK-UP database table WP_POSTS!

All what you need, its take this in function.php at your theme folder.
Change sample data to yours needs.
Run url - your_site.com/replace_composer_html_raw_base64

If success - all data will be changed and you are happy now ;)


<?php
function replace_composer_html_raw_base64(){

    if($_SERVER['REQUEST_URI'] == '/replace_composer_html_raw_base64'){
        global $wpdb;
        
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        $response = $wpdb->get_results('SELECT ID,post_content FROM wp_posts WHERE post_type="page"',ARRAY_A);
        $count = count($response);
        $tag = 'vc_raw_js';//'vc_raw_js';//'vc_raw_html';//vc_raw_js
        $pattern = '\['.$tag.'].*?\]';

        foreach ($response as $post){

            $matches = '';
            preg_match_all('/' . $pattern . '/s', $post['post_content'], $matches);
            $content = replacedComposerHtmlRawString($matches[0],$tag);

            if(!$content && count($content)<1){
                continue;
            }

            foreach ($content as $key=>$item){
                $post['post_content'] = str_replace($item['original'],$item['modified'],$post['post_content']);
            }

            //$post['post_content'] = replacedComposerRawFromTo();

            $upd = array(// Update the post into the database
                'ID'           => $post['ID'],
                'post_content' => $post['post_content'],
            );
            wp_update_post( $upd );
        }

        die('If no errors, all successful! =)  ');
    }
}
// String with shortcode tag, and different tag send, like vc_raw_html,vc_raw_js, etc.
function replacedComposerHtmlRawString($strings,$tag){
    
    if(!is_array($strings)){
        return false;
    }
    
    $return=array();
    foreach ($strings as $key=>$string){
        $return[$key]['original']= $string;
        $string = str_replace('['.$tag.']','',$string);
        $string = str_replace('[/'.$tag.']','',$string);
        $string = base64_decode($string);
        $string = rawurldecode($string);//If something not working, try change this rawurlencode to urlencode, etc... dumped it =)

        //place to replace decoded string
        $string = replacedComposerRawFromTo($string);
        //end place..
        
        $string = rawurlencode($string);
        $string = base64_encode($string);
        $string = '['.$tag.']'.$string.'[/'.$tag.']';
        $return[$key]['modified'] = $string;
    }

    return $return;
}

function replacedComposerRawFromTo($string,$fromTo=false){
    //Changed from 'value' = > to 'value';
    $fromTo=array(
    //Sample data to change!
        'http'=>'https',
        'token123'=>'token321',
        '<script>Hello World</script>'=>'<script>HI WORLD</script>',

    );

    foreach ($fromTo as $from=>$to){
        $string = str_replace($from,$to,$string);
    }

    return $string;
}

add_action('init', 'replace_composer_html_raw_base64');


