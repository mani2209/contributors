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

    $custom_content .= "Contributors<br>";
    foreach ($arrContributors as $strKey => $strValue) {
    	
    	$arrUserData = get_userdata($strValue);
    	$gAvtar = get_avatar( $strValue, 32 );
    	$aUrl = get_author_posts_url($strValue);
    	$custom_content .= $gAvtar."<a href='".$aUrl."'>".$arrUserData->user_nicename."</a><br>";
    }

    return $custom_content;
    } 
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
    $arrContributors = unserialize($strContributors);

    $users_array = get_contributors();
   

    foreach ($users_array as $strKey => $strValue) { 
    		$checked = "";
    		if(in_array($strValue->ID, $arrContributors)){
    			$checked = "checked";
    		}
    	?>
    	<input type="checkbox" name="contributors[]" <?php echo $checked; ?> value="<?php echo $strValue->ID; ?>"><?php echo $strValue->user_nicename; ?><br />
    <?php }
    
}

add_action( 'save_post', 'meta_box_video_save' );
function meta_box_video_save( $post_id )
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