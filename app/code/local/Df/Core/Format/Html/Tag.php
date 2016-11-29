<?php
namespace Df\Core\Format\Html;
class Tag extends \Df_Core_Model {
	/** @return string */
	private function _render() {return
		"<{$this->openTagWithAttributesAsText()}"
		. ($this->shouldAttributesBeMultiline() ? "\n" : '')
		. (!$this->content() && $this->shortTagAllowed() ? '/>' : ">{$this->content()}</{$this->tag()}>")
	;}

	/** @return array(string => string) */
	private function attributes() {return $this->cfg(self::$P__ATTRIBUTES, []);}

	/** @return string */
	private function content() {return dfc($this, function() {
		/** @var string $content */
		$content = df_trim($this[self::$P__CONTENT], "\n");
		return
			$this->tagIs('pre', 'code') || !df_contains($content, "\n")
			? $content
			: "\n" . df_tab_multiline($content) . "\n"
		;
	});}

	/** @return string */
	private function openTagWithAttributesAsText() {return df_cc_s(
		$this->tag()
		,$this->shouldAttributesBeMultiline() ? "\n" : null
		,call_user_func(
			$this->shouldAttributesBeMultiline() ? 'df_tab_multiline' : 'df_nop'
			,implode(
				$this->shouldAttributesBeMultiline() ? "\n" :  ' '
				,df_clean(df_map_k(function($name, $value) {
					df_param_string_not_empty($name, 0);
					/**
					 * 2015-04-16
					 * Передавать в качестве $value массив имеет смысл, например, для атрибута «class».
					 *
					 * 2016-11-29
					 * Не использую @see df_e(), чтобы сохранить двойные кавычки (data-mage-init)
					 * и в то же время сконвертировать одинарные
					 * (потому что значения атрибутов мы ниже обрамляем именно одинарными).
					 */
					$value = htmlspecialchars(
						str_replace("'", '&apos;', !is_array($value) ? $value : df_cc_s($value))
						,ENT_NOQUOTES
						,'UTF-8'
						,false
					);
					return '' === $value ? '' : "{$name}='{$value}'";
				}, $this->attributes()))
			)
		)
	);}

	/** @return bool */
	private function shortTagAllowed() {return !$this->tagIs('div', 'script');}

	/** @return bool */
	private function shouldAttributesBeMultiline() {return dfc($this, function() {
		/** @var bool|null $result */
		$result = $this[self::$P__MULTILINE];
		return !is_null($result) ? $result : 1 < count($this->attributes());
	});}

	/**
	 * 2016-08-05
	 * @return string
	 */
	private function tag() {return dfc($this, function() {return strtolower($this[self::$P__TAG]);});}

	/**
	 * 2016-08-05
	 * @param string[] ...$tags
	 * @return bool
	 */
	private function tagIs(...$tags) {return in_array($this->tag(), $tags);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this
			->_prop(self::$P__ATTRIBUTES, DF_V_ARRAY, false)
			->_prop(self::$P__CONTENT, DF_V_STRING, false)
			->_prop(self::$P__MULTILINE, DF_V_BOOL, false)
			->_prop(self::$P__TAG, DF_V_STRING_NE)
		;
	}
	/** @var string */
	private static $P__ATTRIBUTES = 'attributes';
	/** @var string */
	private static $P__CONTENT = 'content';
	/** @var string */
	private static $P__MULTILINE = 'multiline';
	/** @var string */
	private static $P__TAG = 'tag';

	/**
	 * @param string $tag
	 * @param array(string => string) $attributes [optional]
	 * @param string $content [optional]
	 * @param bool $multiline [optional]
	 * @return string
	 */
	public static function render($tag, array $attributes = [], $content = null, $multiline = null) {return
		(new self([
			self::$P__ATTRIBUTES => $attributes
			,self::$P__CONTENT => $content
			,self::$P__MULTILINE => $multiline
			,self::$P__TAG => $tag
		]))->_render()
	;}
}