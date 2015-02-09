<?php 

require('config.php');

try {

    //var_dump(getStopsNear(46.834672,-71.29809));die();
    if(isset($_GET['lat']) && isset($_GET['lon'])){
        $stops = getStopsNear($_GET['lat'],$_GET['lon']);
        foreach ($stops as $key => $stop) {
            $stops[$key]['stop_name'] = utf8_encode($stop['stop_name']);
        }
        echo json_encode($stops);
    }
    else if(isset($_GET['stop'])){
        $times = getTimesForStop($_GET['stop']);
        $routes = array();
        if(!empty($times)){
            foreach($times as $time){
                if(!empty($time['trips'])){
                    $bus_number = str_replace('"', '', $time['trips'][0]['routes'][0]['route_short_name']);
                    $bus_direction = str_replace('"', '', $time['trips'][0]['trip_headsign']);
                    $bus_ident = preg_replace('/\s+/', '', $bus_number.$bus_direction);
                    if(!isset($routes[$bus_ident])){
                        $routes[$bus_ident] = array(
                            'bus_number' => $bus_number,
                            'bus_direction' => $bus_direction,
                            'times' => array($time['departure_time'])
                        );
                    }
                    else{
                        if(array_search($time['departure_time'],$routes[$bus_ident]['times']) === false){
                            $routes[$bus_ident]['times'][] = $time['departure_time'];
                        }
                    }
                }
            }
        }
        echo json_encode($routes);
    }
    
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}

function getStopsNear($lat = 46.834672,$lon = -71.29809){
    $pdo = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $selectStops = "SELECT * FROM stops WHERE ( 6371 * acos( cos( radians(:lat) ) * cos( radians( stop_lat ) ) * cos( radians( stop_lon ) - radians(:lon) ) + sin( radians(:lat) ) * sin( radians( stop_lat ) ) ) ) < 0.5
                            ORDER BY ( 6371 * acos( cos( radians(:lat) ) * cos( radians( stop_lat ) ) * cos( radians( stop_lon ) - radians(:lon) ) + sin( radians(:lat) ) * sin( radians( stop_lat ) ) ) ) 
                            LIMIT 0 , 20";
    
    $statement=$pdo->prepare($selectStops);
    $statement->execute(array(':lat' => $lat,':lon' => $lon));
    $results=$statement->fetchAll(PDO::FETCH_ASSOC);
    
   /* foreach($results as $key => $result){
        $results[$key]['times'] = getTimesForStop($result['stop_id']);
    }*/
    
    return $results;
}

function getTimesForStop($stop){

    $pdo = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $selectTimes = "SELECT *
                    FROM stop_times
                    WHERE stop_id = :stop
                    AND departure_time >= CURTIME()
                    AND departure_time <= ADDTIME(CURTIME(),'01:30:00')
                    ORDER BY departure_time";
                        
    $statement=$pdo->prepare($selectTimes);
    $statement->execute(array(':stop' => $stop));
    $results=$statement->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($results as $key => $result){
        $results[$key]['trips'] = getTripsForTrip($result['trip_id']);
    }
    
    return $results;
}

function getTripsForTrip($trip){
    $pdo = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $selectTrips = "SELECT *
                    FROM trips
                    WHERE trip_id LIKE CONCAT(:trip,'%-',CASE
                    	WHEN DAYOFWEEK(CURDATE()) = 1 THEN '______1'
                    	WHEN DAYOFWEEK(CURDATE()) = 2 THEN '1______'
                    	WHEN DAYOFWEEK(CURDATE()) = 3 THEN '_1_____'
                    	WHEN DAYOFWEEK(CURDATE()) = 4 THEN '__1____'
                    	WHEN DAYOFWEEK(CURDATE()) = 5 THEN '___1___'
                    	WHEN DAYOFWEEK(CURDATE()) = 6 THEN '____1__'
                    	WHEN DAYOFWEEK(CURDATE()) = 7 THEN '_____1_'
                    	ELSE 'multiint-_______'
                    END)";
                        
    $statement=$pdo->prepare($selectTrips);
    $statement->execute(array(':trip' => $trip));
    $results=$statement->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $key => $result) {
        $results[$key]['routes'] = getRoutesForRoute($result['route_id']);
    }
    
    return $results;
}

function getRoutesForRoute($route){
    $pdo = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $selectRoutes = "SELECT *
                    FROM routes
                    WHERE route_id = :route";
                        
    $statement=$pdo->prepare($selectRoutes);
    $statement->execute(array(':route' => $route));
    $results=$statement->fetchAll(PDO::FETCH_ASSOC);
    
    return $results;
}

?>