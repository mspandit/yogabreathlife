///////////////////////////
var upoint = null;
var zoomlevel = 6;
var maptype = G_NORMAL_MAP;

function ip_info(div)
{
	if(typeof(u006) == "undefined") return;

	var map = new GMap2(document.getElementById(div));

	map.addControl(new uOwnZoomIn());
	map.addControl(new uOwnZoomOut());
	map.addControl(new uOwnChangeMapType());
	map.addControl(new uOwnCenter());
	uOwnWheelZoom(map,div); 

	var mp_info = u006[0];
	var lat = parseFloat(mp_info['lat']);
	var lng = parseFloat(mp_info['lng']);
	var tooltip = mp_info['ip'];
	upoint  = new GLatLng(lat,lng);

	map.setCenter(upoint, zoomlevel, maptype);

	for (var i in u006)
	{
		mp_info = u006[i];

		lat = parseFloat(mp_info['lat']);
		lng = parseFloat(mp_info['lng']);
		tooltip = mp_info['ip'];
		icon	= new GIcon(G_DEFAULT_ICON,ip_infoL10n.url+'icon'+ip_infoL10n.color+'.png');

		var marker = new GMarker(new GLatLng(lat,lng), {icon:icon,title:tooltip,draggable:false});
		map.addOverlay(marker);
	}
}

function uOwnZoomIn() {}

uOwnZoomIn.prototype = new GControl();
uOwnZoomIn.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var zoomInDiv = document.createElement('img');
	zoomInDiv.setAttribute('src', ip_infoL10n.url+'zoommapplus'+ip_infoL10n.color+'.png');
	zoomInDiv.setAttribute('alt', ip_infoL10n.zoomtight);
	zoomInDiv.setAttribute('title', ip_infoL10n.zoomtight);
  	container.appendChild(zoomInDiv);

  	GEvent.addDomListener(zoomInDiv, 'click', function() {map.zoomIn();});

  	map.getContainer().appendChild(container);
  	return container;
}
uOwnZoomIn.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(3, 3));
}

///////////////////////////
function uOwnZoomOut() {}

uOwnZoomOut.prototype = new GControl();
uOwnZoomOut.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var zoomOutDiv = document.createElement('img');
	zoomOutDiv.setAttribute('src', ip_infoL10n.url+'zoommapmoin'+ip_infoL10n.color+'.png');
	zoomOutDiv.setAttribute('alt', ip_infoL10n.zoomwide);
	zoomOutDiv.setAttribute('title', ip_infoL10n.zoomwide);
 	container.appendChild(zoomOutDiv);

  	GEvent.addDomListener(zoomOutDiv, 'click', function() {map.zoomOut();});

  	map.getContainer().appendChild(container);
  	return container;
}
uOwnZoomOut.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(3, 22));
}

///////////////////////////
function uOwnCenter() {}

uOwnCenter.prototype = new GControl();
uOwnCenter.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var centerDiv = document.createElement('img');
	centerDiv.setAttribute('src', ip_infoL10n.url+'centermap'+ip_infoL10n.color+'.png');
	centerDiv.setAttribute('alt', ip_infoL10n.center);
	centerDiv.setAttribute('title', ip_infoL10n.center);
 	container.appendChild(centerDiv);

  	GEvent.addDomListener(centerDiv, 'click', function() {map.setCenter(upoint);});

  	map.getContainer().appendChild(container);
  	return container;
}
uOwnCenter.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(3, 3));
}

///////////////////////////
function uOwnChangeMapType() {}

uOwnChangeMapType.prototype = new GControl();
uOwnChangeMapType.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var changeMap = document.createElement('img');
	changeMap.setAttribute('src', ip_infoL10n.url+'controlmap'+ip_infoL10n.color+'.png');
	changeMap.setAttribute('alt', ip_infoL10n.changemap);
	changeMap.setAttribute('title', ip_infoL10n.changemap);
  	container.appendChild(changeMap);

  	GEvent.addDomListener(changeMap, 'click', function() 
	{
		switch (true)
		{
			case (G_NORMAL_MAP == map.getCurrentMapType()):
				map.setMapType(G_SATELLITE_MAP);
			break;
			case (G_SATELLITE_MAP == map.getCurrentMapType()):
				map.setMapType(G_HYBRID_MAP);
			break;
			case (G_HYBRID_MAP == map.getCurrentMapType()):
				map.setMapType(G_NORMAL_MAP);
			break;
		}
	});

  	map.getContainer().appendChild(container);
  	return container;
}
uOwnChangeMapType.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(3, 22));
}
///////////////////////////
function uOwnWheelZoom(map,div) 
{
      GEvent.addDomListener(document.getElementById(div), "DOMMouseScroll",function(e) 
	{
		if (typeof e.preventDefault  == 'function') e.preventDefault();
		if (typeof e.stopPropagation == 'function') e.stopPropagation();
		if (e.detail)
		{
			if (e.detail < 0)			{ map.zoomIn(); }
			else if (e.detail > 0)		{ map.zoomOut(); }
		}
	}); // Firefox
     	GEvent.addDomListener(document.getElementById(div), "mousewheel",function(e) 
	{
		if (window.event) 
		{
			window.event.cancelBubble = true;
			window.event.returnValue  = false;
		}
		if (e.wheelDelta)
		{
			if (e.wheelDelta > 0)		{ map.zoomIn(); }
			else if (e.wheelDelta < 0)	{ map.zoomOut(); }
		}
	}); // IE
}