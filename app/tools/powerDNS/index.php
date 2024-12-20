<?php
# verify that user is logged in
$User->check_user_session();
?>

<!-- display existing groups -->
<h4><?php print _('PowerDNS management'); ?></h4>
<hr><br>

<?php
# check permissions
if ($User->get_module_permissions ("pdns")>=User::ACCESS_R) {
?>
    <div class="powerDNS">

    <?php if($User->settings->enablePowerDNS==1) { ?>

        <?php
        # powerDNS class
        $PowerDNS = new PowerDNS ($Database);

        // check connection
        $test = $PowerDNS->db_check();
        // save settings for powerDNS default
        $pdns = $PowerDNS->db_settings;

        // check if TTL is set
        if ($test!==false) {
            $test_ttl = db_json_decode($User->settings->powerDNS);
            if ($test_ttl->ttl==NULL) {
                $link = function() {
                    return create_link("administration", "powerDNS", "defaults");
                };
                $Result->show("warning", tr_("Please set <a href='%s'> default powerDNS values </a>!", $link), false);
            }
        }
        // errors
        if(isset($PowerDNS->db_check_error)) {
            foreach ($PowerDNS->db_check_error as $err) {
                $Result->show("warning", $err);
            }
        }
        ?>
        <!-- tabs -->
        <ul class="nav nav-tabs">
        	<?php
        	// tabs
        	$tabs = array("domains", "host_records", "reverse_v4", "reverse_v6");

        	// default tab
        	if(!isset($GET->subnetId)) {
        		$GET->subnetId = "domains";
        	}

        	// check
        	if(!in_array($GET->subnetId, $tabs)) 	{ $Result->show("danger", _("Invalid request"), true); }

        	// print
        	foreach($tabs as $t) {
        		$title = str_replace('_', ' ', $t);
        		$class = $GET->subnetId==$t ? "class='active'" : "";
        		print "<li role='presentation' $class><a href=".create_link("tools", "powerDNS", "$t").">". _(ucwords($title))."</a></li>";
        	}
        	?>
        </ul>

        <div>
        <?php
        // include content
        $pdns_section = $GET->subnetId;
        if (preg_match("/reverse_/", $pdns_section)) {
        	$filename = 'domains.php';
        } else {
        	$filename = $GET->subnetId.".php";
        }

        // include file
        if(!file_exists(dirname(__FILE__) . '/'.$filename)) 	{ $Result->show("danger", _("Invalid request"), true); }
        else													{ include(dirname(__FILE__) . '/'.$filename); }
        ?>
        </div>

    <?php
    } else {
    	$Result->show("info", _('Please enable powerDNS module under server management'), false);
    }
    ?>
    </div>
    <?php
}
else {
    $Result->show("danger", _("You do not have permissions to access this module"), false);
}