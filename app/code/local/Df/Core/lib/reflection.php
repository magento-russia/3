<?php
/**
 * 2016-08-10
 * @param int $offset [optional]
 * @return string
 */
function df_caller_f($offset = 0) {
	/** @var string $result */
	$result = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3 + $offset)[2 + $offset]['function'];
	/**
	 * 2016-09-06
	 * Порой бывают случаи, когда @see df_caller_f() ошибочно вызывается из @see \Closure.
	 * Добавил защиту от таких случаев.
	 */
	if (df_contains($result, '{closure}')) {
		df_error_html("The <b>df_caller_f()</b> function is wrongly called from the «<b>{$result}</b>» closure.");
	}
	return $result;
}

/**
 * 2016-08-10
 * @param int $offset [optional]
 * @return string
 */
function df_caller_m($offset = 0) {
	/** @var array(string => string) $bt */
	$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3 + $offset)[2 + $offset];
	/** @var string $method */
	return $bt['class'] . '::' . $bt['function'];
}

/**
 * 2016-08-29
 * @return string
 */
function df_caller_mh() {return df_tag('b', [], df_caller_ml(1));}

/**
 * 2016-08-31
 * @used-by df_caller_mh()
 * @param int $offset [optional]
 * @return string
 */
function df_caller_ml($offset = 0) {return '\\' . df_caller_m(1 + $offset) . '()';}

/**
 * 2016-02-08
 * Применение @uses dfa_flatten() делает возможным вызовы типа:
 * df_cc_class_uc('Aa', ['Bb', 'Cb']) => Aa\Bb\Cb
 * @see df_cc_class_uc()
 * @param string[] ...$args
 * @return string
 */
function df_cc_class(...$args) {return implode('\\', dfa_flatten($args));}

/**
 * 2016-10-15
 * @param string[] ...$args
 * @return string
 */
function df_cc_class_(...$args) {return implode('_', dfa_flatten($args));}

/**
 * 2016-03-25
 * Применение @uses dfa_flatten() делает возможным вызовы типа:
 * df_cc_class_uc('aa', ['bb', 'cc']) => Aa\Bb\Cc
 * Мы используем это в модулях Stripe и Checkout.com.
 * @see df_cc_class()
 * @param string[] ...$args
 * @return string
 */
function df_cc_class_uc(...$args) {return df_cc_class(df_ucfirst(dfa_flatten($args)));}

/**
 * 2016-03-25
 * Применение @uses dfa_flatten() делает возможным вызовы типа:
 * df_cc_class_uc('aa', ['bb', 'cc']) => Aa\Bb\Cc
 * Мы используем это в модулях Stripe и Checkout.com.
 * @see df_cc_class()
 * @param string[] ...$args
 * @return string
 */
function df_cc_class_uc_(...$args) {return df_cc_class_(df_ucfirst(dfa_flatten($args)));}

/**
 * 2016-08-10
 * Если класс не указан, то вернёт название функции.
 * Поэтому в качестве $a1 можно передавать null.
 * @param string|object|null|array(object|string)|array(string = string) $a1
 * @param string|null $a2 [optional]
 * @return string
 */
function df_cc_method($a1, $a2 = null) {return df_ccc('::',
	$a2 ? [df_cts($a1), $a2] :
		(!isset($a1['function']) ? $a1 :
			[dfa($a1, 'class'), $a1['function']]
		)
);}

/**
 * 2016-01-01
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @return string
 */
function df_class_first($c) {return df_first(df_explode_class($c));}

/**
 * 2015-12-29
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @return string
 */
function df_class_last($c) {return df_last(df_explode_class($c));}

/**
 * 2015-12-29
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * @param string|object $c
 * @return string
 */
function df_class_last_lc($c) {return df_lcfirst(df_class_last($c));}

/**
 * 2016-01-01
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @return bool
 */
function df_class_my($c) {return in_array(df_class_first($c), ['Df', 'Dfe', 'Dfr']);}

/**
 * 2016-07-10
 * Df\Payment\R\Response => Df\Payment\R\Request
 * @param string|object $c
 * @param string[] $newSuffix
 * @return string
 */
function df_class_replace_last($c, ...$newSuffix) {return
	implode(df_cld($c), array_merge(df_head(df_explode_class($c)), dfa_flatten($newSuffix)))
;}

/**
 * 2016-02-09
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @return string
 */
function df_class_second($c) {return df_explode_class($c)[1];}

/**
 * 2016-02-09
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @return string
 */
function df_class_second_lc($c) {return df_lcfirst(df_class_second($c));}

/**
 * 2016-11-25
 * «Df\Sso\Settings\Button» => «Settings\Button»
 * @param string|object $c
 * @return string
 */
