<?php

namespace Core;

class Geo
{
	/**
	 * Earth radius (km) for haversine formula
	 * @const float
	 */
	const EARTH_RADIUS = 6378.1;

	/**
	 * Degrees, minutes, seconds format separator
	 * @const string
	 */
	const DMS_SEPARATOR = '.';

	/**
	 * Latitude & longitude separator for string representation
	 */
	const LAT_LON_SEPARATOR = ',';

	/**
	 * Latitude keyword
	 * @const string
	 */
	const LAT = 'lat';

	/**
	 * Longitude keyword
	 * @const string
	 */
	const LON = 'lon';

	/**
	 * Get haversine SQL formula
	 *
	 * @param  float  $lat1
	 * @param  float  $lon1
	 * @param  string|float $lat2 'Lat' column name by default
	 * @param  string|float $lon2 'Lon' column name by default
	 * @return string
	 */
	public static function haversineSql($lat1, $lon1, $lat2 = 'Lat', $lon2 = 'Lon')
	{
		$res = 'acos('
				. 'cos(radians(' . $lat2 . ')) * cos(radians(' . $lon2 . ')) * cos(radians(' . $lat1 . ')) * cos(radians(' . $lon1 . ')) + '
				. 'cos(radians(' . $lat2 . ')) * sin(radians(' . $lon2 . ')) * cos(radians(' . $lat1 . ')) * sin(radians(' . $lon1 . ')) + '
				. 'sin(radians(' . $lat2 . ')) * sin(radians(' . $lat1 . '))'
			. ') * ' . static::EARTH_RADIUS;

		return $res;
	}

	/**
	 * Calculate distance by haversine formula
	 *
	 * @param float $lat1
	 * @param float $lon1
	 * @param float $lat2
	 * @param float $lon2
	 * @return float km
	 */
	public static function haversineDistance($lat1, $lon1, $lat2, $lon2)
	{
		// convert from degrees to radians
		$lat1 = deg2rad($lat1);
		$lon1 = deg2rad($lon1);
		$lat2 = deg2rad($lat2);
		$lon2 = deg2rad($lon2);

		$latDelta = $lat2 - $lat1;
		$lonDelta = $lon2 - $lon1;

		$angle = 2 * asin(
				sqrt(
					pow(sin($latDelta / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)
				)
			);

		return $angle * static::EARTH_RADIUS;
	}
}
