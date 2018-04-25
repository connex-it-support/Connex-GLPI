	<?php

	/*
	 * @version $Id: login.php 10996 2010-03-11 18:17:19Z moyo $
	 -------------------------------------------------------------------------
	 GLPI - Gestionnaire Libre de Parc Informatique
	 Copyright (C) 2003-2010 by the INDEPNET Development Team.

	 http://indepnet.net/   http://glpi-project.org
	 -------------------------------------------------------------------------

	 LICENSE

	 This file is part of GLPI.

	 GLPI is free software; you can redistribute it and/or modify
	 it under the terms of the GNU General Public License as published by
	 the Free Software Foundation; either version 2 of the License, or
	 (at your option) any later version.

	 GLPI is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details.

	 You should have received a copy of the GNU General Public License
	 along with GLPI; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	 --------------------------------------------------------------------------
	 */

	// ----------------------------------------------------------------------
	// Original Author of file:
	// Purpose of file:
	// ----------------------------------------------------------------------

	define('GLPI_ROOT', '..'); 

	
	//Load GLPI constants
	include ("../inc/based_config.php");
	include_once ("../inc/define.php");
	session_start();

	// Check PHP version not to have trouble
	if (version_compare(PHP_VERSION, GLPI_MIN_PHP) < 0) {
	   die(sprintf("PHP >= %s required", GLPI_MIN_PHP));
	}

	define('DO_NOT_CHECK_HTTP_REFERER', 1);

	// If config_db doesn't exist -> start installation
	if (!file_exists(GLPI_CONFIG_DIR . "/config_db.php")) {
	   include_once (GLPI_ROOT . "/inc/autoload.function.php");
	   Html::redirect("install/install.php");
	   die();

	} else {
	   $TRY_OLD_CONFIG_FIRST = true;
	   include (GLPI_ROOT . "/inc/includes.php");
	   $_SESSION["glpicookietest"] = 'testcookie';

	   // For compatibility reason
	   if (isset($_GET["noCAS"])) {
		  $_GET["noAUTO"] = $_GET["noCAS"];
	   }

	   if (!isset($_GET["noAUTO"])) {
		  Auth::redirectIfAuthenticated();
	   }
	   Auth::checkAlternateAuthSystems(true, isset($_GET["redirect"])?$_GET["redirect"]:"");

	}

if (!isset($_SESSION["glpicookietest"]) || ($_SESSION["glpicookietest"] != 'testcookie')) {
   if (!is_writable(GLPI_SESSION_DIR)) {
      Html::redirect($CFG_GLPI['root_doc'] . "/index.php?error=2");
   } else {
      Html::redirect($CFG_GLPI['root_doc'] . "/index.php?error=1");
   }
}






	$_POST = array_map('stripslashes', $_POST);


	//Do login and checks
	//$user_present = 1;

	if (!isset($_POST['login_name'])) {
	   $_POST['login_name'] = '';
	}

	if (isset($_POST['login_password']) && strlen($_POST['login_password']) > 0 ){


	   $_POST['login_password'] = Toolbox::unclean_cross_side_scripting_deep($_POST['login_password']);

	} else {
	   $_POST['login_password'] = '';

	}

	// Redirect management

	$REDIRECT = "";
	if (isset ($_POST['redirect']) && strlen($_POST['redirect'])>0) {
	   $REDIRECT = "?redirect=" .$_POST['redirect'];
	} else if (isset ($_GET['redirect']) && strlen($_GET['redirect'])>0) {
	   $REDIRECT = "?redirect=" .$_GET['redirect'];
	}




	$auth = new Auth();
	   //print_r($_POST);
	// now we can continue with the process...
 echo '<p id = "test3"> </p>';
	if ($auth->login($_POST['login_name'], $_POST['login_password'],false) ) {
			
		
		
		echo '<script type="text/javascript">
		document.getElementById("proceed").value = "true";

		</script>';
		
	} else {	
		echo 'login failed';

	
	}

	?>