function df_class_suffix($c) {return implode(df_cld($c), array_slice(df_explode_class($c), 2));}

/**
 * 2016-10-15
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 *
 * @param string|object $c
 * @return string
 */
function df_cld($c) {return df_contains(df_cts($c) , '\\') ? '\\' : '_';}

/**
 * 2016-08-04
 * 2016-08-10
 * @uses defined() не реагирует на методы класса, в том числе на статические,
 * поэтому нам использовать эту функию безопасно: https://3v4l.org/9RBfr
 * @param string|object $c
 * @param string $name
 * @param mixed|callable $def [optional]
 * @return mixed
 */
function df_const($c, $name, $def = null) {
	/** @var string $nameFull */
	$nameFull = df_cts($c) . '::' . $name;
	return defined($nameFull) ? constant($nameFull) : df_call_if($def);
}

/**
 * 2016-02-08
 * Проверяет наличие следующих классов в указанном порядке:
 * 1) <имя конечного модуля>\<окончание класса>
 * 2) $def
 * Возвращает первый из найденных классов.
 * @param object|string $c
 * @param string|string[] $suffix
 * @param string|null $def [optional]
 * @param bool $throw [optional]
 * @return string|null
 */
function df_con($c, $suffix, $def = null, $throw = true) {return
	df_con_generic(function($c, $suffix) {
		/** @var string $del */
		$del = df_cld($c);
		// 2016-11-25
		// Применение df_cc() обязательно, потому что $suffix может быть массивом.
		return df_cc($del, df_module_name($c, $del), $suffix);
	}, $c, $suffix, $def, $throw)
;}

/**
 * Инструмент парадигмы «convention over configuration».
 * 2016-10-26
 * @param \Closure $f
 * @param object|string $c
 * @param string|string[] $suffix
 * @param string|null $def [optional]
 * @param bool $throw [optional]
 * @return string|null
 */
function df_con_generic(\Closure $f, $c, $suffix, $def = null, $throw = true) {return
	dfcf(function($f, $c, $suffix, $def = null, $throw = true) {
		/** @var string $result */
		$result = $f($c, $suffix);
		return df_class_exists($result) ? $result : (
			$def ?: (!$throw ? null : df_error("The «{$result}» class is required."))
		);
	}, [$f, df_cts($c), $suffix, $def, $throw])
;}

/**
 * 2016-10-26
 * @param object|string $caller
 * @param string|string[] $suffix
 * @param string|null $def [optional]
 * @param bool $throw [optional]
 * @return string|null
 */
function df_con_child($caller, $suffix, $def = null, $throw = true) {return
	df_con_generic(function($callerC, $suffix) {return
		df_cc(df_cld($callerC), $callerC, $suffix)
	;}, $caller, $suffix, $def, $throw)
;}

/**
 * 2016-11-25
 * Возвращает имя класса с тем же суффиксом, что и $def,
 * но из папки того же модуля, которому принадлежит класс $c.
 * Результат должен быть наследником класса $def.
 * Если класс не найден, то возвращается $def.
 * Параметр $throw этой функции не нужен, потому что параметр $def обязателен.
 *
 * Пример:
 * $c => \Dfe\FacebookLogin\Button
 * $def = \Df\Sso\Settings\Button
 * Результат: «Dfe\FacebookLogin\Settings\Button»
 *
 * @param object|string $c
 * @param string $def
 * @return string|null
 */
function df_con_heir($c, $def) {return
	df_ar(df_con(df_module_name($c, '\\'), df_class_suffix($def), $def), $def)
;}

/**
 * 2016-08-29
 * @used-by dfp_method_call_s()
 * @param string|object $c
 * @param string|string[] $suffix
 * @param string $method
 * @param mixed[] $params [optional]
 * @return mixed
 */
function df_con_s($c, $suffix, $method, array $params = []) {return dfcf(
	function($c, $suffix, $method, array $params = []) {return
		call_user_func_array([df_con($c, $suffix), $method], $params)
	;}
, func_get_args());}

/**
 * 2016-07-10
 * 2016-11-25
 * Возвращает имя класса из той же папки, что и $c, но с окончанием $nameLast.
 * Пример:
 * $c => \Df\Payment\R\Response
 * $nameLast = «Exception»
 * Результат: «Df\Payment\R\Exception»
 *
 * @param object|string $c
 * @param string|string[] $nameLast
 * @param string|null $def [optional]
 * @param bool $throw [optional]
 * @return string|null
 */
function df_con_sibling($c, $nameLast, $def = null, $throw = true) {return
	df_con_generic(function($c, $nameLast) {return
		df_class_replace_last($c, $nameLast)
	;}, $c, $nameLast, $def, $throw)
;}

