<?php
/*
 
 ----------------------------------------------------------------------
GLPI - Gestionnaire libre de parc informatique
 Copyright (C) 2002 by the INDEPNET Development Team.
 http://indepnet.net/   http://glpi.indepnet.org
 ----------------------------------------------------------------------
 Based on:
IRMA, Information Resource-Management and Administration
Christian Bauer, turin@incubus.de 

 ----------------------------------------------------------------------
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
 ----------------------------------------------------------------------
 Original Author of file:
 Purpose of file:
 ----------------------------------------------------------------------
*/

include ("_relpos.php");
// FUNCTIONS Tracking System


function searchFormTracking ($show,$contains) {
	// Tracking Search Block
	
	GLOBAL $cfg_layout, $cfg_install,$lang;
	
	echo "\n<center>";
	echo "<table border='0'>";
	echo "<tr><th align='center' colspan=3>".$lang["tracking"][0].":</th></tr>";

	echo "<form method=\"get\" action=\"".$cfg_install["root"]."/tracking/index.php\">";
	echo "<tr class='tab_bg_1'>";
	echo "<td colspan=2 align='center'>";
	echo "<select name=\"show\" size=1>";

	echo "<option "; if ($show == "all") { echo "selected"; }
	echo " value=\"all\">".$lang["tracking"][1]."</option>";

	echo "<option "; if ($show == "individual") { echo "selected"; }
	echo " value=\"individual\">".$lang["tracking"][2]."</option>";

	echo "<option "; if ($show == "unassigned") { echo "selected"; }
	echo " value=\"unassigned\">".$lang["tracking"][3]."</option>";

	echo "<option "; if ($show == "old") { echo "selected"; }
	echo " value=\"old\">".$lang["tracking"][4]."</option>";

	echo "</select>";
	echo "</td>";
	echo "<td align='center'><input type=submit value=\"".$lang["buttons"][1]."\"></td>";
	echo "</tr>";
	echo "</form>";
	echo "<form method=\"get\" action=\"".$cfg_install["root"]."/tracking/index.php\">";
	echo "<tr class='tab_bg_1'>";
	echo "<td class='tab_bg_2'>";
	echo "<b>".$lang["tracking"][5].":</b> </td><td><input type=text name=contains value=\"$contains\"size=15>";
	echo "</td><td>";
	echo "<input type=submit value=\"".$lang["buttons"][0]."\">";
	echo "</td></tr>";
	echo "</form>";
	echo "</table>\n";
	echo "</center><br>\n";
}

function getTrackingPrefs ($username) {
	// Returns users preference settings for job tracking
	// Currently only supports sort order

	$db = new DB;
	$query = "SELECT tracking_order FROM prefs WHERE (user = '$username')";
	$result = $db->query($query);
	$tracking_order = $db->result($result, 0, "tracking_order");

	if($tracking_order == "yes")
	{
		$prefs["order"] = "ASC";
	} 
	else
	{
		$prefs["order"] = "DESC";
	}

	return $prefs;
}

