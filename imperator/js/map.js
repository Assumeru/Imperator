$(function($) {
	var $currentHover;

	function setUpZoom($container) {
		var $controls = $('#map .map-controls');
		$controls.find('.zoom-in').click(zoomIn);
		$controls.find('.zoom-out').click(zoomOut);
		$controls.show();
		$container.on('wheel', zoomScroll);
	}

	function zoomScroll($e) {
		if($e.originalEvent !== undefined) {
			if($e.originalEvent.deltaY > 0) {
				zoomOut($e);
			} else {
				zoomIn($e);
			}
		}
	}

	function zoomIn($e) {
		$e.preventDefault();
		zoomMap(10);
	}

	function zoomOut($e) {
		$e.preventDefault();
		zoomMap(-10);
	}

	function zoomMap($amount) {
		var $svg = $('#map .map-container svg'),
		$height = parseInt($svg.attr('height')),
		$new = $height + $amount;
		if($new > 10 && $new < 200) {
			$svg.attr('height', $new+'%');
		}
	}

	function setUpClick($container) {
		var $territories = $container.find('g[id]');
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
			$popup.css('top', ($e.pageY - $container.offset().top)+'px');
			$popup.css('left', ($e.pageX - $container.offset().left)+'px');
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
			$map.removeClass('loading');
		});
	}

	loadMap($('#map .map-square > img').attr('src'));
});