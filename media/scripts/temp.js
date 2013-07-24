$(function() {
     
    //user runs a members search
    $('#member_search_run').click(function(){
        runSearch(1);
    });
    
    
    
    function runSearch(page){
        $.post(ajaxSearch, $("#ajax_search").serialize() + '&page=' + page, function(Users){
        //if an object is not returned, alert an error
        if (typeof Users != 'object'){
             alert('We were unable to run this search');
        } else { //object was returned
             var searchTable = '';
             var tableHeaders = Array();
             var k = 0;
             //loop of users
             jQuery.each(Users, function(cid, theUser){
                  var h = 0;
                  if (cid=='header'){
                       jQuery.each(theUser, function(key, value){
                            tableHeaders[h] = value;
                            h++;
                       });
                  } else if (cid=='page'){
                      
                  } else {
                       searchTable = searchTable + '<tr><td><a class="cidLink" href="#">' + cid + '</a></td>';
                       jQuery.each(theUser, function(key, value){
                            if (k==0 && tableHeaders.length==0){
                                 tableHeaders[h] = key;
                                 h++;
                            }
                            searchTable = searchTable + '<td>' + value + '</td>';
                       });
                       searchTable = searchTable + '</tr>';
                       k++;
                  }
             });

             if (searchTable == ''){
                  searchTable = '<tr><th>Search Result</th></tr><tr><td>No Users found march these criteria</td></tr>';
             } else {
                  var headerOutput = '<tr>';
                  jQuery.each(tableHeaders, function(key, value){
                       headerOutput = headerOutput + '<th>' + value + '</th>';
                  });
                  searchTable = headerOutput + '</tr>' + searchTable;
             }

             searchTable = '<table id="member_return_table" class="standard">' + searchTable + '</table>';

             $('#member_return .box_content').html(searchTable);

             //show the return box (slide in and content visible)
             if ($('#member_return').attr('opened')!='yes'){
                  $('#member_return').slideDown(700);
                  $('#member_return').css('visibility', 'visible');
                  $('#member_return').attr('opened', 'yes');
             } else {

                  $('#member_return').find('.box_content').each(function(){
                       //current content is hidden?
                       if ($(this).attr('status')!='show'){
                            showBox($(this));
                       }
                  });
             }

             $('#member_return').find('a').click(function(){
                  //the CID of the user clicked
                  var user_cid = $(this).text();

                  //update links to contain the CID
                  var k = 0;
                  $('#member_tabs').find('a').each(function(){
                       //link/?cid=700000
                       $(this).attr('href', ajaxTabs[k] + '?cid=' + user_cid);
                       k++;
                  });

                  //reload the current tab
                  $('#member_tabs').tabs("load", 
                       $('#member_tabs').tabs( "option", "active")
                  );

                  //show the tab box (slide in and content visible)
                  $('#member_tabs').slideDown(700);
                  $('#member_tabs').css('visibility', 'visible');
                  //hide the member return list
                  $(this).parents('.box_content').each(function(){
                       //hide box if not locked open
                       if ($(this).attr('locked')!='yes'){
                            hideBox($(this));
                       }
                  });


             });

            }
        }, "json").fail(function(){
            alert('An error occurred when querying for the member data');
        });
    }
     
     //toggle button to show/hide box is clicked
     $('.box .toggle').click(function(){
          //find all box divs with class="box_content"
          $(this).parent().find('.box_content').each(function(){
               //current content is hidden?
               if ($(this).attr('status')=='hidden'){
                    showBox($(this));
                    //user has specifically asked for this to remain open, lock this in place
                    $(this).attr('locked', 'yes');
               } else {
                    hideBox($(this));
                    //remove any lock placed by the user
                    $(this).attr('locked', 'no');
               }
          })
     });
     
});



function hideBox(ref){
     //content is not hidden, hide it and save an attribute to record this
     ref.slideUp(400);
     ref.attr('status', 'hidden');
     //change icon on toggle button to up arrow
     ref.parent().find('.toggle .toggle_icon').each(function(){
          $(this).addClass('ui-icon-carat-1-s');
          $(this).removeClass('ui-icon-carat-1-n');
     });
}

function showBox(ref){
     //show content, set an attribute to record this
     ref.slideDown(700);
     ref.attr('status', 'show');
     //change icon on toggle button to down arrow
     ref.parent().find('.toggle .toggle_icon').each(function(){
          $(this).addClass('ui-icon-carat-1-n');
          $(this).removeClass('ui-icon-carat-1-s');
     });
}