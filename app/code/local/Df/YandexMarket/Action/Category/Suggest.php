<?php
namespace Df\YandexMarket\Action\Category;
use Df\YandexMarket\Categories as C;
class Suggest extends \Df_Core_Model_Action {
	/**
	 * @override
	 * @see Df_Core_Model_Action::generateResponseBody()
	 * @used-by Df_Core_Model_Action::responseBody()
	 * @return string
	 */
	protected function generateResponseBody() {
		/** @var string $q */
		$q = df_request('query');
		return df_json_encode(['query' => $q, 'suggestions' =>
			df_cache_get_simple(md5($q), function() use ($q) {return
				/**
				 * 2016-11-07
				 * @uses array_values() надо использовать обязательно,
				 * иначе результат будет содержать ключи со случайными идентификаторами:
				 * мы их создали функцией @see df_uid() в методе @see processRow()
				 * Такие ключи приведут к сбою в JavaScript:
				 * скрипт будет считать, что получил не массив результатов, а единственный результат-объект.
				 */
				array_values(array_filter(C::paths(), function($path) use($q) {return
					df_contains($path, $q)
				;}))
			;})
		]);
	}

	/**
	 * @override
	 * @see Df_Core_Model_Action::contentType()
	 * @used-by Df_Core_Model_Action::getResponseLogFileExtension()
	 * @used-by Df_Core_Model_Action::processPrepare()
	 * @return string
	 */
	protected function contentType() {return 'application/json';}
}