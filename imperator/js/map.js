Imperator.Map = (function($) {
	var $currentHover,
	MAX_ZOOM = [50, 250],
	$dragPosition = {
		x: 0,
		y: 0
	},
	$loading = true,
	$onLoad = [];

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

	function addUnitBoxes() {
		var $id, $bounds, $dims, $n,
		$g = makeSVG('g', {'class': 'units'}),
		$margin = 25,
		$elements = $('#map svg g[id]');
		for($n = 0; $n < $elements.length; $n++) {
			$id = $elements[$n].id;
			$bounds = document.getElementById($id).getBBox();
			$dims = {
				x: $bounds.x + $bounds.width / 2,
				y: $bounds.y + $bounds.height / 2,
				r: ($bounds.width + $bounds.height) / 200 * (100 - $margin * 2)
			};
			$g.appendChild(makeSVG('rect', {
				x: $dims.x - $dims.r / 2,
				y: $dims.y - $dims.r / 2,
				height: $dims.r,
				width: $dims.r,
				id: 'unitBox-'+$id
			}));
		}
		$('#map svg').append($g);
	}

	function getUnitPattern($number) {
		var $text, $width,
		$pattern = document.getElementById('pattern-units-numbers-'+$number);
		if($pattern === null) {
			$width = Math.floor(Math.log($number) / Math.LN10) * 8 + 8;
			$pattern = makeSVG('pattern', {width: 1, height: 1, id: 'pattern-units-numbers-'+$number, viewBox: '0 0 '+$width+' 16'});
			$text = makeSVG('text', {x: 0, y: 14});
			$text.appendChild(document.createTextNode($number));
			$pattern.appendChild($text);
			$('#map svg defs').append($pattern);
		}
		return '#pattern-units-numbers-'+$number;
	}

	function loadUnitGraphics($unitGraphics) {
		$.ajax({
			type: 'GET',
			url: $unitGraphics,
			dataType: 'xml'
		}).fail(function() {
			console.error('Faled to load unit graphics.');
		}).done(function($svg) {
			addOnLoad(function() {
				$('#map svg').append($svg.documentElement.getElementsByTagName('defs')[0]);
				addUnitBoxes();
			});
		});
	}

	function loadMap($url, $unitGraphics) {
		var $map = $('#map');
		$map.addClass('loading');
		if($unitGraphics !== undefined) {
			loadUnitGraphics($unitGraphics);
		}
		$.ajax({
			type: 'GET',
			url: $url,
			dataType: 'xml'
		}).fail(function() {
			console.error('Failed to load map.');
			$map.removeClass('loading');
			onLoad();
		}).done(function($svg) {
			var $container = $('<div class="map-container"></div>');
			$('#map .map-square .map-container').remove();
			$('#map .map-square').append($container);
			$container.append($svg.documentElement);
			setUpClick($container);
			setUpZoom($container);
			setUpDrag($container);
			$map.removeClass('loading');
			onLoad();
		});
	}

	function onLoad() {
		$loading = false;
		for(var $n = 0; $n < $onLoad.length; $n++) {
			$onLoad[$n]();
		}
	}

	function updateUnitBox($type, $id, $units) {
		var $box = $('#unitBox-'+$id);
		if($type == 'numeric') {
			$box.attr('fill', 'url('+getUnitPattern($units)+') none');
			$box.show();
		} else if($type == 'default') {
			if($units >= 50) {
				$units = 50;
			} else if($units >= 40) {
				$units = 40;
			} else if($units >= 30) {
				$units = 30;
			} else if($units > 20) {
				$units = 20;
			}
			$box.attr('fill', 'url(#pattern-units-'+$units+') none');
			$box.show();
		} else {
			$box.hide();
		}
	}

	function addOnLoad($function) {
		if($loading) {
			$onLoad.push($function);
		} else {
			$function();
		}
	}

	function makeSVG($elem, $attr) {
		var $k,
		$out = document.createElementNS('http://www.w3.org/2000/svg', $elem);
		if($attr !== undefined) {
			for($k in $attr) {
				$out.setAttribute($k, $attr[$k]);
			}
		}
		return $out;
	}

	$(function() {
		var $img = $('#map .map-square img');
		loadMap($img.attr('src'), $img.attr('data-unit-graphics'));
	});

	return {
		onLoad: addOnLoad,
		updateUnitBox: updateUnitBox
	};
})(jQuery);