/**
 * 2015-08-14
 * Обратите внимание, что @uses get_class() не ставит «\» впереди имени класса:
 * http://3v4l.org/HPF9R
	namespace A;
	class B {}
	$b = new B;
	echo get_class($b);
 * => «A\B»
 *
 * 2015-09-01
 * Обратите внимание, что @uses ltrim() корректно работает с кириллицей:
 * https://3v4l.org/rrNL9
 * echo ltrim('\\Путь\\Путь\\Путь', '\\');  => Путь\Путь\Путь
 *
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 *
 * @used-by df_explode_class()
 * @used-by df_module_name()
 * @param string|object $c
 * @param string $del [optional]
 * @return string
 */
function df_cts($c, $del = '\\') {
	/** @var string $result */
	$result = df_trim_text_right(is_object($c) ? get_class($c) : ltrim($c, '\\'), '\Interceptor');
	return '\\' === $del ?  $result : str_replace('\\', $del, $result);
}

/**
 * 2016-01-29
 * @param string $c
 * @param string $del
 * @return string
 */
function df_cts_lc($c, $del) {return implode($del, df_explode_class_lc($c));}

/**
 * 2016-04-11
 * Dfe_CheckoutCom => dfe_checkout_com
 * @param string $c
 * @param string $del
 * @return string
 */
function df_cts_lc_camel($c, $del) {return implode($del, df_explode_class_lc_camel($c));}

/**
 * @param string|object $c
 * @return string[]
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 */
function df_explode_class($c) {return df_explode_multiple(['\\', '_'], df_cts($c));}

/**
 * 2016-04-11
 * Dfe_CheckoutCom => [Dfe, Checkout, Com]
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @return string[]
 */
function df_explode_class_camel($c) {return dfa_flatten(df_explode_camel(explode('\\', df_cts($c))));}

/**
 * 2016-01-14
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @return string[]
 */
function df_explode_class_lc($c) {return df_lcfirst(df_explode_class($c));}

/**
 * 2016-04-11
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * Dfe_CheckoutCom => [dfe, checkout, com]
 * @param string|object $c
 * @return string[]
 */
function df_explode_class_lc_camel($c) {return df_lcfirst(df_explode_class_camel($c));}

/**
 * «Df\YandexMarket\Yml\Document» => «yandex.market»
 * «\Df\C1\Cml2\Export\Document\Catalog» => «1c»
 * @param Varien_Object $object
 * @param string $del
 * @return string
 */
function df_module_id(Varien_Object $object, $del) {
	/** @var string $className */
	$className = get_class($object);
	/** @var string $key */
	$key = $className . $del;
	/** @var array(string => string) */
	static $cache;
	if (!isset($cache[$key])) {
		// «yandex.market»
		$cache[$key] = mb_strtolower(
			// «Yandex.Market»
			implode($del, df_explode_camel(
				// «YandexMarket»
				dfa(df_explode_class($className), 1)
			)
		));
	}
	return $cache[$key];
}

/**
 * «Dfe\AllPay\Response» => «Dfe_AllPay»
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * 2016-10-26
 * Функция успешно работает с короткими именами классов:
 * «A\B\C» => «A_B»
 * «A» => A»
 * https://3v4l.org/Jstvc
 * @param string|object $c [optional]
 * @param string $del [optional]
 * @return string
 */
function df_module_name($c, $del = '_') {return dfcf(function($c, $del) {return
	implode($del, array_slice(df_explode_class($c), 0, 2))
;}, [df_cts($c), $del]);}

/**
 * 2016-08-28
 * «Dfe\AllPay\Response» => «AllPay»
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @return string
 */
function df_module_name_short($c) {return dfcf(function($c) {return
	df_explode_class($c)[1]
;}, [df_cts($c)]);}

/**
 * 2016-02-16
 * «Dfe\CheckoutCom\Method» => «dfe_checkout_com»
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @param string $del [optional]
 * @return string
 */
function df_module_name_lc($c, $del = '_') {return
	implode($del, df_explode_class_lc_camel(df_module_name($c, '\\')))
;}

/**
 * Намеренно добавили к названию метода окончание «ByClass»,
 * чтобы название метода не конфликтовало с родительским методом
 * @see Df_Core_Model::moduleTitle()
 * «\Df\C1\Cml2\Export\Document\Catalog» => «1C:Управление торговлей»
 * 2016-10-20
 * Нельзя делать параметр $c опциональным, потому что иначе получим сбой:
 * «get_class() called without object from outside a class»
 * https://3v4l.org/k6Hd5
 * @param string|object $c
 * @return string
 */
function df_module_title($c) {
	/** @var string $moduleName */
	$moduleName = df_module_name($c);
	return df_leaf_s(df_config_node('modules', $moduleName,  'title'), $moduleName);
}