<?php
/***********************************************************************************
 *
 * 	ROCKET READER - SETTINGS PAGE
 *
 *	Release: 01/28/2017
 *
 ***********************************************************************************/
global $wpdb;

if (!function_exists('add_action')) exit;

if (isset($_POST['action']) && 'save_settings' === $_POST['action']) {
	// SAVE SETTINGS
	check_admin_referer('rr_settings_'.$this->rr_version);

	$usepopup = 'N';
	if(isset($_POST['rr_use_popup']))
		$usepopup = sanitize_text_field($_POST['rr_use_popup']);	
	$this->rr_options['rr_wpm']              = sanitize_text_field($_POST['rr_wpm']);
	$this->rr_options['rr_minimum_words']    = sanitize_text_field($_POST['rr_minimum_words']);
	$this->rr_options['rr_use_popup']        = $usepopup;
	$this->rr_options['rr_cont_bgcolor']     = sanitize_text_field($_POST['rr_cont_bgcolor']);
	$this->rr_options['rr_cont_bordercolor'] = sanitize_text_field($_POST['rr_cont_bordercolor']);
	$this->rr_options['rr_textcolor']        = sanitize_text_field($_POST['rr_textcolor']);
	$this->rr_options['rr_bgcolor']          = sanitize_text_field($_POST['rr_bgcolor']);	
	$this->rr_options['rr_bordercolor']      = sanitize_text_field($_POST['rr_bordercolor']);	
	$this->rr_options['rr_fpc']              = sanitize_text_field($_POST['rr_fpc']);	

	update_option('rr_options', $this->rr_options);

	echo "<div class='updated'><p><strong>".__('Rocket Reader SETTINGS UPDATED!','rocket-reader-speed-reader')."</strong></p></div>";
}
else if (isset($_POST['action']) && ($_POST['action']=='show_all' || $_POST['action']=='hide_all')) {
	// FROM v1.2.2
	$sql = "
	SELECT `ID`
	  FROM $wpdb->posts
	 WHERE (`post_type` = 'page' OR `post_type` = 'post')
	   AND `post_status` = 'publish'
	";
	$results = $wpdb -> get_results($sql);
	for ($i=0; $i<count($results); $i++)
	{
		// DELETE DEPRECIATED SETTING (from v1.2.2)
		delete_post_meta($results[$i]->ID, 'disable_rocket_reader');
		if($_POST['action']=='show_all') {
			update_post_meta($results[$i]->ID, 'enable_rocket_reader', 'Y');
		} else {
			delete_post_meta($results[$i]->ID, 'enable_rocket_reader');
		}
	}
	if($_POST['action']=='show_all')
		$msg = 'The Rocket Reader has been ADDED to ALL posts and pages';
	else
		$msg = 'The Rocket Reader has been DELETED from ALL posts and pages';
?>
<script type="text/javascript">alert('<?php echo $msg;?>');</script>
<?php
} // // if (isset($_POST['action']) && 'save_settings' === $_POST['action'])
?>

<div class="rr-title-bar">
  <h2><?php _e( 'Rocket Reader - Settings', 'rocket-reader-speed-reader' ); ?></h2>
