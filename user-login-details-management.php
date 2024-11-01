<?php
/*
	Plugin Name: User Login Details Management
	Description: This will add three column within user listing table in admin and let display login details respective of the user.
	Version: 1.0
	Author: Elsner Technologies
	Author URI: http://www.elsner.com
*/

add_action('init', 'uldm_thickbox_callback', 10);
function uldm_thickbox_callback(){
	add_thickbox();
}

function uldm_user_login_details_modify_user_table( $column ) {
    $column['uldm_last_login_date_time'] = 'Last Login';
    $column['uldm_view_login_log'] = 'View Login Log';
    $column['uldm_days_since_last_login'] = 'Days since last login';
    
    return $column;
}
add_filter( 'manage_users_columns', 'uldm_user_login_details_modify_user_table' );


function uldm_user_login_details_modify_user_table_row( $val, $column_name, $user_id ) {
    switch($column_name) {

        case 'uldm_last_login_date_time' :
            return uldm_get_user_last_login($user_id,false);
            break;

        case 'uldm_days_since_last_login' :
            return uldm_days_since_last_login($user_id);
            break;    

        case 'uldm_view_login_log' :
            return '<a href="#TB_inline?width=300&height=300&inlineId='.$user_id.'_log" class="thickbox">Click here</a>'.uldm_get_login_log($user_id);
            break;

        default:
    }
}
add_filter( 'manage_users_custom_column', 'uldm_user_login_details_modify_user_table_row', 10, 3 );

			
// set the last login date
add_action('wp_login','uldm_set_last_login', 0, 2);
function uldm_set_last_login($login, $user) {

    $user = get_user_by('login',$login);
    $time = current_time( 'timestamp' );
    echo $time."<br>";
    $last_login = get_user_meta( $user->ID, '_last_login', 'true' );
    $_login_log = get_user_meta( $user->ID, '_login_log', 'true' );

    if(empty($_login_log))
    	$_login_log = array();

    if(!$last_login){

    	update_usermeta( $user->ID, '_last_login', $time );
    	update_usermeta( $user->ID, '_login_log', $_login_log );

    }else{

    	$_login_log[] = $time;
	    update_usermeta( $user->ID, '_last_login_prev', $last_login );
	    update_usermeta( $user->ID, '_last_login', $time );
	    update_usermeta( $user->ID, '_login_log', $_login_log );
	
	}

}

// get the last login date
function uldm_get_user_last_login($user_id,$echo = false){
    $date_format = get_option('date_format') . ' ' . get_option('time_format');

    $last_login = get_user_meta($user_id, '_last_login', true);
    
    $login_time = 'Never logged in';
    if(!empty($last_login)){
       if(is_array($last_login)){
       		$login_time = date('Y-m-d H:i:s', array_pop($last_login));
        }
        else{
            $login_time = date('Y-m-d H:i:s', $last_login);
        }
    }
    return $login_time;
}
 
  
function uldm_days_since_last_login($current_user) {
  
    if ($current_user ){
  		$now = time();
  		$last_login = uldm_get_user_last_login($current_user);
  		$datediff = $now - strtotime($last_login);
  		if($last_login != 'Never logged in'){
	  		$days = floor($datediff/(60*60*24));
	  		if($days > 365)
	  		return 'Long time ago';
	  		elseif($days >= 0)
	  		return 	$days. " day(s) ago"; 
            else
            return  "0 day(s) ago"; 
  		}else{
  			return 'Never Logged in';
  		}
    }
}


function uldm_get_login_log($user_id = 0){

	ob_start();
	$data = array();
	$data = get_user_meta( $user_id, '_login_log', 'true' );
	
	echo '<div id="'.$user_id.'_log" style="display:none;">';
	if(!empty($data)){
		echo "<p>Login log:</p>";
		echo "<ul>";
		foreach ($data as $key => $value) {
			echo "<li>".date('l M d Y H:i:s', $value)."</li>";
		}
		echo "</ul>";
	}else{
		echo "<p>No login log found for this user</p>";
	}
	echo '</div>';

	return ob_get_clean();

}
?>