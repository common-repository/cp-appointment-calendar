<?php
add_action( 'admin_enqueue_scripts', 'cpappcal_feedback_insert_adminScripts', 1); 
add_action( 'wp_ajax_cpappcal_feedback', 'cpappcal_feedback' );
$cpappcal_plugfile =  'dex_appointments.php';
$cpappcal_plugslug = 'cp-appointment-calendar';
$cpappcal_supportlink = 'https://wordpress.dwbooster.com/support';
$cpappcal_supportlink_full = $cpappcal_supportlink . '?priority-support=yes';

if (!get_option('installed_'.$cpappcal_plugslug, ''))
    update_option('installed_'.$cpappcal_plugslug, time() );

function cpappcal_feedback_insert_adminScripts($hook) {
    if( 'plugins.php' == $hook  )            
    {
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-dialog');       
        add_action( 'admin_footer', 'cpappcal_feedback_javascript' );            
    }
}
    


// This function is used only if explicitly accepted (opt-in) by the user
function cpappcal_feedback() {
    global $cpappcal_plugfile, $cpappcal_plugslug;
    $cpappcal_plugin_data = get_plugin_data( dirname(__FILE__).'/'.$cpappcal_plugfile );
    $cpappcal_plugin_version = $cpappcal_plugin_data['Version'];
    $time = time() - get_option('installed_'.$cpappcal_plugslug, '');
    $data = array(
                 'answer' => (@$_POST["answer"]),
                 'otherplugin' => (@$_POST["opinfo"]),
                 'otherinfo' => (@$_POST["oinfo"]),
                 'plugin' => ($cpappcal_plugin_data['Name']),
                 'pluginv' => ($cpappcal_plugin_version),
                 'wordpress' => (get_bloginfo( 'version' )),
                 'itime' => ($time),
                 'phpversion' => (phpversion ())
                 );
    if (@$_POST["onymous"] == 'false') // send this data only if explicitly accepted
    {
        $current_user = wp_get_current_user();
        $data['email'] = ($current_user->user_email);
        $data['website'] = ($_SERVER['HTTP_HOST']);
        $data['url'] = (get_site_url());
    }

    //extract data from the post
    //set POST variables
    $url = 'https://wordpress.dwbooster.com/licensesystem/debug-data.php';
    $fields = $data;
    
    wp_remote_post( 
                     $url,
                     array ( 'body' => $fields )
                  );
    
	wp_die(); // this is required to terminate immediately and return a proper response
}


