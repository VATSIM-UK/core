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
 * File:			
 * Description:		
 * 
 */

?>

<table class="standard">
	
	<tr>
		<th>CID</th>
		<th>Name</th>
		<th>Email</th>
		<th>Controller</th>
		<th>Pilot</th>
	</tr>
	
	<?php
	
		foreach ($Users as $User){
			echo '<tr>';
			
				echo '<td><a href="'.$this->{_HANDLER}->link(array('user', 'view','cid'=>$User->cid()), false).'">'.$User->cid().'</a></td>';
				echo '<td>'.$User->getData('name').'</td>';
				echo '<td>'.$User->getData('email').'</td>';
				echo '<td class="centre">'.$User->ratingOutput('atc').'</td>';
				echo '<td class="centre">'.$User->ratingOutput('pilot').'</td>';
			
			echo '</tr>'."\n";
		}
	
	?>
	
</table>
