<?php
use Mage_Sales_Model_Resource_Setup as SRS;
class Df_Core_Setup extends Df_Core_Model {
	/**
	 * Метод публичен, потому что объекты данного класса могут передаваться в ресурсную модель,
	 * а уже ресурсная модель может изменять структуру БД.
	 * Обратите внимание, что метод @see Varien_Db_Adapter_Pdo_Mysql::lastInsertId()
	 * почему-то отсутствует в интерфейсе @see Varien_Db_Adapter_Interface (видимо, по недосмотру),
	 * однако используется установочными скриптами.
	 * @return Varien_Db_Adapter_Interface|Varien_Db_Adapter_Pdo_Mysql
	 */
	public function conn() {return $this->getSetup()->getConnection();}

	/**
	 * 2015-08-03
	 * Обратите внимание,
	 * что текущая версия MySQL не позволяет удобным образом
	 * условно удалять колонку таблицы только при её наличии (типа drop column if exists):
	 * http://stackoverflow.com/questions/173814
	 * Поэтому мы вынуждены использовать @uses runSilent() вместо @see run().
	 *
	 * 2016-11-04
	 * Добавил функцию @see df_db_column_exists()
	 *
	 * 2016-11-04
	 * Раньше реализация была такой:
	 * $this->runSilent("alter table {$table} drop column `{$column}`;");
	 * На самом деле, @uses Varien_Db_Adapter_Pdo_Mysql::dropColumn()
	 * всегда проверяет наличие колонки перед попыткой её удаления,
	 * причём даже в старых версиях Magento CE:
	 * https://github.com/OpenMage/magento-mirror/blob/1.4.0.0/lib/Varien/Db/Adapter/Pdo/Mysql.php#L644-L646
	 *
	 * @used-by \Df\C1\Setup::add1CIdColumnToTable()
	 * @param string $table
	 * @param string $column
	 * @return void
	 */
	public function dropColumn($table, $column) {df_conn()->dropColumn($table, $column);}

	/**
	 * @param string $table
	 * @return void
	 */
	public function dropTable($table) {df_table_drop($table, $this->conn());}

	/**
	 * Метод публичен, потому что объекты данного класса могут передаваться в ресурсную модель,
	 * а уже ресурсная модель может изменять структуру БД.
	 * @return Df_Core_Model_Resource_Setup
	 */
	public function getSetup() {return $this->cfg(self::$P__SETUP, Df_Core_Model_Resource_Setup::s());}

	/**
	 * Метод публичен, потому что объекты данного класса могут передаваться в ресурсную модель,
	 * а уже ресурсная модель может изменять структуру БД.
	 * @param string $sql
	 * @return void
	 */
	public function run($sql) {$this->getSetup()->run($sql);}

	/**
	 * Метод публичен, потому что объекты данного класса могут передаваться в ресурсную модель,
	 * а уже ресурсная модель может изменять структуру БД.
	 * 2015-02-10
	 * Используйте этот метод в тех ситуациях, когда надо игнорировать исключительные ситуации.
	 * Пример: удаление из базы данных колонку, которая и так может там отсутствовать.
	 * @used-by \Df\C1\Setup::add1CIdColumnToTable()
	 * @param string $sql
	 * @return void
	 */
	public function runSilent($sql) {try {$this->run($sql);} catch (Exception $e) {}}

	/**
	 * @used-by process()
	 * @return void
	 */
	protected function _process() {}

	/**
	 * Мы не можем использовать @see Mage::getResourceSingleton(),
	 * потому что этот метод требует, чтобы второй параметр его конструктора был массивом.
	 * По этой причине мы вынуждены использовать @uses Mage::getResourceModel()
	 * и кэшировать результат вручную.
	 *
	 * В новых версиях Magento CE Mage::getResourceModel('sales/setup')
	 * вернёт объект класса @see Mage_Sales_Model_Resource_Setup,
	 * а в старых версиях — объект класса @see Mage_Sales_Model_Mysql4_Setup.
	 *
	 * 2016-10-16
	 * Упомянутые старые версии отныне не поддерживаем.
	 *
	 * @return SRS
	 */
	protected function getSetupSales() {return dfc($this, function() {return
		df_ar(Mage::getResourceModel('sales/setup', 'sales_setup'), SRS::class)
	;});}

	/**
	 * @used-by pc()
	 * @return void
	 * @throws Exception
	 */
	private function process() {
		/**
		 * Установка перед выполнением следующей процедуры текущим магазином административного
		 * устраняет сбой «Table 'catalog_category_flat' doesn't exist»,
		 * который происходил при выплнении метода
		 * @see Df_Catalog_Setup_2_23_5::processCategories(),
		 * если перед обновлением была включена денормализация,
		 * и сразу после установки  перезагрузить не администативную страницу магазина,
		 * а витринную (а может быть, и без разницы, какую страницу).
		 * Возможно, этот же сбой можно устранить и временным отключением денормализации.
		 * http://magento-forum.ru/topic/4178/
		 */
		df_admin_begin();
		try {
			$this->_process();
			df_cache_clean();
		}
		catch (Exception $e) {
			df_admin_end();
			df_cache_clean();
			df_notify_exception($e);
			df_error($e);
		}
		df_admin_end();
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__SETUP, Df_Core_Model_Resource_Setup::class, false);
	}
	/** @var string */
	private static $P__SETUP = 'setup';

	/** @return Df_Catalog_Model_Resource_Installer_Attribute */
	protected static function attribute() {return Df_Catalog_Model_Resource_Installer_Attribute::s();}

	/**
	 * 2015-02-10
	 * @used-by Df_Catalog_Setup_2_23_5::p()
	 * @used-by Df_Core_Model_Resource_Setup::p()
	 * @param string $class
	 * @param Df_Core_Model_Resource_Setup $setup [optional]
	 * @return void
	 */
	public static function pc($class, Df_Core_Model_Resource_Setup $setup = null) {
		df_ic($class, __CLASS__, array(self::$P__SETUP => $setup))->process();
	}
}