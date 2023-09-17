function initMap() {
  var map = new google.maps.Map(document.getElementById('map-container'), {
    zoom: 10,
    center: { lat: 0, lng: 0 },
  });

  var inputA = document.getElementById('locationA');
  var inputB = document.getElementById('locationB');
  var options = { types: ['geocode'] };
  new google.maps.places.Autocomplete(inputA, options);
  new google.maps.places.Autocomplete(inputB, options);

  var directionsService = new google.maps.DirectionsService();
  var directionsRenderer = new google.maps.DirectionsRenderer({ map: map });

  document
    .getElementById('calculate-fare')
    .addEventListener('click', function () {
      calculateFare(directionsService, directionsRenderer);
    });
}

function calculateFare(directionsService, directionsRenderer) {
  var start = document.getElementById('locationA').value;
  var end = document.getElementById('locationB').value;
  directionsService.route(
    {
      origin: start,
      destination: end,
      travelMode: 'DRIVING',
    },
    function (response, status) {
      if (status === 'OK') {
        directionsRenderer.setDirections(response);
        var distanceInMiles =
          response.routes[0].legs[0].distance.value * 0.000621371; // distance in Miles
        var totalFares = Object.values(gmfcRideNames).map((fare) =>
          (distanceInMiles * fare).toFixed(2)
        );

        // Creating the table based on those values:
        var tableHtml = '<div class="gmfc-responsive-table"><table>';
        tableHtml +=
          '<tr><th>Copmany</th><th>Distance</th><th>Total Fare</th></tr>';
        for (let i = 0; i < Object.keys(gmfcRideNames).length; i++) {
          tableHtml += '<tr>';
          tableHtml += '<td>' + Object.keys(gmfcRideNames)[i] + '</td>';

          tableHtml += '<td>' + distanceInMiles.toFixed(2) + ' Miles</td>';
          tableHtml += '<td>' + totalFares[i] + '$</td>';
          tableHtml += '</tr>';
        }
        tableHtml += '</table></div>';
        document.getElementById('fare').innerHTML = tableHtml;
      } else {
        window.alert('Directions request failed due to ' + status);
      }
    }
  );
}
