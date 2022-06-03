import urllib.request, json, datetime

url       = "https://feiertage-api.de/api/?nur_daten=1&jahr="
now      = datetime.date.today()
years    = [ now.year, now.year + 1 ]
holidays = []

for year in years:
    with urllib.request.urlopen( url + str (year) ) as response:
        data = json.loads( response.read() )

        for key, value in data.items():
            holidays.append( value )

with open( "i18n/holidays.php", "w" ) as holiday_file:
    holiday_array = "\nreturn array(\n\t'DE' => array(\n"

    for holiday in holidays:
        holiday_array = holiday_array + "\t\t'" + holiday + "'" + ",\n"

    holiday_array = holiday_array + "\t),\n);"

    holiday_file.write("""<?php
/**
 * Holidays
 *
 * Returns an array of holidays.
 *
 * @package WooCommerceGermanizedDHL/i18n
 * @version 1.0.0
 */
defined( 'ABSPATH' ) || exit;
""" + holiday_array + "\n" )
    holiday_file.close()