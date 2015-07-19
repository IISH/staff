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

				if ( preg_match("/^[0-9]{3}/i", $item) || preg_match("/^[0-9]{1}/i", $item) ) {
					$ret .= $separator . '<a href="#" onClick="showImageDiv(\'' . Settings::get('floorplan_level' . $item[0]) . '\');">' . $item . "</a>";
				} elseif ( preg_match("/^[0-9]{1}[^0-9]?/i", $item) ) {
					$ret .= $separator . '<a href="' . Settings::get('floorplan_level0') . '" target="_blank">' . $item . "</a>";
				} else {
					$ret .= $separator . $item;
				}

				$separator = ', ';
			}
		}

		return $ret;
	}
}