<?php
/**
 * 
 * This file was written for use on vatsim-uk.co.uk (and vatsim-uk.org) only
 * and is not to be copied or distributed for use on any other site. Permission
 * to use the content of this file must come from the VATUK (Vatsim UK) Web
 * Services department plus the developer of the file.
 * 
 * Changes to this script must be approved by the script developer who reserves
 * the right to withdraw this script from all use at any time.
 * 
 * This copyright notice is to remain intact and unedited at all times
 * 
 * @author Kieran Hardern
 * @copyright Copyright (C) 2012 onwards, Kieran Hardern. All rights reserved.
 * @version 1.0
 * 
 * File:			siteFiles/layout/feature/message.feature.php
 * Description:	Outputs the messages set in the session
 * 
 */

while ($message = $this->{_SESSION}->getMessage()){
	echo "\n";
	echo '<div class="messageBox '.$message['type'].'Message">';
		echo $message['message'];
	echo '</div>';
}

?>
