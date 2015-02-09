mysqlimport --ignore-lines=1 --fields-terminated-by=, --fields-optionally-enclosed-by='"' --local -u root -p rtcquebec googletransit/routes.txt
mysqlimport --ignore-lines=1 --fields-terminated-by=, --fields-optionally-enclosed-by='"' --local -u root -p rtcquebec googletransit/stops.txt
mysqlimport --ignore-lines=1 --fields-terminated-by=, --fields-optionally-enclosed-by='"' --local -u root -p rtcquebec googletransit/stop_times.txt
mysqlimport --ignore-lines=1 --fields-terminated-by=, --fields-optionally-enclosed-by='"' --local -u root -p rtcquebec googletransit/trips.txt