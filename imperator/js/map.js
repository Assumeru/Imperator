$(function($) {
	var $currentHover;

	function setUpHover($container) {
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
			$('#map > img').remove();
			$('#map').append($container);
			$container.append($svg.documentElement);
			setUpHover($container);
			$map.removeClass('loading');
		});
	}

	loadMap($('#map > img').attr('src'));
});