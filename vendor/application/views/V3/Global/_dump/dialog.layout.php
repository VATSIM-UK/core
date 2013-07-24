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
 * File:			siteFiles/layout/dialog.layout.php
 * Description:	Outputs the required dialog boxes at the end of the template -
 *				called through Layout Class
 * 
 */

?>

					<div id="loginBox" class="dialog" title="Login">
						<div class="dialogContent">
							<div id="loginReturn">
							<?php
								$this->dialog('login');
							?>
							</div>
							
						</div>
					</div>

					<div id="dashboardManager" class="dialog" title="Add Module">
						<div class="dialogContent">
							<div id="moduleOptions"></div>
						</div>
					</div>
