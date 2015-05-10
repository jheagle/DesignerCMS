/* main script for all pages */

/* perform when document is ready */
$(document).ready(function() {
	
	var hasFixedMenu = false;
	/* adjust menu when it scrolls out of view */
	var menuTop = parseInt($(this).scrollTop());
	if (menuTop > 228) {
		if(!hasFixedMenu){
			$('.menu').clone().appendTo('header').addClass('menu-top');
			hasFixedMenu = !hasFixedMenu;
		}
	} else {
		$('.menu').remove('.menu-top');
		hasFixedMenu = false;
	}
	$(window).scroll(function() {
		menuTop = parseInt($(this).scrollTop());
		if (menuTop > 228) {
			if(!hasFixedMenu){
				$('.menu').clone().appendTo('header').addClass('menu-top');
				hasFixedMenu = !hasFixedMenu;
			}
		} else {
			$('.menu').remove('.menu-top');
			hasFixedMenu = false;
		}
	});
	
	$(".background").html(generateBackground());
	function generateBackground(){
		var s, curHeight, curWidth, winHeight = $(window).height(), winWidth = $(window).width(), numHigh, numWide;
		while(curHeight < winHeight || curWidth < winWidth){
			if(curWidth < winWidth){
				
			}
			if(curHeight < winHeight){
				
			}
		}
	}
	
	function generateBlock(curHeight, curWidth){
		
	}
});