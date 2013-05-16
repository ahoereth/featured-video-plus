// dynamic
function fvp_dynamic(id){
	var t = jQuery('#fvp_'+id);
	t.css({'position':'relative'})
	 .prepend(jQuery('<div />', {
		'class':'fvp_loader',
		'style':'background:transparent url(\''+fvpdata.loadingb+'\') no-repeat center center;'+
						'position: absolute; margin: '+t.children('img').css('margin')+';'+
						'height:'+t.children('img').css('height')+';width:'+t.children('img').css('width')+'; z-index:1000;'
	}));
	jQuery.post( fvpdata.ajaxurl,
		{
			'action': 'fvp_get_embed',
			'nonce' : fvpdata.nonce,
			'id'    : id
		},
	function(data){
		if (data.success){
			t.replaceWith(data.html);
			if (fvpdata.fitvids=="1" && fvpdata.overlay=="0")
				jQuery(".featured_video_plus").fitVids({customSelector:["iframe", "object", "embed", ".video-js"]});

			if (fvpdata.videojs)
				videojs('videojs'+data.id,{'autoplay':true},function(){});
		}
	});//.fail(function() { alert("failed here"); });;
}

jQuery(document).ready(function($){

	if(fvpdata.overlay||fvpdata.dynamic){
		$('.fvp_overlay,.fvp_dynamic').hover(
			function(){ $(this).children('img').animate({opacity:0.65});	},
			function(){ $(this).children('img').animate({opacity:1}); 		}
		);
	}

	// overlay
	if(fvpdata.overlay){
	  $('.fvp_overlay').click(function(){
			$(this).openDOMWindow({
				eventType:null,
				windowPadding:0,
				borderSize:0,
				windowBGColor:'transparent',
				overlayOpacity:fvpdata.opacity
			});
			$('#DOMWindow').css({'background':"transparent url('"+fvpdata.loadingw+"') center center no-repeat"});
	  	var id = new RegExp('[\\?&amp;\\#]fvp_([0-9]+)').exec($(this).attr('href'));
			if ($('#fvp_'+id[1]).html().length === 0){
				jQuery.post( fvpdata.ajaxurl,
					{
						'action': 'fvp_get_embed',
						'nonce' : fvpdata.nonce,
						'id'    : id[1]
					},
				function(data){
					if (data.success){
						//$('#fvp_'+id[1]).html(data.html); // save the data to not reload when opened again
						$('#DOMWindow').html(data.html).css({'width':'auto','height':'auto','margin':'auto auto','overflow':'hidden'});
						$(window).trigger('scroll');

						if (fvpdata.videojs)
							videojs('videojs'+data.id,{'autoplay':true},function(){});
					}
				});//.fail(function() { alert("failed here"); });;
			}else{
				$('#DOMWindow').html( $('#fvp_'+id[1]).html() ).css({'width':'auto','height':'auto','margin':'auto auto','overflow':'hidden'});
				$(window).trigger('scroll');
			}
		});
	}

	// fitvids
	if (fvpdata.fitvids=="1" && fvpdata.dynamic=="0" && fvpdata.overlay=="0")
	  $(".featured_video_plus").fitVids({customSelector:["iframe", "object", "embed", ".video-js"]});

});