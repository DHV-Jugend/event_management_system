jQuery(document).ready(function(){jQuery(".masterTooltip").hover(function(){var title=jQuery(this).attr("title");jQuery(this).data("tipText",title).removeAttr("title"),jQuery('<p class="tooltip"></p>').text(title).appendTo("body").fadeIn("slow")},function(){jQuery(this).attr("title",jQuery(this).data("tipText")),jQuery(".tooltip").remove()}).mousemove(function(e){var mousex=e.pageX+20,mousey=e.pageY+10;jQuery(".tooltip").css({top:mousey,left:mousex})})});