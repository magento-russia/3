<?php
use Df\Core\Format\Html;
/**
 * @param string $class
 * @param string|null $content
 * @return string
 */
function df_div($class, $content = null) {return df_tag('div', ['class' => $class], $content);}

/**
 * 2016-11-13
 * @param string[] $args
 * @return string|string[]
 */
function df_html_b(...$args) {return df_call_a(function($s) {return df_tag('b', [], $s);}, $args);}

/**
 * @used-by df_html_select_yesno()
 * @used-by Df_Admin_Block_Column_Select::renderHtml()
 * @used-by Df_Checkout_Block_Frontend_Ergonomic_Address_Field_Country::getDropdownAsHtml()
 * @param array(int|string => string)|array(array(string => int|string|mixed[])) $options
 * @param string|null $selected [optional]
 * @param array(string => string) $attributes [optional]
 * @return string
 */
function df_html_select(array $options, $selected = null, array $attributes = []) {return
	Html\Select::render($options, $selected, $attributes)
;}

/**
 * @used-by app/design/adminhtml/rm/default/template/df/access_control/tab.phtml
 * @param bool|null $selected [optional]
 * @param array(string => string) $attributes [optional]
 * @return string
 */
function df_html_select_yesno($selected = null, array $attributes = []) {
	return df_html_select(['нет', 'да'], is_null($selected) ? null : (int)$selected, $attributes);
}

/**
 * @param string $src
 * @return string
 */
function df_script_external($src) {return
	!$src ? '' : df_tag('script', ['src' => $src, 'type' => 'text/javascript'])
;}

/**
 * @param string $body
 * @return string
 */
function df_script_local($body) {return
	!$body ? '' : df_tag('script', ['type' => 'text/javascript'], $body)
;}

/**
 * 2015-12-21
 * 2015-12-25: Пустой тег style приводит к белому экрану в Chrome: <style type='text/css'/>.
 * @param string $css
 * @return string
 */
function df_style_inline($css) {return !$css ? '' : df_tag('style', ['type' => 'text/css'], $css);}

/**
 * 2015-04-16
 * Отныне значением атрибута может быть массив:
 * @see \Df\Core\Format\Html\Tag::getAttributeAsText()
 * Передавать в качестве значения массив имеет смысл, например, для атрибута «class».
 *
 * 2016-05-30
 * Отныне в качестве параметра $attributes можно передавать строку вместо массива.
 * В этом случае значение $attributes считается классом CSS формируемого элемента.
 *
 * @used-by df_div()
 * @param string $tag
 * @param string|array(string => string|string[]|int|null) $attributes [optional]
 * @param string $content [optional]
 * @param bool $multiline [optional]
 * @return string
 */
function df_tag($tag, $attributes = [], $content = null, $multiline = null) {
	if (!is_array($attributes)) {
		$attributes = ['class' => $attributes];
	};
	return Html\Tag::render($tag, $attributes, $content, $multiline);
}

/**
 * 2016-11-17
 * @param string $text
 * @param string[] $url
 * @return string
 */
function df_tag_a($text, ...$url) {return df_tag('a', ['href' => implode($url)], $text);}

/**
 * 2016-11-17
 * @param string $text
 * @param string[] $url
 * @return string
 */
function df_tag_ab($text, ...$url) {return
	df_tag('a', ['href' => implode($url), 'target' => '_blank'], $text)
;}

/**
 * 2016-10-24
 * @param string $content
 * @param bool $condition
 * @param string $tag
 * @param string|array(string => string|string[]|int|null) $attributes [optional]
 * @param bool $multiline [optional]
 * @return string
 */
function df_tag_if($content, $condition, $tag, $attributes = [], $multiline = null) {return
	!$condition ? $content : df_tag($tag, $attributes, $content, $multiline)
;}

/**
 * @param string[] $items
 * @param bool $isOrdered [optional]
 * @param string|null $cssList [optional]
 * @param string|null $cssItem [optional]
 * @return string
 */
function df_tag_list(array $items, $isOrdered = false, $cssList = null, $cssItem = null) {return
	Html\ListT::render($items, $isOrdered, $cssList, $cssItem
);}