function cpappcal_feedback_javascript() { 
    global $cpappcal_plugslug, $cpappcal_supportlink, $cpappcal_supportlink_full; 
      ?>
	<script type="text/javascript">

$ = jQuery.noConflict()
$( window ).load(function() {
        document.querySelector('[data-slug="<?php echo $cpappcal_plugslug; ?>"] .deactivate a').addEventListener('click', function(event){
            event.preventDefault()
            var urlRedirect = document.querySelector('[data-slug="<?php echo $cpappcal_plugslug; ?>"] .deactivate a').getAttribute('href');
                      
            $('<div title="QUICK FEEDBACK"><div style="padding:10px;">'+
               '<style type="text/css">.abcreasonblock { margin-top:8px; }</style>'+
      		   '<h3><strong>If you have a moment, please let us know why you are deactivating:</strong></h3>'+
               '<form id="cpfeedbackform">'+ 
               
               '<div class="abcreasonblock"><input type="radio" name="abcm_reason" onclick="cpappcal_update_reason(this);" value="no-work"> The plugin didn\'t work<br />'+
                '<div id="abcm_nowork" style="margin-left:25px;display:none;padding:10px;border:1px dotted gray;color:#660000"><strong>We can help!</strong> We offer <strong>free support</strong> for this plugin. Feel free to open a support ticket at <a href="<?php echo $cpappcal_supportlink_full; ?>"><strong><?php echo $cpappcal_supportlink; ?></strong></a> and we will be happy to help.</div></div>'+
                
               '<div class="abcreasonblock"><input type="radio" name="abcm_reason" onclick="cpappcal_update_reason(this);" value="-"> I don\'t like to share my information with you<br /></div>'+
               
               '<div class="abcreasonblock"><input type="radio" name="abcm_reason" onclick="cpappcal_update_reason(this);" value="temporary-deactivation"> It\'s a temporary deactivation. I\'m just debugging an issue.<br /></div>'+
               
               '<div class="abcreasonblock"><input type="radio" name="abcm_reason" onclick="cpappcal_update_reason(this);" value="better-plugin"> I found a better plugin<br />'+
               '<div id="abcm_otherplugin" style="margin-left:25px;display:none;"><input type="text" name="abcm_otherpinfo" placeholder="What\'s the plugin name?" style="width:100%"></div></div>'+
               
               '<div class="abcreasonblock"><input type="radio" name="abcm_reason" onclick="cpappcal_update_reason(this);" value="other"> Other<br />'+
               '<div id="abcm_other" style="margin-left:25px;display:none;font-weight:bold;">Kindly tell us the reason so we can improve.<br /><input type="text" name="abcm_otherinfo" style="width:100%"></div></div>'+
            
               '<div id="abcnofeedback" style="display:none;margin-top:30px;text-align:right"><input type="checkbox" name="cpabcanonymous" value="yes"> Anonymous feedback</div>'+
               
               '</form>'+               
      		   '</div></div>'
      		  ).dialog({
                  width:'600',
      			dialogClass: 'wp-dialog',
                  modal: true,
                  close: function(event, ui)
                  {
                      $(this).dialog("close");
                      $(this).remove();
                  },
                  closeOnEscape: true,
                  buttons: [
                      {
                        id: 'abcdeactivatebtn',
                        text: "Skip & Deactivate", 
                        click: function() {                               
                                   var answer = $("input[name='abcm_reason']:checked").val();
                                   if (answer == undefined || answer == '' || answer == '-')
                                       window.location.href = urlRedirect; 
                                   else
                                   {
                                       var opinfo = $("input[name='abcm_otherpinfo']").val();
                                       var oinfo = $("input[name='abcm_otherinfo']").val();
                                       var isAnonymous = $("input[name='cpabcanonymous']:checked").length > 0;
                                       var data = {
		                                   	'action': 'cpappcal_feedback',
		                                   	'answer': $("input[name='abcm_reason']:checked").val(),
                                            'opinfo': $("input[name='abcm_otherpinfo']").val(),
                                            'oinfo': $("input[name='abcm_otherinfo']").val(),
                                            'onymous': $("input[name='cpabcanonymous']:checked").length > 0
		                                   };                                       
                                       $.post(ajaxurl, data, function(response) {
			                               window.location.href = urlRedirect;      						           
		                               });    
                                   }                                   
      				           }                            
                      },
                      {
                        text: "We can help: Support Service", 
                        click: function() {
                                   window.open('<?php echo $cpappcal_supportlink_full; ?>');
      						       $(this).dialog("close");
      				           }                            
                      },
                      {
                        text: "Cancel", 
                        "class": 'button button-primary button-close',
                        click: function() {
      						       $(this).dialog("close");
      				           }                            
                      }
                  ]
              });
        })
});

function cpappcal_update_reason(field)
{
    document.getElementById("abcdeactivatebtn").value = 'Submit & Deactivate';
    document.getElementById("abcdeactivatebtn").innerHTML = '<span class="ui-button-text">Submit &amp; Deactivate</span>';
    document.getElementById("abcnofeedback").style.display = '';
    document.getElementById("abcm_other").style.display = 'none';
    document.getElementById("abcm_otherplugin").style.display = 'none';
    document.getElementById("abcm_nowork").style.display = 'none';
    if (field.value == '-')
    {
        document.getElementById("abcdeactivatebtn").value = 'Skip & Deactivate';
        document.getElementById("abcdeactivatebtn").innerHTML = '<span class="ui-button-text">Skip &amp; Deactivate</span>'; 
        document.getElementById("abcnofeedback").style.display = 'none';        
    }    
    else if (field.value == 'other')
        document.getElementById("abcm_other").style.display = '';
    else if (field.value == 'better-plugin')
        document.getElementById("abcm_otherplugin").style.display = '';
    else if (field.value == 'no-work')
        document.getElementById("abcm_nowork").style.display = '';    
}

	</script><?php
}

?>