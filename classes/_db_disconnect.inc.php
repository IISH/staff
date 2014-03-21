<?php 
// close ALL connections
if ( $dbhandleProtime ) {
	@mssql_close( $dbhandleProtime );
}
if ( $dbhandleTimecard ) {
    @mssql_close( $dbhandleTimecard );
}
if ( $dbhandlePresentornot ) {
    @mysql_close( $dbhandlePresentornot );
}
