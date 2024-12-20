<?php

/**
 *
 * Script to verify userentered input and verify it against database
 *
 * If successful write values to session and go to main page!
 *
 */


/* functions */
require_once( dirname(__FILE__) . '/../../functions/functions.php' );


# initialize user object
$Database 	= new Database_PDO;
$User 		= new User ($Database);
$Result 	= new Result ();
$Log 		= new Logging ($Database);

# set default language
if(isset($User->settings->defaultLang) && !is_null($User->settings->defaultLang) ) {
    # get global default language
    $lang = $User->get_default_lang();
    if (is_object($lang))
        set_ui_language($lang->l_code);
}

# Authenticate
if( !empty($POST->ipamusername) && !empty($POST->ipampassword) )  {

	# initialize array
	$ipampassword = array();

	# check failed table
	$cnt = $User->block_check_ip ();

	# check for failed logins and captcha
	if($User->blocklimit > $cnt) {
		// all good
	}
	# count set, captcha required
	elseif(!isset($POST->captcha)) {
		$Log->write( _("Login IP blocked"), _("Login from IP address")." ".$_SERVER['REMOTE_ADDR']." "._("was blocked because of 5 minute block after 5 failed 2fa attempts"), 1);
		$Result->show("danger", _('You have been blocked for 5 minutes due to authentication failures'), true);
	}
	# captcha check
	else {
		# check captcha
		$captcha_code = isset($_SESSION['securimage_code_value']) ? $_SESSION['securimage_code_value']['default'] : '';
		if ($captcha_code == '') {
			$Result->show("danger", _("Missing security code"), true);
		}
		if(strtolower($POST->captcha)!=strtolower($captcha_code)) {
			$Result->show("danger", _("Invalid security code"), true);
		}
	}

	# all good, try to authentucate user
	$User->authenticate($POST->ipamusername, $POST->ipampassword);
}
# Username / pass not provided
else {
	$Result->show("danger", _('Please enter your username and password'), true);
}