function showJobList($username,$show,$contains,$item) {
	// Lists all Jobs, needs $show which can have keywords 
	// (individual, unassigned) and $contains with search terms.
	// If $item is given, only jobs for a particular machine
	// are listed.

	GLOBAL $cfg_layout, $cfg_install, $lang;
		
	$prefs = getTrackingPrefs($username);

	// Build where-clause
	if ($contains)
	{
		$where = "(contents LIKE \"%$contains%\")";
	}
	else if ($show == "old")
	{
		$where = "(status = 'old')";
	}
	else
	{
		$where = "(status = 'new')";
	}


	// Build query, two completely different things here, need to be fixed
	// and made into a more featured query-parser

	if ($show == "individual")
	{
		$query = "SELECT ID FROM tracking WHERE $where and (assign = '$username') ORDER BY date ".$prefs["order"]."";
	}
	else if ($show == "user")
	{
		$query = "SELECT ID FROM tracking WHERE $where and (author = '$username') ORDER BY date ".$prefs["order"]."";
	}
	else if ($show == "unassigned")
	{
		$query = "SELECT ID FROM tracking WHERE $where and (assign is null) ORDER BY date ".$prefs["order"]."";
	}
	else
	{
		$query = "SELECT ID FROM tracking WHERE $where ORDER BY date ".$prefs["order"]."";
	}

	if ($item)
	{
		$query = "SELECT ID FROM tracking WHERE $where and (computer = '$item') ORDER BY date ".$prefs["order"]."";
	}	
		

	$db = new DB;
	$result = $db->query($query);

	$i = 0;
	$number = $db->numrows($result);

	if ($number > 0) {
		echo "<center><table border='0' width='90%'>";
		echo "<tr><th colspan=8>$number Job";
		if ($number > 1) { echo "s"; }
		echo " ".$lang["job"][16].":</th></tr>";
		echo "<tr><th>".$lang["joblist"][0]."</th><th>".$lang["joblist"][1]."</th>";
		echo "<th width=5>".$lang["joblist"][2]."</th><th>".$lang["joblist"][3]."</th>";
		echo "<th>".$lang["joblist"][4]."</th><th>".$lang["joblist"][5]."</th>";
		echo "<th colspan=2>".$lang["joblist"][6]."</th></tr>";
		while ($i < $number) {
			$ID = $db->result($result, $i, "ID");
			showJobShort($ID, 0);
			$i++;
		}
		if ($item)
		{
			echo "<tr class='tab_bg_2'>";
			echo "<td align='center' colspan=8 class='tab_bg_1'><b>";
			echo "<a href=\"".$cfg_install["root"]."/tracking/tracking-add-form.php?ID=$item\">";
			echo $lang["joblist"][7];
			echo "</a>";
			echo "</b></td></tr>";
		}
		echo "</table></center>";
	}
	else
	{
		echo "<br><center>";
		echo "<table border='0' width='90%'>";
		echo "<tr><th>".$lang["joblist"][8]."</th></tr>";

		if ($item) 
		{
			echo "<tr><td align='center' class='tab_bg_1'><b>";
			echo "<a href=\"".$cfg_install["root"]."/tracking/tracking-add-form.php?ID=$item\">";
			echo $lang["joblist"][7];
			echo "</a>";
			echo "</b></td></tr>";
		}
		echo "</table>";
		echo "</center><br>";
	}
}

function showOldJobListForItem($username,$contains,$item) {
	// $item is required
	//affiche toutes les vielles intervention pour un $item donn�. 


	GLOBAL $cfg_layout, $cfg_install, $lang;
		
	$prefs = getTrackingPrefs($username);
	
$where = "(status = 'old')";	
$query = "SELECT ID FROM tracking WHERE $where and (computer = '$item') ORDER BY date ".$prefs["order"]."";
	

	$db = new DB;
	$result = $db->query($query);

	$i = 0;
	$number = $db->numrows($result);

	if ($number > 0)
	{
		echo "<center><table border='0' width='90%'>";
		echo "<tr><th colspan=8>$number anciennes ".$lang["job"][17]."";
		if ($number > 1) { echo "s"; }
		echo " ".$lang["job"][16].":</th></tr>";
		echo "<tr><th>".$lang["joblist"][0]."</th><th>".$lang["joblist"][1]."</th>";
		echo "<th width=5>".$lang["joblist"][2]."</th><th>".$lang["joblist"][3]."</th>";
		echo "<th>".$lang["joblist"][4]."</th><th>".$lang["joblist"][5]."</th>";
		echo "<th colspan=2>".$lang["joblist"][6]."</th></tr>";
		while ($i < $number)
		{
			$ID = $db->result($result, $i, "ID");
			showJobShort($ID, 0);
			$i++;
		}
		if ($item)
		{
			echo "<tr class='tab_bg_2'>";
			echo "<td align='center' colspan=8 class='tab_bg_1'><b>";
		}
		echo "</table></center>";
	} 
	else
	{
		echo "<br><center>";
		echo "<table border='0' width='90%'>";
		echo "<tr><th>".$lang["joblist"][22]."</th></tr>";

		if ($item)
		{
			echo "<tr><td align='center' class='tab_bg_1'><b>";
		}
		echo "</table>";
		echo "</center><br>";
	}
}


