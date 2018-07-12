function myFunctionToDoSomething(path,value){
    var seesion_day = $('#id_session_date_day').val();
    var seesion_month = $('#id_session_date_month').val();
    var seesion_year = $('#id_session_date_year').val();
    var seesion_hour = $('#id_session_date_hour').val();
    var seesion_minute = $('#id_session_date_minute').val();
    var session_date = toTimestamp(seesion_year + ' ' + seesion_month + ' ' + seesion_day + ' ' + seesion_hour + ':' + seesion_minute);
    var seesion_day_end = $('#id_session_date_end_day').val();
    var seesion_month_end = $('#id_session_date_end_month').val();
    var seesion_year_end = $('#id_session_date_end_year').val();
    var seesion_hour_end = $('#id_session_date_end_hour').val();
    var seesion_minute_end = $('#id_session_date_end_minute').val();
    var session_date_end = toTimestamp(seesion_year_end + ' ' + seesion_month_end + ' ' + seesion_day_end + ' ' + seesion_hour_end + ':' + seesion_minute_end);
    $.ajax({
        url : path + "/course/format/classroom/getClassroom.php?location_id=" + value + "&session_date" + session_date + "&session_date_end=" + session_date_end,
        cache : false,
        success : function(html){
            $("#classroom_dropdown").html(html);
        }
    });
}
function toTimestamp(strDate) {
    var datum = Date.parse(strDate);
    return datum / 1000;
}
initAutocomplete();
function initAutocomplete() {
    var map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: -33.8688, lng: 151.2195},
        zoom: 13,
        mapTypeId: 'roadmap'
    });

    // Create the search box and link it to the UI element.
    var input = document.getElementById('id_address');
    var searchBox = new google.maps.places.SearchBox(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

    var markers = [];
    // Listen for the event fired when the user selects a prediction and retrieve.
    // More details for that place.
    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();
        if (places.length == 0) {
            return;
        }
        // Clear out the old markers.
        markers.forEach(function(marker) {
            marker.setMap(null);
        });
        markers = [];

        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
            if (!place.geometry) {
                console.log("Returned place contains no geometry");
                return;
            }
            var icon = {
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25)
            };

            // Create a marker for each place.
            markers.push(new google.maps.Marker({
                map: map,
                icon: icon,
                title: place.name,
                position: place.geometry.location
            }));

            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });
}