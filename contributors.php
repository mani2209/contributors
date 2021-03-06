<?php
/*
Plugin Name: WordPress Contributors Plugin
Description: Plugin for show contributors
Author: Manish K Srivastava
Version: 1.0
*/




function show_contributor( $content ) {
 if ( is_single() && 'post' == get_post_type() ) {
    $custom_content = $content;

    $strContributors = get_post_meta(get_the_ID() , 'meta_box_contributor' , true);
    $arrContributors = unserialize($strContributors);
    $post_author = get_post_field( 'post_author', get_the_ID() );
    $arrContributors[] = $post_author;
    
    if($arrContributors):
        $custom_content .= "Contributors<br>";
        foreach ($arrContributors as $strKey => $strValue) {
            

            $arrUserData = get_userdata($strValue);
            $gAvtar = get_avatar( $strValue, 32 );
            $aUrl = get_author_posts_url($strValue);
            if($arrUserData->display_name){
                $strDisplayName = $arrUserData->display_name;
            }else{
                $strDisplayName = $arrUserData->user_nicename;
            }
             
            $custom_content .= $gAvtar."<a href='".$aUrl."'>".$strDisplayName."</a><br>";
        }
    endif;
    return $custom_content;
    } 
    return $content;
}
add_filter( 'the_content', 'show_contributor' );


add_action( 'add_meta_boxes', 'meta_box_contributor' );
function meta_box_contributor()
{
    add_meta_box( 'contributor-meta-box-id', 'Contributors', 'meta_box_callback', 'post', 'normal', 'high' );
}

function meta_box_callback( $post )
{
   
    $strContributors = get_post_meta($post->ID , 'meta_box_contributor' , true);
    
        if($strContributors)
            $arrContributors = unserialize($strContributors);
        else
            $arrContributors = array();

        $users_array = get_contributors();
        $post_author = get_post_field( 'post_author', $post->ID );
       
        if($users_array):
            foreach ($users_array as $strKey => $strValue) { 
                if($post_author != $strValue->ID):
                    $checked = "";
                    if(in_array($strValue->ID, $arrContributors)){
                        $checked = "checked";
                    }
                    if($strValue->display_name){
                        $strDisplayName = $strValue->display_name;
                    }else{
                        $strDisplayName = $strValue->user_nicename;
                    }
                ?>
                <input type="checkbox" name="contributors[]" <?php echo $checked; ?> value="<?php echo $strValue->ID; ?>"><?php echo $strDisplayName; ?><br />
            <?php endif; }
        endif;    
    
}

add_action( 'save_post', 'meta_box_contributor_save' );
function meta_box_contributor_save( $post_id )
{ 
    // Bail if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    
    // if our current user can't edit this post, bail
    if( !current_user_can( 'edit_post' ) ) return;

    // now we can actually save the data
    $allowed = array( 
        'a' => array( // on allow a tags
            'href' => array() // and those anchords can only have href attribute
        )
    );

    // Probably a good idea to make sure your data is set

    if( isset( $_POST['contributors'] ) ):
        $strContributors = serialize($_POST['contributors']);
        update_post_meta( $post_id, 'meta_box_contributor', $strContributors );
    elseif($_POST['contributors'] == ''):
        update_post_meta( $post_id, 'meta_box_contributor', '');
    endif;

}

function get_contributors() { 

    $users = array();
    $roles = array('author', 'administrator', 'editor');

    foreach ($roles as $role) :
        $users_query = new WP_User_Query( array( 
            'fields' => 'all_with_meta', 
            'role' => $role, 
            'orderby' => 'display_name'
            ) );
        $results = $users_query->get_results();
        if ($results) $users = array_merge($users, $results);
    endforeach;

    foreach ($users as $key => $value) {
        $arrUser[] = $value->data;
    }

    return $arrUser;
}