function showJobShort($ID, $followup) {
	// Prints a job in short form
	// Should be called in a <table>-segment
	// Print links or not in case of user view

	GLOBAL $cfg_layout, $cfg_install, $cfg_features, $lang;

	// Make new job object and fill it from database, if success, print it

	$job = new Job;
	
	if ($job->getfromDB($ID,0))
	{

		if ($job->status == "new")
		{
			echo "<tr class='tab_bg_1'>";
			echo "<td align='center'><font color=\"green\"><b>".$lang["joblist"][9]."</b></font></td>";
			echo "<td width='30%'><small>".$lang["joblist"][11].":<br>&nbsp;$job->date</small></td>";

		}
		else
		{
 			echo "<tr class='tab_bg_1'>";
			echo "<td align='center'><b>".$lang["joblist"][10]."</b></td>";
			echo "<td width='30%'><small>".$lang["joblist"][11].":<br>&nbsp;$job->date<br>";
			echo "<i>".$lang["joblist"][12].":<br>&nbsp;$job->closedate</i></small></td>";
		}

		echo "<td align='center'><b>$job->priority</b></td>";
		
		echo "<td align='center'><b>";

		if (strcmp($_SESSION["glpitype"],"post-only")!=0)
		echo "<a href=\"".$cfg_install["root"]."/setup/users-info.php?ID=$job->author\">$job->author</a>";
		else
		echo "$job->author";

		echo "</b></td>";

		if ($job->assign == "")
		{
			echo "<td align='center'>[Nobody]</td>"; 
	    	}
		else
		{
			echo "<td align='center'><b>$job->assign</b></td>";
		}    
		
		if (strcmp($_SESSION["glpitype"],"post-only")!=0)
		echo "<td><a href=\"".$cfg_install["root"]."/computers/computers-info-form.php?ID=$job->computer\"><b>$job->computername ($job->computer)</b></a></td>";
		else
		echo "<td><b>$job->computername($job->computer)</b></td>";

		$stripped_content = substr($job->contents,0,$cfg_features["cut"]);
		echo "<td><b>$stripped_content</b></td>";

		// Job Controls
		echo "<td width=10% class='tab_bg_2' align='center'>";
		
		if (strcmp($_SESSION["glpitype"],"post-only")!=0)
		echo "<b><a href=\"".$cfg_install["root"]."/tracking/tracking-followups.php?ID=$job->ID\">".$lang["joblist"][13]."</a>&nbsp;($job->num_of_followups)&nbsp;<br>";
		else
		echo "<b><a href=\"".$cfg_install["root"]."/helpdesk.php?show=user&ID=$job->ID\">".$lang["joblist"][13]."</a>&nbsp;($job->num_of_followups)&nbsp;<br>";
		if ($job->status == "new"&&strcmp($_SESSION["glpitype"],"post-only")!=0)
		{
			echo "<a href=\"".$cfg_install["root"]."/tracking/tracking-mark.php?ID=$job->ID\">".$lang["joblist"][14]."</a><br>";
		}
		if(strcmp($_SESSION["glpitype"],"post-only")!=0)
		echo "<a href=\"".$cfg_install["root"]."/tracking/tracking-assign-form.php?ID=$job->ID\">".$lang["joblist"][15]."</a></b></td>";

		// Finish Line
		echo "</tr>";

		if (isset($followups))
		{
			echo "<tr><th>&nbsp;</th><td colspan=7>";
			showFollowups($job->ID);
			echo "</td></tr>"; 
		}
	
	}
	else
	{
    		echo "<tr class='tab_bg_2'><td colspan=6><i>".$lang["joblist"][16]."</i></td></tr>";
	}
}

