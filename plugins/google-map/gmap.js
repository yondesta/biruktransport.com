window.marker = null;

function initialize() {
  var map;

  var dawi_building = new google.maps.LatLng(8.96085035528968, 38.76405607970902); 

  var style = [{
    "featureType": "road.highway",
    "elementType": "geometry",
    "stylers": [{
        "saturation": -100
      },
      {
        "lightness": -8
      },
      {
        "gamma": 1.18
      }
    ]
  }, {
    "featureType": "road.arterial",
    "elementType": "geometry",
    "stylers": [{
        "saturation": -100
      },
      {
        "gamma": 1
      },
      {
        "lightness": -24
      }
    ]
  }, {
    "featureType": "poi",
    "elementType": "geometry",
    "stylers": [{
      "saturation": -100
    }]
  }, {
    "featureType": "administrative",
    "stylers": [{
      "saturation": -100
    }]
  }, {
    "featureType": "transit",
    "stylers": [{
      "saturation": -100
    }]
  }, {
    "featureType": "water",
    "elementType": "geometry.fill",
    "stylers": [{
      "saturation": -100
    }]
  }, {
    "featureType": "road",
    "stylers": [{
      "saturation": -100
    }]
  }, {
    "featureType": "administrative",
    "stylers": [{
      "saturation": -100
    }]
  }, {
    "featureType": "landscape",
    "stylers": [{
      "saturation": -100
    }]
  }, {
    "featureType": "poi",
    "stylers": [{
      "saturation": -100
    }]
  }, {}];

  var mapOptions = {
    // SET THE CENTER
    center: dawi_building,

    // SET THE MAP STYLE & ZOOM LEVEL
    mapTypeId: google.maps.MapTypeId.ROADMAP,

    // SET THE BACKGROUND COLOUR
    backgroundColor: "#000",

    // REMOVE ALL THE CONTROLS EXCEPT ZOOM
    zoom: 17,
    panControl: false,
    zoomControl: true,
    mapTypeControl: false,
    scaleControl: false,
    streetViewControl: false,
    overviewMapControl: false,
    zoomControlOptions: {
      style: google.maps.ZoomControlStyle.LARGE
    }

  }
  map = new google.maps.Map(document.getElementById('map'), mapOptions);
  // SET THE MAP TYPE
  var mapType = new google.maps.StyledMapType(style, {
    name: "Grayscale"
  });
  map.mapTypes.set('grey', mapType);
  map.setMapTypeId('grey');

  //CREATE A CUSTOM PIN ICON
  var marker_image = 'plugins/google-map/images/marker.png';
  var pinIcon = new google.maps.MarkerImage(marker_image, null, null, null, new google.maps.Size(74, 73));

  marker = new google.maps.Marker({
    position: nottingham,
    map: map,
    icon: pinIcon,
    title: 'eventre'
  });
}

if ($('#map').length) {
  google.maps.event.addDomListener(window, 'load', initialize);
}