</div>
<div class="rr-intro"><?php _e( 'Plugin version', 'rocket-reader-speed-reader' ); ?>: v<?php echo $this->rr_version?> [<?php echo $this->rr_release_date?>]
-
<a href="http://cagewebdev.com/rocket-reader/" target="_blank"><?php _e( 'Plugin page', 'rocket-reader-speed-reader' ); ?></a>
-
<a href="https://wordpress.org/plugins/rocket-reader-speed-reader/" target="_blank"><?php _e( 'Download page', 'rocket-reader-speed-reader' ); ?></a>
-
<a href="http://rvg.cage.nl/" target="_blank"><?php _e( 'Author', 'rocket-reader-speed-reader' ); ?></a>
-
<a href="http://cagewebdev.com/" target="_blank"><?php _e( 'Company', 'order-your-posts-manually' ); ?></a>
-
<a href="http://cagewebdev.com/index.php/donations-rr/" target="_blank"><?php _e( 'Donation page', 'rocket-reader-speed-reader' ); ?></a>
</div>
<div id="rr-settings-form">
  <form action="" method="post" name="rr-settings" id="rr-settings" >
    <?php wp_nonce_field('rr_settings_'.$this->rr_version); ?>
    <input name="action" type="hidden" value="save_settings" />
    <label for="rr_wpm">
      <?php _e('Initial <strong>Number Of Words Per Minute</strong> (will be overridden by the user\'s cookie, if set)','rocket-reader-speed-reader')?>
      :</label>
    <br />
    <input type="text" name="rr_wpm" id="rr_wpm" size="8" value="<?php echo $this->rr_options['rr_wpm']; ?>" />
    <br />
    <!-- Since v1.5.0 -->
    <label for="rr_minimum_words">
      <?php _e('Minimum <strong>Number of Words</strong> per post / page for showing the Rocket Reader button (\'0\' means NO minimum)','rocket-reader-speed-reader')?>
      :</label>
    <br />
    <input type="text" name="rr_minimum_words" id="rr_minimum_words" size="8" value="<?php echo $this->rr_options['rr_minimum_words']; ?>" />
    <br />
    <label for="rr_use_popup">
      <?php _e('Use a <strong>Popup Window</strong> for displaying the animated text','rocket-reader-speed-reader')?>
      :</label>
    <br />
    <input name="rr_use_popup" id="rr_use_popup" type="checkbox" value="Y" />
    <br />
    <br />
    <hr />
    <br />
    <span class="rr-dark-blue">
    <?php _e('The following colors should be in "#FFF" or "#FFFFFF" format!','rocket-reader-speed-reader')?>
    </span> <br />
    <br />
    <label for="rr_cont_bgcolor"><strong>
      <?php _e('Background color container','rocket-reader-speed-reader')?>
      :</strong></label>
    <br />
    <input type="text" name="rr_cont_bgcolor" id="rr_cont_bgcolor" size="8" value="<?php echo $this->rr_options['rr_cont_bgcolor']; ?>" />
    <br />
    <label for="rr_cont_bordercolor"><strong>
      <?php _e('Border color container','rocket-reader-speed-reader')?>
      :</strong></label>
    <br />
    <input type="text" name="rr_cont_bordercolor" id="rr_cont_bordercolor" size="8" value="<?php echo $this->rr_options['rr_cont_bordercolor']; ?>" />
    <br />
    <label for="rr_textcolor"><strong>
      <?php _e('Text color','rocket-reader-speed-reader')?>
      :</strong></label>
    <br />
    <input type="text" name="rr_textcolor" id="rr_textcolor" size="8" value="<?php echo $this->rr_options['rr_textcolor']; ?>" />
    <br />
    <label for="rr_bgcolor"><strong>
      <?php _e('Background color','rocket-reader-speed-reader')?>
      :</strong></label>
    <br />
    <input type="text" name="rr_bgcolor" id="rr_bgcolor" size="8" value="<?php echo $this->rr_options['rr_bgcolor']; ?>" />
    <br />
    <label for="rr_bordercolor"><strong>
      <?php _e('Border color','rocket-reader-speed-reader')?>
      :</strong></label>
    <br />
    <input type="text" name="rr_bordercolor" id="rr_bordercolor" size="8" value="<?php echo $this->rr_options['rr_bordercolor']; ?>" />
    <br />
    <label for="rr_fpc"><strong>
      <?php _e('Focal point color','rocket-reader-speed-reader')?>
      :</strong></label>
    <br />
    <input type="text" name="rr_fpc" id="rr_fpc" size="8" value="<?php echo $this->rr_options['rr_fpc']; ?>" />
    <br />
    <br />
    <input name="btn_save" type="submit" value="<?php _e('Save Settings','rocket-reader-speed-reader')?>" class="button-primary button-large" />
    <input name="btn_cancel" type="button" value="<?php _e('Cancel','rocket-reader-speed-reader')?>" class="button" onclick="history.go(-1);" />
  </form>
  <?php
  if($this->rr_options['rr_use_popup'] == 'Y') {
  ?>
  <script type="text/javascript">
jQuery("#rr_use_popup").prop("checked", true);
</script>
  <?php
  } // if($this->rr_options['rr_use_popup'] == 'Y')
  ?>
  <br />
  <hr />
  <br />
  <?php
	// FROM v1.2.2
?>
  <div id="rr-add-delete-all"> <span class="rr-dark-blue">
    <?php _e('A quick way to ADD or DELETE the Rocket Reader to / from ALL posts / pages','rocket-reader-speed-reader')?>
    :</span><br />
    (
    <?php _e('To show the Rocket Reader on a specific post / page: add a custom field named <strong>enable_rocket_reader</strong> and give it the value <strong>Y</strong>','rocket-reader-speed-reader')?>
    ) <br />
    <br />
    <table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><form action="" method="post" name="show_all_form">
            <input name="action" type="hidden" value="show_all" />
            <input name="btn_save_sa" type="submit" value="<?php _e('ADD TO ALL','rocket-reader-speed-reader')?>" class="button-primary button-large" />
          </form></td>
        <td>&nbsp;&nbsp;&nbsp;</td>
        <td><form action="" method="post" name="hide_all_form">
            <input name="action" type="hidden" value="hide_all" />
            <input name="btn_save_ha" type="submit" value="<?php _e('DELETE FROM ALL','rocket-reader-speed-reader')?>" class="button-primary button-large" />
          </form></td>
      </tr>
    </table>
  </div>
</div>
