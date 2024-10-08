<?php

# functions
require_once('../../../functions/functions.php');

# Classes
$Database = new Database_PDO;
$User  = new User ($Database);
$Result = new Result;
$Password_check = new Password_check ();

# user must be authenticated
$User->check_user_session ();

#CSRF
$User->Crypto->csrf_cookie ("validate", "pass-change", $POST->csrf_cookie) === false ? $Result->show("danger", _("Invalid CSRF cookie"), true) : "";

# Check old password
if(!hash_equals($User->user->password, crypt($POST->oldpassword, $User->user->password))) { $Result->show("danger", _("Invalid password"), true); }

# Check new password != old password
if($POST->ipampassword1==$POST->oldpassword) { $Result->show("danger", _("New password must be different"), true); }

# Enforce password policy
$policy = (db_json_decode($User->settings->passwordPolicy, true));
$Password_check->set_requirements($policy, pf_explode(",",$policy['allowedSymbols']));
if (!$Password_check->validate ($POST->ipampassword1)) { $Result->show("danger alert-danger ", _('Password validation errors').":<br> - ".implode("<br> - ", $Password_check->get_errors ()), true); }

if($POST->ipampassword1!=$POST->ipampassword2) { $Result->show("danger", _("New passwords do not match"), true); }

# update pass
$User->update_user_pass($POST->ipampassword1);
