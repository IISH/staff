<?php
class static_Room {
	public static function createRoomUrl( $room ) {
		$ret = '';
		$separator = '';

		$room = trim($room);
		if ( $room == '' ) {
			return $ret;
		}

		$room = str_replace( array(',', '.', ';', ':', '/', '\\', '|'), ' ', $room);
		$arrRoom = explode(' ', $room);

		foreach ( $arrRoom as $item ) {
			if ( $item != '' ) {

				// 3 digit rooms or a single digit room
				if ( preg_match("/^[0-9]{3}/i", $item) || preg_match("/^[0-9]{1}/i", $item) ) {
					$ret .= $separator . '<a href="#" onClick="return showFloorPlan(\'' . Settings::get('floorplan_level' . $item[0]) . '\', \'' . $item[0] . '\');">' . $item . "</a>";
				// rooms on level 0, starting with a single digit and then a non-digit character
				// always level 0
				} elseif ( preg_match("/^[0-9]{1}[^0-9]?/i", $item) ) {
					$ret .= $separator . '<a href="#" onClick="return showFloorPlan(\'' . Settings::get('floorplan_level0') . '\', \'0\');">' . $item . "</a>";
				} else {
					$ret .= $separator . $item;
				}

				$separator = ', ';
			}
		}

		return $ret;
	}
}