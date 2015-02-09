var currentMode = 'home';
var stops = [];
var currentStopIndex = 0;
var routes = [];
var currentRouteIndex = 0;

simply.on('singleClick', function(e) {
  console.log(currentMode);
  if(e.button == 'select' && currentMode == 'home'){
    simply.text({ title: 'Localisation', body: 'Veuillez patienter...' },true);
    navigator.geolocation.getCurrentPosition(function(pos) {
      var coords = pos.coords;
      //coords.latitude = 46.834672;
      //coords.longitude = -71.29809;
      simply.text({ title: 'Localisé !', body: 'Recherche des arrêts proches...' },true);
      ajax({ url: 'http://rtcapi.paulcote.net/?lat='+coords.latitude+'&lon='+coords.longitude }, function(data){
        stops = JSON.parse(data);
        if(stops.length > 0){
          currentStopIndex = 0;
          routes = [];
          currentRouteIndex = 0;
          showStop();
        }
        else{
          simply.text({ title: 'Erreur', body: 'Aucun arrêt proche. Veuillez réessayer plus près d\'un arrêt.' },true);
        }
      });
    });
  }
  else if(e.button == 'select' && currentMode == 'stop'){
    simply.text({ title: (currentStopIndex+1)+'. Arrêt '+stops[currentStopIndex].stop_id, body: 'Recherche des prochains passages...' },true);
    ajax({ url: 'http://rtcapi.paulcote.net/?stop='+stops[currentStopIndex].stop_id }, function(data){
      routes = JSON.parse(data);
      if(Object.keys(routes).length > 0){
        currentRouteIndex = 0;
        showRoute();
      }
      else{
        currentMode = 'times';
        simply.text({ title: 'Erreur', body: 'Aucun passage dans la prochaine heure et demie.' },true);
      }
    });
  }
  else if(e.button == 'select' && currentMode == 'times'){
    currentRouteIndex = 0;
    showStop();
  }
  else if(e.button == 'down' && currentMode == 'stop'){
    if(currentStopIndex < stops.length-1)
      currentStopIndex++;
    else
      currentStopIndex = 0;
    showStop();
  }
  else if(e.button == 'up' && currentMode == 'stop'){
    if(currentStopIndex > 0)
      currentStopIndex--;
    else
      currentStopIndex = stops.length-1;
    showStop();
  }
  else if(e.button == 'down' && currentMode == 'times'){
    if(currentRouteIndex < Object.keys(routes).length-1)
      currentRouteIndex++;
    else
      currentRouteIndex = 0;
    showRoute();
  }
  else if(e.button == 'up' && currentMode == 'times'){
    if(currentRouteIndex > 0)
      currentRouteIndex--;
    else
      currentRouteIndex = Object.keys(routes).length-1;
    showRoute();
  }
});
  
function showStop(){
  currentMode = 'stop';
  simply.text({ title: (currentStopIndex+1)+'. Arrêt '+stops[currentStopIndex].stop_id, body: stops[currentStopIndex].stop_name.replace(/\"/g,'') },true);
}

function showRoute(){
  currentMode = 'times';
  var times = routes[Object.keys(routes)[currentRouteIndex]].times.slice(0,3);
  simply.text({ title: routes[Object.keys(routes)[currentRouteIndex]].bus_number, 
                 subtitle: routes[Object.keys(routes)[currentRouteIndex]].bus_direction,
                 //body: routes[Object.keys(routes)[currentRouteIndex]].bus_direction + "\n" + 
                 body: times.join(', ')},true);
}

simply.setText({
  title: 'RTC Quebec',
  body: 'Appuyer sur le bouton central pour commencer la recherche.',
}, true);