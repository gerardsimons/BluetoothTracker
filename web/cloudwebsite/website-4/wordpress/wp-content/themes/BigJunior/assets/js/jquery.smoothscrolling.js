/*
jQuery plugin to enable smooth scrolling for anchor links for the whereAt Cloud website
Written by Jasper Bussemaker, 2014
*/

(function($) {
	$(document).ready(function(e) {
		$("body").on("click", "a", function(e) {
			var href = $(this).attr("href");
			if (href.substr(0, 1) != "#") return;
			
			var target = "[name="+href.substr(1)+"]";
			if ($(target).length == 0) {
				target = "#"+href.substr(1);
				if ($(target).length == 0) return;
			}
			
			e.preventDefault();
			$.smoothScroll({scrollTarget: target});
		});
	});
})(jQuery);