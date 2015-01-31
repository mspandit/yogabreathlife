///////////////////////////

var point = null;
var zoomlevel = 6;
var maptype = G_NORMAL_MAP;

function IPuserinfo(lat,lng,div)
{
	point  = new GLatLng(lat,lng); 
	var size = new GSize(267,250);
	var map = new GMap2(document.getElementById(div), {size:size});

	map.addControl(new OwnZoomIn());
	map.addControl(new OwnZoomOut());
	map.addControl(new OwnChangeMapType());
	map.addControl(new OwnCenter());
	OwnWheelZoom(map); 

	map.setCenter(point, zoomlevel,maptype);

	tooltip = 'lat : '+lat+' lng : '+lng;

	icon	= new GIcon(G_DEFAULT_ICON,mailpress_IP_user_infoL10n.url+'icon'+mailpress_IP_user_infoL10n.color+'.png');
	var marker = new GMarker(point, {icon:icon,title:tooltip,draggable:false});
		
	map.addOverlay(marker);
}

function OwnZoomIn() {}

OwnZoomIn.prototype = new GControl();
OwnZoomIn.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var zoomInDiv = document.createElement('img');
	zoomInDiv.setAttribute('src', mailpress_IP_user_infoL10n.url+'zoommapplus'+mailpress_IP_user_infoL10n.color+'.png');
	zoomInDiv.setAttribute('alt', mailpress_IP_user_infoL10n.zoomtight);
	zoomInDiv.setAttribute('title', mailpress_IP_user_infoL10n.zoomtight);
  	container.appendChild(zoomInDiv);

  	GEvent.addDomListener(zoomInDiv, 'click', function() {map.zoomIn();});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnZoomIn.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(3, 3));
}

///////////////////////////
function OwnZoomOut() {}

OwnZoomOut.prototype = new GControl();
OwnZoomOut.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var zoomOutDiv = document.createElement('img');
	zoomOutDiv.setAttribute('src', mailpress_IP_user_infoL10n.url+'zoommapmoin'+mailpress_IP_user_infoL10n.color+'.png');
	zoomOutDiv.setAttribute('alt', mailpress_IP_user_infoL10n.zoomwide);
	zoomOutDiv.setAttribute('title', mailpress_IP_user_infoL10n.zoomwide);
 	container.appendChild(zoomOutDiv);

  	GEvent.addDomListener(zoomOutDiv, 'click', function() {map.zoomOut();});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnZoomOut.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(3, 22));
}

///////////////////////////
function OwnCenter() {}

OwnCenter.prototype = new GControl();
OwnCenter.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var centerDiv = document.createElement('img');
	centerDiv.setAttribute('src', mailpress_IP_user_infoL10n.url+'centermap'+mailpress_IP_user_infoL10n.color+'.png');
	centerDiv.setAttribute('alt', mailpress_IP_user_infoL10n.center);
	centerDiv.setAttribute('title', mailpress_IP_user_infoL10n.center);
 	container.appendChild(centerDiv);

  	GEvent.addDomListener(centerDiv, 'click', function() {map.setCenter(point);});

  	map.getContainer().appendChild(container);
  	return container;
}
OwnCenter.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(3, 3));
}

///////////////////////////
function OwnChangeMapType() {}

OwnChangeMapType.prototype = new GControl();
OwnChangeMapType.prototype.initialize = function(map) 
{
	var container = document.createElement('div');

	var changeMap = document.createElement('img');
	changeMap.setAttribute('src', mailpress_IP_user_infoL10n.url+'controlmap'+mailpress_IP_user_infoL10n.color+'.png');
	changeMap.setAttribute('alt', mailpress_IP_user_infoL10n.changemap);
	changeMap.setAttribute('title', mailpress_IP_user_infoL10n.changemap);
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
OwnChangeMapType.prototype.getDefaultPosition = function() 
{
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(3, 22));
}
///////////////////////////
function OwnWheelZoom(map) 
{
      GEvent.addDomListener(document.getElementById("IPuserinfo_map"), "DOMMouseScroll",function(e) 
	{
		if (typeof e.preventDefault  == 'function') e.preventDefault();
		if (typeof e.stopPropagation == 'function') e.stopPropagation();
		if (e.detail)
		{
			if (e.detail < 0)			{ map.zoomIn(); }
			else if (e.detail > 0)		{ map.zoomOut(); }
		}
	}); // Firefox
     	GEvent.addDomListener(document.getElementById("IPuserinfo_map"), "mousewheel",function(e) 
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