function showJobDetails($ID) {
	// Prints a job in long form with all followups and stuff

	GLOBAL $cfg_install, $cfg_layout, $cfg_features, $lang;
	
	// Make new job object and fill it from database, if success, print it

	$job = new Job;
	
	if ($job->getfromDB($ID,0)) {
		// test if the user if authorized to view this job
		if (strcmp($_SESSION["glpitype"],"post-only")==0&&!strcmp($_SESSION["glpiname"],$job->author)==0)
		   { echo "Warning !! ";return;}

		echo "<center><table border='0' width='90%' cellpadding=5>\n";
		echo "<tr><th colspan=2>".$lang["job"][0]." $job->ID:</th></tr>";
		echo "<tr class='tab_bg_2'>";
		echo "<td width=50% rowspan=2>";

		echo "<table cellpadding=2 cellspacing='0' border='0'>";

		echo "<tr><td>".$lang["joblist"][0].":</td><td>";
		if ($job->status == "new") { 
			echo "<font color=\"green\"><b>".$lang["joblist"][9]."</b></font>"; }
		else {
			echo "<b>".$lang["joblist"][10]."</b>";
		}
		echo "</td></tr>";

		echo "<tr><td>".$lang["joblist"][2]."</td><td><b>";
		echo "<b>".$job->priority."</b></td></tr>";	

		echo "<tr><td>".$lang["joblist"][3]."</td><td>";
		if (strcmp($_SESSION["glpitype"],"post-only")!=0)
		echo "<b><a href=\"".$cfg_install["root"]."/setup/users-info.php?ID=$job->author\">$job->author</a></b>";
		else 
		echo "<b>$job->author</b>";
		echo "</td></tr>";

		echo "<tr><td>".$lang["joblist"][5]."</td><td>";
		if (strcmp($_SESSION["glpitype"],"post-only")!=0)
		echo "<b><a href=\"".$cfg_install["root"]."/computers/computers-info-form.php?ID=$job->computer\">$job->computername ($job->computer)</a></b>";
		else
		echo "<b>computername ($job->computer)</b>";
		echo "</td></tr>";
		echo "</table>";

		echo "</td>";

		echo "<td>";
		echo "<table cellpadding=2 cellspacing='0' border='0'>";
		echo "<tr><td>".$lang["joblist"][11].":</td>";
		echo "<td><b>".$job->date."</b></td></tr>";
		echo "<tr><td>".$lang["joblist"][12].":</td>";
		if ($job->closedate == "0000-00-00 00:00:00" || $job->closedate == "")
		{
			echo "<td><i>".$lang["job"][1]."</i></td></tr>";
		}
		else
		{
			$db = new DB;
			$query = "SELECT SEC_TO_TIME(UNIX_TIMESTAMP('$job->closedate') - UNIX_TIMESTAMP('$job->date'))";
			$result = $db->query($query);
			$opentime = $db->result($result, 0, 0);
			echo "<td><b>$job->closedate</b></tr>";
			echo "<tr><td colspan=2>".$lang["job"][2].": $opentime</td></tr>";
		}
		echo "</table>";
		echo "</td>";
	
		echo "</tr><tr class='tab_bg_2'>";
		echo "<td align='center'>";	

			assignFormTracking($ID,$_SESSION["glpiname"],$cfg_install["root"]."/tracking/tracking-assign-form.php");
		
		echo "</td>";
		
		echo "</tr><tr class='tab_bg_2'>";
		
		echo "<td colspan=2>";
		echo $lang["joblist"][6].":<br><br>";
		echo "<b>$job->contents</b>";
		echo "<br><br></td>";
		
		echo "</tr>";
	
		if (strcmp($_SESSION["glpitype"],"post-only")!=0)
		if ($job->status == "new") {
			echo "<tr class='tab_bg_1'>";
			echo "<td colspan=2 align='center'>";
			echo "<b><a href=\"".$cfg_install["root"]."/tracking/tracking-mark.php?ID=$job->ID\">".$lang["job"][3]."</a></b>";
			echo "</td></tr>";
		}
		echo "</table>";
		echo "<br><br><table width='90%' border='0'><tr><th>".$lang["job"][7].":</th></tr></table>";
		echo "</center>";

		showFollowups($job->ID);
	} 
	else
	{
    		echo "<tr class='tab_bg_2'><td colspan=6><i>".$lang["joblist"][16]."</i></td></tr>";
	}
}

