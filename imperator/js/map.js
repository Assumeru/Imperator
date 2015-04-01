$(function($) {
	var $currentHover,
	MAX_ZOOM = [50, 250],
	$dragPosition = {
		x: 0,
		y: 0
	};

	function hidePopUp() {
		if($currentHover !== undefined) {
			$currentHover.hide();
			$currentHover = undefined;
		}
	}

	function setUpDrag($container) {
		$container.mousedown(startDrag);
		$('body').mouseup(stopDrag);
	}

	function startDrag($e) {
		$dragPosition.x = $e.pageX;
		$dragPosition.y = $e.pageY;
		$('#map .map-container').mousemove(mapDrag);
	}

	function stopDrag() {
		$('#map .map-container').off('mousemove');
	}

	function mapDrag($e) {
		var ΔX = $dragPosition.x - $e.pageX,
		ΔY = $dragPosition.y - $e.pageY;
		$dragPosition.x = $e.pageX;
		$dragPosition.y = $e.pageY;
		moveMap(ΔX, ΔY);
	}

	function moveMap($x, $y) {
		var $container = $('#map .map-container'),
		$top = $container.scrollTop(),
		$left = $container.scrollLeft();
		$container.scrollLeft($left + $x);
		$container.scrollTop($top + $y);
		hidePopUp();
	}

	function moveTowardsMouse($e, $zoom) {
		var $container = $('#map .map-container'),
		$offset = $container.offset(),
		$height = $container.height(),
		$width = $container.width(),
		$center = {
			x: $offset.left + $width / 2,
			y: $offset.top + $height / 2
		},
		ΔX = 20 * ($e.clientX - $center.x) / $width,
		ΔY = 20 * ($e.clientY - $center.y) / $height;
		moveMap(($zoom + ΔX) / 100 * $width, ($zoom + ΔY) / 100 * $height);
	}

	function setUpZoom($container) {
		var $controls = $('#map .map-controls'),
		$svg = $container.find('svg'),
		$height = $svg.height(),
		$width = $svg.width();
		$controls.find('.zoom-in').click(zoomIn);
		$controls.find('.zoom-out').click(zoomOut);
		$controls.show();
		$container.on('wheel', zoomScroll);
		if($width > $height) {
			zoomMap(-100 * $height / $width);
		}
	}

	function zoomScroll($e) {
		var $zoom;
		if($e.originalEvent !== undefined) {
			if($e.originalEvent.deltaY > 0) {
				$zoom = zoomOut($e);
			} else {
				$zoom = zoomIn($e);
			}
			moveTowardsMouse($e.originalEvent, $zoom);
		}
	}

	function zoomIn($e) {
		$e.preventDefault();
		return zoomMap(10);
	}

	function zoomOut($e) {
		$e.preventDefault();
		return zoomMap(-10);
	}

	function zoomMap($amount) {
		var $svg = $('#map .map-container svg'),
		$height = parseInt($svg.attr('height')),
		$new = $height + $amount;
		if($new < MAX_ZOOM[0]) {
			$new = MAX_ZOOM[0];
		} else if($new > MAX_ZOOM[1]) {
			$new = MAX_ZOOM[1];
		}
		$svg.attr('height', $new+'%');
		if($new !== $height) {
			hidePopUp();
		}
		return $new - $height;
	}

	function setUpClick($container) {
		var $offset,
		$territories = $container.find('g[id]');
		$territories.click(function($e) {
			var $popup = $('#map .territory-hover[data-territory="'+this.id+'"]');
			$e.preventDefault();
			if($currentHover !== undefined) {
				$currentHover.hide();
				if($currentHover.is($popup)) {
					$currentHover = undefined;
					return;
				}
			}
			$currentHover = $popup;
			$popup.show();
			$offset = $container.offset();
			$popup.css('top', ($e.pageY - $offset.top)+'px');
			$popup.css('left', ($e.pageX - $offset.left)+'px');
		});
	}

	function loadMap($url) {
		var $map = $('#map');
		$map.addClass('loading');
		$.ajax({
			type: 'GET',
			url: $url,
			dataType: 'xml'
		}).fail(function() {
			console.error('Failed to load map.');
			$map.removeClass('loading');
		}).done(function($svg) {
			var $container = $('<div class="map-container"></div>');
			$('#map .map-square > img').remove();
			$('#map .map-square').append($container);
			$container.append($svg.documentElement);
			setUpClick($container);
			setUpZoom($container);
			setUpDrag($container);
			$map.removeClass('loading');
		});
	}

	loadMap($('#map .map-square > img').attr('src'));
});