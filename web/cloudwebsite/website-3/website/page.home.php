<div class="wrapper">
	<div class="container">
        <div style="width:100%;height:500px" id="slider">
          <img src="images/example-slide-1.jpg" alt="Photo by: Missy S Link: http://www.flickr.com/photos/listenmissy/5087404401/">
          <img src="images/example-slide-2.jpg" alt="Photo by: Daniel Parks Link: http://www.flickr.com/photos/parksdh/5227623068/">
          <img src="images/example-slide-3.jpg" alt="Photo by: Mike Ranweiler Link: http://www.flickr.com/photos/27874907@N04/4833059991/">
          <img src="images/example-slide-4.jpg" alt="Photo by: Stuart SeegerLink: http://www.flickr.com/photos/stuseeger/97577796/">
        </div>
    </div>
</div>

<style type="text/css">
#slider {
	position:relative;
}

.slidesjs-pagination {
	z-index: 100;
	margin: 6px 0 0;
	list-style: none;
	text-align:center;
	position:relative;
	top:-30px;
}

.slidesjs-pagination li {
	display:inline-block;
	margin: 0 1px;
}

.slidesjs-pagination li a {
  display: block;
  width: 13px;
  height: 0;
  padding-top: 13px;
  background-image: url(images/pagination.png);
  background-position: 0 0;
  float: left;
  overflow: hidden;
}

.slidesjs-pagination li a.active,
.slidesjs-pagination li a:hover.active {
  background-position: 0 -13px
}

.slidesjs-pagination li a:hover {
  background-position: 0 -26px
}
</style>

<script type="text/javascript" src="jquery.slides.min.js"></script>
<script type="text/javascript">
$(document).ready(function(e) {
    $("#slider").slidesjs({
		width: 960,
		height: 500,
		navigation: {
			active: false
		},
		pagination: {
			active: true,
			effect: "fade"
		},
		play: {
			active: false,
			effect: "fade",
			interval: 3000,
			auto: true,
			swap: true
		},
		effect: {
			face: {speed: 400}
		}
	});
});
</script>

<div class="wrapper">
	<div class="container">
    	<div class="content">
        	asdf
        </div>
    </div>
</div>