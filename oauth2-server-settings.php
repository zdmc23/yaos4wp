<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( !function_exists( 'add_action' ) ) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}

global $chk;
if(isset($_POST['wphw_submit'])){
  wphw_opt();
}
function wphw_opt(){
  $hellotxt = $_POST['footertextname'];
  global $chk;
  if( get_option('footer_text') != trim($hellotxt)){
    $chk = update_option( 'footer_text', trim($hellotxt));
  }
}
?>
<div class="wrap">
  <div id="icon-options-general" class="icon32"> <br>
  </div>
  <h2>Plugin Settings</h2>
  <?php if(isset($_POST['wphw_submit']) && $chk):?>
  <div id="message" class="updated below-h2">
    <p>Content updated successfully</p>
  </div>
  <?php endif;?>
  <div class="metabox-holder">
    <div class="postbox">
      <h3><strong>You can configure below endpoints in your OAuth client.</strong></h3>
      <form method="post" action="">
        <table class="form-table">
          <tr>
            <th scope="row">Authorize Endpoint: </th>
            <td>
              <?php
                echo "http://localhost" . ':' . $_SERVER['SERVER_PORT'] . "/yaos4wp/authorize";
              ?>
            </td>
            <td>
              <?php
                echo "(e.g., http://localhost" . ':' . $_SERVER['SERVER_PORT'] . "/yaos4wp/authorize?response_type=code&client_id=myawesomeapp&scope=basic&state=zz )";
              ?>
            </td>
          </tr>
          <tr>
            <th scope="row">Redirect URI: </th>
            <td>
              <!--
              <input type="text" name="footertextname" value="<?php echo get_option('footer_text');?>" style="width:350px;" />
              -->
              <?php
                echo "http://localhost" . ':' . $_SERVER['SERVER_PORT'] . "/yaos4wp/callback";
              ?>
            </td>
            <td>
              (this is only an example that is coded for; it will print the auth code when redirection occurs. for mobile, you will *need* to use a DeepLink)
            </td>
          </tr>
          <tr>
            <th scope="row">Access Token Endpoint: </th>
            <td>
              <?php
                echo "http://localhost" . ':' . $_SERVER['SERVER_PORT'] . "/yaos4wp/token";
              ?>
            </td>
          </tr>
          <tr>
            <th scope="row">Scope: </th>
            <td>
              basic
            </td>
          </tr>
          <tr>
            <th scope="row">&nbsp;</th>
            <td style="padding-top:10px;  padding-bottom:10px;">
              <input type="submit" name="wphw_submit" value="Save changes" class="button-primary" />
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
