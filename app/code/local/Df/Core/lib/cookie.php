<?php
/**
 * 2016-11-07
 * @param string $name
 * @param string|null $d [optional]
 * @return string|null
 */
function df_cookie($name = null, $d = null) {return df_request_o()->getCookie($name, $d);}