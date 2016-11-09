<?php
namespace Df\C1\Cml2\Session;
use Df\C1\Cml2\Session\ByCookie\MagentoAPI;
/**
 * 2016-11-09
 * Эта сессия идентифицирует экземпляр учётной системы.
 * Современные версии модуля 1С-Битрикс не передают всю информацию за единый сеанс обмена,
 * а вместо этого выполняют много малых сеансов: в рамках одного передают товарные остатки,
 * в рамках другого — цены, в рамках третьего — товары и т.п.
 * Идентификация экземпляра позвляет нам логически связать эти много малых сеансов
 * в единую последовательность.
 * Это позволяет в текущем сеансе обращаться к файлам прошлого сеанса
 * (переданным данным, журналу обмена).
 */
class Composite extends \Df_Core_Model_Session_Custom_Additional {
	/**
	 * @param bool|null $value [optional]
	 * @return bool|null
	 */
	public function flag_catalogHasJustBeenExported($value = null) {
		/** @var bool|null $result */
		if (is_null($value)) {
			$result = !!$this->getData(self::$P__CATALOG_HAS_JUST_BEEN_EXPORTED);
		}
		else {
			$this->setData(self::$P__CATALOG_HAS_JUST_BEEN_EXPORTED, $value);
			$result = null;
		}
		return $result;
	}

	/**
	 * @param string $type
	 * @param string $id
	 * @return string
	 */
	public function getFilePathById($type, $id) {
		df_param_string_not_empty($type, 0);
		df_param_string_not_empty($id, 1);
		/** @var string $result */
		$result = dfa($this->getFileMap($type), $id);
		if (!$result) {
			df_error(
				'По какой-то причине файл типа «%s» с идентификатором «%s»'
				. ' не был зарегистрирован в системе.'
				. "\nРабота модуля невозможна."
				. "\nОбратитесь к разработчику."
				, $type, $id
			);
		}
		return $result;
	}

	/**
	 * @param string $type
	 * @param string $id
	 * @param string $path
	 * @return void
	 */
	public function setFilePathById($type, $id, $path) {
		df_param_string_not_empty($type, 0);
		df_param_string_not_empty($id, 1);
		df_param_string_not_empty($path, 2);
		$this->setData(self::$P__FILE_MAPS, array_merge($this->getFileMap(), [
			$type => array_merge($this->getFileMap($type), [$id => $path])
		]));
	}

	/**
	 * Вызывая функцию @uses md5(), мы избавляемся от недопустимых символов
	 * в идентификаторе сессии.
	 * 2016-11-09
	 * Недостаточно учитывать только адрес IP экземпляра:
	 * для МойСклад и других облачных систем вообще нормальным будет,
	 * если разные экземпляры разных клиентов будут иметь одинаковый адрес IP.
	 * @see session_id()
	 * http://php.net/manual/function.session-id.php
	 * @override
	 * @return string
	 */
	protected function getSessionIdCustom() {return dfc($this, function() {return md5(df_ckey(
		\Mage::app()->getRequest()->getClientIp()
		,MagentoAPI::s()->getUser()->getId()
		,df_store_id()
	));});}

	/**
	 * @param string $type [optional]
	 * @return array(string => string)|array(array(string => string))
	 */
	private function getFileMap($type = null) {
		/** @var array(string => string) $maps */
		$maps = $this->getData(self::$P__FILE_MAPS) ?: [];
		return !$type ? $maps : dfa($maps, $type, []);
	}

	const NAME = __CLASS__;
	/** @var string */
	private static $P__CATALOG_HAS_JUST_BEEN_EXPORTED = 'catalog_has_just_been_exported';
	/** @var string */
	private static $P__FILE_MAPS = 'file_maps';

	/** @return self */
	public static function s() {static $r; return $r ?: $r = new self;}
}