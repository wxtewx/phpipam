<?php
# verify that user is logged in
$User->check_user_session();

// tabs
$tabs = array("subnets", "leases", "reservations");

?>
<div class="DHCP">

<!-- display existing groups -->
<h4><?php print _('DHCP information'); ?></h4>
<hr><br>

<?php if($User->settings->enableDHCP==1 && $User->get_module_permissions ("dhcp")>=User::ACCESS_R) { ?>

    <?php
    # validate DHCP settings - JSON
    if ($Tools->validate_json_string($User->settings->DHCP)===false) {
        $Result->show("danger", "Error parsing DHCP settings: ".$Tools->json_error, false);
    }
    else {
        # parse and verify settings
        $dhcp_db = db_json_decode($User->settings->DHCP, true);

        # DHCP wrapper class
        $DHCP	= new DHCP ($dhcp_db['type'], $dhcp_db['settings']);

        // read config
        $config = $DHCP->read_config ();
        ?>
        <!-- tabs -->
        <ul class="nav nav-tabs">
        	<?php
        	// default tab
        	if(!isset($GET->subnetId)) {
        		$GET->subnetId = "subnets";
        	}

        	// check
        	if(!in_array($GET->subnetId, $tabs)) 	{ $Result->show("danger", _("Invalid request"), true); }

        	// print
        	foreach($tabs as $t) {
        		$title = str_replace('_', ' ', $t);
        		$class = $GET->subnetId==$t ? "class='active'" : "";
        		print "<li role='presentation' $class><a href=".create_link("tools", "dhcp", "$t").">". _(ucwords(str_replace("_", " ", $title)))."</a></li>";
        	}
        	?>
        </ul>

        <div>
        <?php
        // include content
        $filename = $GET->subnetId . ".php";

        // include file
        if(!file_exists(dirname(__FILE__) . $filename)) 	{ $Result->show("danger", _("Invalid request"), true); }
        else											    { include(dirname(__FILE__) . $filename); }
        ?>
        </div>
<?php
}
} else {
    if($User->get_module_permissions ("dhcp")==User::ACCESS_NONE) {
        $Result->show("danger", _("You do not have permissions to access this module"), false);
    }
    else {
    	$Result->show("info", _('Please enable DHCP module under server management'), false);
    }
}
?>
</div>