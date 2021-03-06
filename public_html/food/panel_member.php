<?php
include_once 'config_openfood.php';
session_start();
valid_auth('member');

// So paypal_utilities knows this is a local request and not a paypal request
$not_from_paypal = true;
include_once ('paypal_utilities.php');
$basket_status = '';

// Do we need to post membership changes?
if (isset ($_POST['update_membership']) && $_POST['update_membership'] == 'true')
  {
    include_once ('func.check_membership.php');
    renew_membership ($_SESSION['member_id'], $_POST['membership_type_id']);
    // Now update our session membership values
    $membership_info = get_membership_info ($_SESSION['member_id']);
    $_SESSION['renewal_info'] = check_membership_renewal ($membership_info);
    // Make sure this function does not run again from the template_header.php
    $_POST['update_membership'] = 'false';
  }

/////////////// FINISH PRE-PROCESSING AND BEGIN PAGE GENERATION /////////////////

// Generate the display output
$display = '
  <table width="100%" class="compact">
    <tr valign="top">
      <td align="left" width="50%">
    <img src="'.DIR_GRAPHICS.'type.png" width="32" height="32" align="left" hspace="2" alt="Membership Type">
    <strong>Membership Type</strong> [<a onClick="popup_src(\''.PATH.'update_membership.php?display_as=popup\', \'membership_renewal\', \'\');">Change</a>]
        <ul class="fancyList1">
          <li><strong>'.$_SESSION['renewal_info']['membership_class'].':</strong> '.$_SESSION['renewal_info']['membership_description'].'<br><br></li>
          <li class="last_of_group">'.$_SESSION['renewal_info']['membership_message'].'</li>
        </ul>
    <img src="'.DIR_GRAPHICS.'time.png" width="32" height="32" align="left" hspace="2" alt="Information">
    <strong>Next Renewal Date</strong>
        <ul class="fancyList1">
          <li class="last_of_group">'.date('F j, Y', strtotime($_SESSION['renewal_info']['standard_renewal_date'])).'</li>
        </ul>
    <img src="grfx/docs.png" width="32" height="32" align="left" hspace="2" alt="Documentation"><br>
    <strong>Documentation</strong>
        <ul class="fancyList1">
          <li class="last_of_group">[<a onClick="popup_src(\''.PATH.'motd.php?display_as=popup\', \'motd\', \'\');">Message of the day</a>]</li>
        </ul>
      </td>
      <td align="left" width="50%">
        <img src="'.DIR_GRAPHICS.'status.png" width="32" height="32" align="left" hspace="2" alt="Member Resources">
        <b>Member Resources</b>
        <ul class="fancyList1">
          <li><a href="locations.php">Food Pickup/Delivery Locations</a></li>
          <li><a href="contact.php">How to Contact Us with Questions</a></li>
          <li><a href="member_form.php">Update Membership Info.</a></li>
          <li><a href="reset_password.php">Change Password</a></li>
          <li><a href="faq.php">How to Order FAQ</a></li>
          <li class="last_of_group"><a href="producer_form.php?action=new_producer">New Producer Application Form</a></li>
        </ul>
        <img src="'.DIR_GRAPHICS.'money.png" width="32" height="32" align="left" hspace="2" alt="Payment Options">
        <b>Payment Options</b>
        <ul class="fancyList1">'.
        // Only show PayPal if PayPal is enabled and if there is a real member_id
        (PAYPAL_ENABLED && $_SESSION['member_id'] ? 
          paypal_display_form (array (
            'form_id' => 'paypal_form2',
            'span1_content' => '<li class="last_of_group"><strong>Pay with PayPal &nbsp; &nbsp;</strong><div class="paypal_message">(enter amount at PayPal)</div>',
            'span2_content' => '',
            'form_target' => 'paypal',
            'allow_editing' => false,
            'amount' => number_format (0, 2),
            'business' => PAYPAL_EMAIL,
            'item_name' => htmlentities (ORGANIZATION_ABBR.' '.$_SESSION['member_id'].' '.$_SESSION['show_name']),
            'notify_url' => BASE_URL.PATH.'paypal_utilities.php',
            'custom' => htmlentities ('member#'.$_SESSION['member_id']),
            'no_note' => '0',
            'cn' => 'Message:',
            'cpp_cart_border_color' => '#3f7300',
            'cpp_logo_image' => BASE_URL.DIR_GRAPHICS.'logo1_for_paypal.png',
            'return' => BASE_URL.PATH.'panel_member.php',
            'cancel_return' => BASE_URL.PATH.'panel_member.php',
            'rm' => '2',
            'cbt' => 'Return to '.SITE_NAME,
            'paypal_button_src' => 'https://www.paypal.com/en_US/i/btn/btn_buynow_SM.gif'
            )).'</li>'
          : '').'
          <li class="last_of_group">
            <strong>Mail a check to :</strong><br><br>
            '.SITE_MAILING_ADDR.'<br><br>
            (Indicate &quot;Member #'.$_SESSION['member_id'].'&quot; on payment)
          </li>
        </ul>
        </td>
      </tr>
    </table>';

$page_specific_css = '
  <style type="text/css">
    .paypal_message {
      font-size:70%;
      margin:0.5em 0 1em;
      }
  </style>';

$page_title_html = '<span class="title">'.$_SESSION['show_name'].'</span>';
$page_subtitle_html = '<span class="subtitle">Member Panel</span>';
$page_title = 'Member Panel';
$page_tab = 'member_panel';

include("template_header.php");
echo '
  <!-- CONTENT BEGINS HERE -->
  '.$display.'
  <!-- CONTENT ENDS HERE -->';
include("template_footer.php");