<?php
/**
 * @param int $levelsToSkip
 * Позволяет при записи стека вызовов пропустить несколько последних вызовов функций,
 * которые и так очевидны (например, вызов данной функции, вызов df_bt() и т.п.)
 * @return void
 */
function df_bt($levelsToSkip = 0) {
	/** @var array $bt */
	$bt = array_slice(debug_backtrace(), $levelsToSkip);
	/** @var array $compactBT */
	$compactBT = [];
	/** @var int $traceLength */
	$traceLength = count($bt);
	/**
	 * 2015-07-23
	 * 1) Удаляем часть файлового пути до корневой папки Magento.
	 * 2) Заменяем разделитель папок на унифицированный.
	 */
	/** @var string $bp */
	$bp = BP . DIRECTORY_SEPARATOR;
	/** @var bool $nonStandardDS */
	$nonStandardDS = DIRECTORY_SEPARATOR !== '/';
	for ($traceIndex = 0; $traceIndex < $traceLength; $traceIndex++) {
		/** @var array $currentState */
		$currentState = dfa($bt, $traceIndex);
		/** @var array(string => string) $nextState */
		$nextState = dfa($bt, 1 + $traceIndex, []);
		/** @var string $file */
		$file = str_replace($bp, '', dfa($currentState, 'file'));
		if ($nonStandardDS) {
			$file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
		}
		$compactBT[]= array(
			'Файл' => $file
			,'Строка' => dfa($currentState, 'line')
			,'Субъект' =>
				!$nextState
				? ''
				: df_ccc('::', dfa($nextState, 'class'), dfa($nextState, 'function'))
			,'Объект' =>
				!$currentState
				? ''
				: df_ccc('::', dfa($currentState, 'class'), dfa($currentState, 'function'))
		);
	}
	df_report('bt-{date}-{time}.log', print_r($compactBT, true));
}

/**
 * @used-by \Df\C1\Cml2\Action\Catalog\Import::_process()
 * @used-by \Df\Qa\Message::message()
 * @used-by \Df\Shipping\Collector::call()
 * @return void
 */
function df_context() {
	/** @var mixed[] $args */
	$args = func_get_args();
	/** @var int $count */
	$count = func_num_args();
	df_assert_gt0($count);
	if (is_array($args[0])) {
		df_map('call_user_func_array', $args, [], __FUNCTION__);
	}
	else {
		df_assert_between($count, 2, 3);
		\Df\Qa\Context::add($args[0], $args[1], dfa($args, 2, 0));
	}
}

/**
 * 2015-04-05
 * @used-by \Df\Core\Exception\InvalidObjectProperty::__construct()
 * @used-by \Df\Core\Validator::check()
 * @param mixed $value
 * @param bool $addQuotes [optional]
 * @return string
 */
function df_debug_type($value, $addQuotes = true) {
	/** @var string $result */
	if (is_object($value)) {
		$result = 'объект класса ' . get_class($value);
	}
	else if (is_array($value)) {
		$result = sprintf('массив с %d элементами', count($value));
	}
	else if (is_null($value)) {
		$result = 'NULL';
	}
	else {
		$result = sprintf('%s (%s)', df_string($value), gettype($value));
	}
	return !$addQuotes ? $result : df_quote_russian($result);
}

/**
 * @param string $nameTemplate
 * @param string $message
 * @param string $subfolder [optional]
 * @param string $datePartsSeparator [optional]
 * @return void
 */
function df_report($nameTemplate, $message, $subfolder = '', $datePartsSeparator = '-') {
	df_file_put_contents(
		df_file_name(
			df_cc_path(Mage::getBaseDir('var'), 'log', $subfolder)
			,$nameTemplate
			,$datePartsSeparator
		)
		,$message
	);
}