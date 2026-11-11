<script src="{{ URL('/leading/js/jquery.min.js') }}"></script><!-- JQUERY.MIN JS -->
<script src="{{ URL('/leading/plugins/bootstrap/js/popper.min.js') }}"></script><!-- BOOTSTRAP.MIN JS -->
<script src="{{ URL('/leading/plugins/bootstrap/js/bootstrap.min.js') }}"></script><!-- BOOTSTRAP.MIN JS -->
<script src="{{ URL('/leading/plugins/bootstrap-select/bootstrap-select.min.js') }}"></script><!-- FORM JS -->
<script src="{{ URL('/leading/plugins/bootstrap-touchspin/jquery.bootstrap-touchspin.js') }}"></script><!-- FORM JS -->
<script src="{{ URL('/leading/plugins/magnific-popup/magnific-popup.js') }}"></script><!-- MAGNIFIC POPUP JS -->
<script src="{{ URL('/leading/plugins/counter/waypoints-min.js') }}"></script><!-- WAYPOINTS JS -->
<script src="{{ URL('/leading/plugins/counter/counterup.min.js') }}"></script><!-- COUNTERUP JS -->
<script src="{{ URL('/leading/plugins/imagesloaded/imagesloaded.js') }}"></script><!-- IMAGESLOADED -->
<script src="{{ URL('/leading/plugins/masonry/masonry-3.1.4.js') }}"></script><!-- MASONRY -->
<script src="{{ URL('/leading/plugins/masonry/masonry.filter.js') }}"></script><!-- MASONRY -->
<script src="{{ URL('/leading/plugins/owl-carousel/owl.carousel.js') }}"></script><!-- OWL SLIDER -->

<script src="{{ URL('/leading/js/custom.js') }}"></script><!-- CUSTOM FUCTIONS  -->
<script src="{{ URL('/leading/js/dz.carousel.js') }}"></script><!-- SORTCODE FUCTIONS  -->
<script src="{{ URL('/leading/js/dz.ajax.js') }}"></script><!-- CONTACT JS  -->
<script src='https://www.google.com/recaptcha/api.js'></script> <!-- Google API For Recaptcha  -->
<!-- REVOLUTION JS FILES -->
<script src="{{ URL('/leading/plugins/revolution/revolution/js/jquery.themepunch.tools.min.js') }}"></script>
<script src="{{ URL('/leading/plugins/revolution/revolution/js/jquery.themepunch.revolution.min.js') }}"></script>
<!-- Slider revolution 5.0 Extensions  (Load Extensions only on Local File Systems !  The following part can be removed on Server for On Demand Loading) -->
<script src="{{ URL('/leading/plugins/revolution/revolution/js/extensions/revolution.extension.actions.min.js') }}"></script>
<script src="{{ URL('/leading/plugins/revolution/revolution/js/extensions/revolution.extension.carousel.min.js') }}"></script>
<script src="{{ URL('/leading/plugins/revolution/revolution/js/extensions/revolution.extension.kenburn.min.js') }}"></script>
<script src="{{ URL('/leading/plugins/revolution/revolution/js/extensions/revolution.extension.layeranimation.min.js') }}"></script>
<script src="{{ URL('/leading/plugins/revolution/revolution/js/extensions/revolution.extension.migration.min.js') }}"></script>
<script src="{{ URL('/leading/plugins/revolution/revolution/js/extensions/revolution.extension.navigation.min.js') }}"></script>
<script src="{{ URL('/leading/plugins/revolution/revolution/js/extensions/revolution.extension.parallax.min.js') }}"></script>
<script src="{{ URL('/leading/plugins/revolution/revolution/js/extensions/revolution.extension.slideanims.min.js') }}"></script>
<script src="{{ URL('/leading/plugins/revolution/revolution/js/extensions/revolution.extension.video.min.js') }}"></script>
<script>
var tpj=jQuery;
	
	var revapi1059;
	tpj(document).ready(function() {
		'use strict';
		if(tpj("#rev_slider_1059_1").revolution == undefined){
			revslider_showDoubleJqueryError("#rev_slider_1059_1");
		}else{
			revapi1059 = tpj("#rev_slider_1059_1").show().revolution({
				sliderType:"standard",
				jsFileLocation:"//server.local/revslider/wp-content/plugins/revslider/public/assets/js/",
				sliderLayout:"fullscreen",
				dottedOverlay:"none",
				delay:9000,
				navigation: {
					keyboardNavigation: "on",
					keyboard_direction: "horizontal",
					mouseScrollNavigation: "off",
					onHoverStop: "off",
					touch: {
						touchenabled: "on",
						swipe_threshold: 75,
						swipe_min_touches: 1,
						swipe_direction: "horizontal",
						drag_block_vertical: false
					},
					arrows: {
						style: "hesperiden",
						enable: true,
						hide_onmobile: false,
						hide_onleave: false,
						tmp: '',
						left: {
							h_align: "left",
							v_align: "top",
							h_offset: 50,
							v_offset: 300
						},
						right: {
							h_align: "right",
							v_align: "top",
							h_offset: 50,
							v_offset: 300
						}
					},
					bullets: {
						enable: true,
						hide_onmobile: false,
						style: "hesperiden",
						hide_onleave: false,
						direction: "horizontal",
						h_align: "left",
						v_align: "bottom",
						h_offset: 20,
						v_offset: 20,
						space: 5,
						tmp: ''
					},
					thumbnails: {
						style: "hesperiden",
						enable: true,
						width: 100,
						height: 50,
						min_width: 100,
						wrapper_padding: 5,
						wrapper_color: "#ffffff",
						wrapper_opacity: "0.5",
						tmp: '<span class="tp-thumb-image"></span><span class="tp-thumb-title">titlo</span>',
						visibleAmount: 5,
						hide_onmobile: false,
						hide_onleave: false,
						direction: "horizontal",
						span: false,
						position: "inner",
						space: 5,
						h_align: "right",
						v_align: "bottom",
						h_offset: 20,
						v_offset: 50
					},
					
				},
				responsiveLevels:[1240,1024,778,480],
				visibilityLevels:[1240,1024,778,480],
				gridwidth:[1240,1024,778,480],
				gridheight:[868,768,960,720],
				lazyType:"none",
				parallax: {
					type:"scroll",
					origo:"slidercenter",
					speed:1000,
					levels:[5,10,15,20,25,30,35,40,45,46,47,48,49,50,100,55],
					type:"scroll",
				},
				shadow:0,
				spinner:"off",
				stopLoop:"on",
				stopAfterLoops:-1,
				stopAtSlide:1,
				shuffle:"off",
				autoHeight:"off",
				fullScreenAutoWidth:"off",
				fullScreenAlignForce:"off",
				fullScreenOffsetContainer: "",
				fullScreenOffset: "60px",
				disableProgressBar:"on",
				hideThumbsOnMobile:"off",
				hideSliderAtLimit:0,
				hideCaptionAtLimit:0,
				hideAllCaptionAtLilmit:0,
				debugMode:false,
				fallbacks: {
					simplifyAll:"off",
					nextSlideOnWindowFocus:"off",
					disableFocusListener:false,
				}
			});
		}
	}); /*ready*/
</script>