function postJob($ID,$author,$status,$priority,$computer,$isgroup,$uemail,$emailupdates,$contents) {
	// Put Job in database

	GLOBAL $cfg_install, $cfg_features, $cfg_layout;
	
	$job = new Job;

	if (!$isgroup) {
		$job->isgroup = "no";
	} else {
		$job->isgroup = "yes";
	}
	$job->status = $status;
	$job->author = $author;
	$job->computer = $ID;
	$job->contents = $contents;
	$job->priority = $priority;
	$job->uemail = $uemail;
	$job->emailupdates = $emailupdates;
	
	if ($job->putinDB()) {
		// Log this event
		logEvent($ID,"computers",4,"tracking","$author added new job.");
		
		// Processing Email
		if ($cfg_features["mailing"])
		{
			$user=new User;
			$user->getfromDB($author);
			$mail = new Mailing("new",$job,$user);
			$mail->send();
		}
		return true;	
	} else {
		echo "Couldn't post followup.";
		return false;
	}
}

function markJob ($ID,$status) {
	// Mark Job with status
	GLOBAL $cfg_features;
	$job = new Job;
	$job->getFromDB($ID,1);
	$job->updateStatus($status);
	// Processing Email
	if ($status=="old"&&$cfg_features["mailing"])
		{
			$user=new User;
			$user->getfromDB($_SESSION["glpiname"]);
			$mail = new Mailing("finish",$job,$user);
			$mail->send();
		}

}

function assignJob ($ID,$user,$admin) {
	// Assign a job to someone

	GLOBAL $cfg_features, $cfg_layout;	
	$job = new Job;
	$job->getFromDB($ID,0);

	$job->assignTo($user);
	
	// Processing Email
	if ($cfg_features["mailing"])
		{
			$user=new User;
			$user->getfromDB($_SESSION["glpiname"]);
			$mail = new Mailing("attrib",$job,$user);
			$mail->send();
		}
}

function showFollowups($ID) {
	// Print Followups for a job

	GLOBAL $cfg_install, $cfg_layout, $lang;

	// Get Number of Followups

	$job = new Job;
	$job->getFromDB($ID,0);

	if ($job->num_of_followups) {
		echo "<center><table border='0' width='90%' cellpadding=2>\n";
		echo "<tr><th>".$lang["joblist"][1]."</th><th>".$lang["joblist"][3]."</th><th>".$lang["joblist"][6]."</th></tr>\n";

		for ($i=0; $i < $job->num_of_followups; $i++) {
			$fup = new Followup;
			$fup->getFromDB($ID,$i);
			echo "<tr class='tab_bg_2'>";
			echo "<td align='center'>$fup->date</td>";
			echo "<td align='center'>$fup->author</td>";
			echo "<td width=70%><b>$fup->contents</b></td>";
			echo "</tr>";
		}		

		echo "</table></center>";
	
	} else {
		echo "<center><b>".$lang["job"][8]."</b></center>";
	}

	// Show input field only if job is still open
	if(strcmp($_SESSION["glpitype"],"post-only")!=0)
	if ($job->closedate=="0000-00-00 00:00:00") {
		echo "<center><table border='0' width='90%'>\n\n";
		echo "<form method=get action=\"".$cfg_install["root"]."/tracking/tracking-followups.php\">";
		echo "<input type=hidden name=ID value=$ID>";
		echo "<tr><th>".$lang["job"][9].":</th></tr>";
		echo "<tr class='tab_bg_1'><td width='100%' align='center'><textarea cols=60 rows=5 name=contents ></textarea></td></tr>";
		echo "<tr><td align='center' class='tab_bg_1'>";
		echo "<input type=submit value=\"".$lang["buttons"][2]."\"></td>";
		echo "</tr></form></table></center>";
	}

}

