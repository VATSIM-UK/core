<script type="text/javascript">
     var ajaxTabs = Array();
     ajaxTabs[0] = "<?=URL::site('membership/manage/details');?>";
     ajaxTabs[1] = "<?=URL::site('membership/manage/record');?>";
     ajaxTabs[2] = "<?=URL::site('membership/manage/teamspeak');?>";
     
     var ajaxSearch  = "<?=URL::site('membership/manage/search');?>";
</script>

<div id="member_search" class="box">
    <form action="<?=URL::site('membership/manage/search');?>" id="ajax_search" method="post">
     <table class="standard thAlternate">
          <tr>
               <th>Name</th>
               <td><input type="text" name="name" value="" /></td>
               
               <th>CERT Email</th>
               <td><input type="text" name="cert_email" value="" /></td>
               
               <th>ATC</th>
               <td></td>
               
               <th>Status</th>
               <td></td>
          </tr>
          <tr>
               <th>CID</th>
               <td><input type="text" name="cid" style="width: 60px;" maxlength="7" /></td>
               
               <th>Any Email</th>
               <td><input type="text" name="email" value="" /></td>
               
               <th>Pilot</th>
               <td></td>
               
               <th>State</th>
               <td></td>
          </tr>
          <tr>
               <td colspan="8" class="centre"><input type="button" class="button" value="Search" id="member_search_run" />
               <input type="submit" class="button" value="Search 2" /></td>
          </tr>
     </table>
    </form>
</div>

<div id="member_return" class="box" style="visibility: hidden; display: none;">
     <div class="toggle ui-state-highlight ui-corner-bottom">
          <div class="toggle_icon ui-icon ui-icon-carat-1-n"></div>
     </div>
     <div class="box_content">
          
          <table id="member_return_table" class="standard">
               <tr>
                    <th>CID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>State</th>
                    <th>ATC</th>
                    <th>Pilot</th>
               </tr>
          </table>
     </div>
     
</div>

<div id="member_tabs" class="tabs" style="visibility: hidden; display: none;">
    <ul>
         <li><a href="<?=URL::site('membership/manage/details');?>"><span>Details</span></a></li>
         <li><a href="<?=URL::site('membership/manage/record');?>"><span>User Record</span></a></li>
         <li><a href="<?=URL::site('membership/manage/teamspeak');?>"><span>TeamSpeak</span></a></li>
    </ul>
</div>
