<?php

namespace EmomeIMSP;

interface ShortMessageProviderInterface
{
	/**
	 * Submit the SM via custom parameters.
	 *
	 * @param array  $params
	 */
	function submitSM($params = array());
}