function postFollowups ($ID,$author,$contents) {

	GLOBAL $cfg_install, $cfg_features, $cfg_layout;
	
	$fup = new Followup;
	
	$fup->tracking = $ID;
	$fup->author = $author;
	$fup->contents = $contents;
	
	if ($fup->putInDB()) {
		// Log this event
		$fup->logFupUpdate();
		// Processing Email
		if ($cfg_features["mailing"])
		{
			$job= new Job;
			$job->getfromDB($ID,0);
			$user=new User;
			$user->getfromDB($author);
			$mail = new Mailing("followup",$job,$user);
			$mail->send();
		}
	} else {
		echo "Couldn't post followup.";
	}
}

function addFormTracking ($ID,$author,$target,$error) {
	// Prints a nice form to add jobs

	GLOBAL $cfg_layout, $lang;
	
	if ($error) {
		echo "<center><b>$error</b></center>";
	}
	echo "<form method=get action=$target>";
	echo "<center><table border='0'>";
	echo "<tr><th colspan=2>".$lang["job"][13].":</th></tr>";

	echo "<tr class='tab_bg_2'><td>".$lang["joblist"][1].":</td>";
	echo "<td align='center'>".date("Y-m-d H:i:s")."</td></tr>";

	echo "<tr><td class='tab_bg_2'>".$lang["joblist"][0].":</td>";
	echo "<td align='center' class='tab_bg_1'><select name=status>";
	echo "<option value=new selected>".$lang["job"][14]."</option>";
	echo "<option value=old>".$lang["job"][15]."</option>";
	echo "</select></td></tr>";

	echo "<tr><td class='tab_bg_2'>".$lang["joblist"][2].":</td>";
	echo "<td align='center' class='tab_bg_1'><select name=priority>";
	echo "<option value=5>".$lang["joblist"][17]."</option>";
	echo "<option value=4>".$lang["joblist"][18]."</option>";
	echo "<option value=3 selected>".$lang["joblist"][19]."</option>";
	echo "<option value=2>".$lang["joblist"][20]."</option>";
	echo "<option value=1>".$lang["joblist"][21]."</option>";
	echo "</select></td></tr>";

	echo "<tr class='tab_bg_2'><td>".$lang["joblist"][3].":</td>";
	echo "<td align='center'>$author</td></tr>";

	echo "<tr class='tab_bg_2'><td>".$lang["joblist"][5].":</td>";
	echo "<td align='center'>";
	$db=new DB;
	$query = "SELECT name FROM computers WHERE (ID = $ID)";
	$result = $db->query($query);
	$computername = $db->result($result, 0, "name");
	echo "$computername ($ID)"; 
	echo "<input type=hidden name=ID value=\"$ID\">";
	echo "</td></tr>";

	echo "<tr><td colspan=2 height=5></td></tr>";
	echo "<tr><th colspan=2>".$lang["job"][11].":</th></tr>";

	echo "<tr><td colspan=2><textarea cols=50 rows=14  name=contents></textarea></td></tr>";

	echo "<tr class='tab_bg_1'><td colspan=2 align='center'>";
	echo "<input type=submit value=\"".$lang["buttons"][2]."\">";
	echo "</td></tr>";
	
	echo "</table></center>";

}

function assignFormTracking ($ID,$admin,$target) {
	// Print a nice form to assign jobs if user is allowed

	GLOBAL $cfg_layout, $lang;


  if (can_assign_job($admin))
  {

	$job = new Job;
	$job->getFromDB($ID,0);

	echo "<table border='0'>";
	echo "<tr><th>".$lang["job"][4]." $ID:</th></tr>";
	echo "<form method=get action=\"".$target."\">";
	echo "<td align='center' class='tab_bg_1'>";

	echo "<table border='0'>";
	echo "<tr>";
	echo "<td>".$lang["job"][5].":</td><td>";
		dropdownUsers($job->assign, "user");
	echo "<input type=hidden name=update value=\"1\">";
	echo "<input type=hidden name=ID value=$job->ID>";
	echo "</td><td><input type=submit value=\"".$lang["job"][6]."\"></td>";
	echo "</tr></table>";

	echo "</td>";
	echo "</form>";
	echo "</tr></table>";
	}
	else
	{
	 echo $lang["tracking"][6];
	}
}
