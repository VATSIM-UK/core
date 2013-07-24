/* 
 * Script: general.js
 * Author: Kieran Hardern
 * Description:
 *		General functionality, required on multiple pages
 */

$(function() {
	//change the styling of the module headers on hovering
	$(".module").hover(function(){
		//on hover over a module, set the focus state for the header
		$(this).find('.moduleHeader').switchClass('ui-state-default', 'ui-state-focus', 0);
	}, function(){
		$(this).find('.moduleHeader').switchClass('ui-state-focus', 'ui-state-default', 0);
	});
	
	//function to initiate a show/hide when a module button is clicked
	$('.moduleCollapse').click(function(){
		//if the module is currently visible
	   if ($(this).attr('current')!='novis'){
		   //make the module hidden
		   $(this).parent().parent().find('.moduleContent').hide( 'blind', false, 500, false );
		   //round the bottom corners of the module header
		   $(this).parent().switchClass('ui-corner-top', 'ui-corner-all', 0);
		   //set the parent module attribute to not visible
		   $(this).attr('current', 'novis');
		   //change the text displayed on this button
		   $(this).html('Expand');
	   //the module is not visible
	   } else {
		   //make the module visible
		   $(this).parent().parent().find('.moduleContent').show( 'blind', false, 500, false );
		   //remove the bottom rounded corners of the module header
		   $(this).parent().switchClass('ui-corner-all', 'ui-corner-top', 0);
		   //set the parent module attribute to visible
		   $(this).attr('current', 'vis');
		   //change the text displayed on this button
		   $(this).html('Collapse');
	   }
	});
	
	/*
	//function to initiate if a show/hide button is clicked
	$(".module .button").click(function() {
		//if the module is currently visible
		if ($(this).attr('current')=='vis'){
			//determine the div ID from the atrribute 'param' and hide that ID with the blind effect - no return function
			$($(this).attr('param')).find('.moduleContent').hide( 'blind', false, 500, false );
			//round the bottom corners of the module header
			$($(this).attr('param')).find('.moduleHeader').switchClass('ui-corner-top', 'ui-corner-all', 0);
			//set the current attribute to indicate hidden
			$(this).attr('current', 'novis');
			$(this).html('Show');
		} else {
			//determine the div ID from the 'param' attribute and reverse the hide effect by fading back in
			$($(this).attr('param')).find('.moduleContent').show( 'blind', false, 500, false );
			//remove the rounded corners on the bottom of the module now that the content is back
			$($(this).attr('param')).find('.moduleHeader').switchClass('ui-corner-all', 'ui-corner-top', 0);
			//set the current attribute to indicate visible
			$(this).attr('current', 'vis');
			$(this).html('Hide');
		}
	});*/
    
    /*
    //function to initiate a show/hide of a module when the header is clicked
    $(".moduleHeader").click(function(){
	   //if the module is currently visible
	   if ($(this).parent().attr('current')!='novis'){
		   //make the module hidden
		   $(this).parent().find('.moduleContent').hide( 'blind', false, 500, false );
		   //round the bottom corners of the module header
		   $(this).switchClass('ui-corner-top', 'ui-corner-all', 0);
		   //set the parent module attribute to not visible
		   $(this).parent().attr('current', 'novis');
	   //the module is not visible
	   } else {
		   //make the module visible
		   $(this).parent().find('.moduleContent').show( 'blind', false, 500, false );
		   //remove the bottom rounded corners of the module header
		   $(this).switchClass('ui-corner-all', 'ui-corner-top', 0);
		   //set the parent module attribute to visible
		   $(this).parent().attr('current', 'vis');
	   }
    });
    */

	//opening the main menu
	//on hover over the menu tab
	$(".menuOpen").hover(function(){
		//make sure the menu is closed (prevents duplicate instances of effects)
		if ($(".menuOpen").attr('menuOpen')!='on'){
			//set the menu as open
			$(".menuOpen").attr('menuOpen', 'on');
			//fade the menu into view
			$(".menuContain").fadeIn(400);
		}
	});
	
	//closing the main menu
	//on hovering over any part of the menu
	$(".mainMenu").hover(function(){
		//hovering over does nothing - menu is opened initially by the hover over the opening tab
	}, function(){
		//on removing hover, check to see if the menu is open
		if ($(".menuOpen").attr('menuOpen')=='on'){
			//if it is open and hasn't been fixed
			if ($(this).attr('fixState')!='fixed'){
				//remove it from view with the blind effect
				$(".menuContain").effect('blind', false, 500, false);
				//set the menu to closed
				$(".menuOpen").attr('menuOpen', 'off');
			}
		}
	});
	
	//fixing the main menu
	//clicking on any part of the expanded (or closed) menu
	$(".mainMenu").click(function(){
		//if the menu is already fixed, remove the fix state
		if ($(this).attr('fixState')=='fixed'){
			$(this).attr('fixState', false);
		//otherwise, set the menu into fixed state
		} else {
			$(this).attr('fixState', 'fixed');
		}
	});
	
	//popup dialogs
	//initiate all dialogs with the dialog class
	$(".dialog").dialog({
		//will not open until prompted
		autoOpen: false,
		//will close with the escape key with the explode effect
		closeOnEscape: true,
		hide: 'explode',
		//x position centre, y position 220 from top
		position: ['center',220],
		//minimum width settings
		minWidth: 400,
		minHeight: 150,
		//multiple dialogs may stack upon each other
		stack: true
	});
	
	//initiate all dialogs with the dialogLarge class
	$(".dialogTall").dialog({
		//will not open until prompted
		autoOpen: false,
		//will close with the escape key with the explode effect
		closeOnEscape: true,
		hide: 'explode',
		//x position centre, y position 220 from top
		position: ['center',150],
		//minimum width settings
		minWidth: 400,
		minHeight: 500,
		//multiple dialogs may stack upon each other
		stack: true
	});
	
	
	//open the dialog when clicked  - TEST
	$(".openDialog").click(function(){
		$("#dialog1").dialog('open');
	});
     
     //start any page tabs
     $( ".tabs" ).tabs({
          collapsible: true,
          //if ajax tabs, set an error message if unable to load
          beforeLoad: function( event, ui ) {
               ui.jqXHR.error(function() {
                    ui.panel.html("This tab could not be loaded");
               });
          }
     });
     
     //.addClass( "ui-tabs-vertical ui-helper-clearfix" );
     
     //$( ".tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
	
});

