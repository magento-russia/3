<?php
namespace Df\C1\Cml2\Action;
class Login extends \Df\C1\Cml2\Action {
	/**
	 * @override
	 * @see Df_Core_Model_Action::_process()
	 * @used-by Df_Core_Model_Action::process()
	 * @return void
	 * @throws \Exception
	 */
	protected function _process() {
		try {
			/** @var string $userName */
			/** @var string $password */
			list($userName, $password) = df_mage()->core()->httpHelper()->authValidate();
			if (!$userName) {
				df_error('Администратор пытается авторизоваться с пустым системным именем, что недопустимо.');
			}
			if (!$password) {
				df_error(
					"Администратор «{$userName}» пытается авторизоваться с пустым паролем, что недопустимо."
				);
			}
			$this->sessionMagentoAPI()->start($sessionName = null);
			/** @var \Mage_Api_Model_User $apiUser */
			$apiUser = null;
			try {
				$apiUser = $this->sessionMagentoAPI()->login($userName, $password);
			}
			catch (\Exception $e) {
				df_error('Авторизация не удалась: неверно системное имя «%s», либо пароль к нему.', $userName);
			}
			if (!df_bool($apiUser->getIsActive())) {
				df_error('Администратор «%s» не допущен к работе', $userName);
			}
			if (!$this->sessionMagentoAPI()->isAllowed('rm/_1c')) {
				df_error(
					"Администратор «%s»
					не допущен к обмену данными между Magento и 1С:Управление торговлей.
					\nДля допуска администратора к данной работе
					наделите администратора должностью, которая обладает полномочием
					«Российская сборка» → «1С:Управление торговлей»."
					,$userName
				);
			}
			/**
			 * 2016-11-07
			 * Модуль Битрикс ветки 6.x поддерживает ещё 2 опциональных параметра в ответе:
			 * «в 4-ой строке содержится ключ сессии обмена (CSRF);
			 * в 5-ой строке содержится дата и время сервера сайта (CSRF).»
			 * http://dev.1c-bitrix.ru/api_help/sale/algorithms/data_2_site.php
			 *
			 * Судя по исходному коду модуля, эти параметры не являются обязательными,
			 * но при их отсутствии модуль будет выдавать предупреждение:
					Попытка
						Токен = "&" + СтрПолучитьСтроку(ОтветСервера, 4);
					Исключение
						Токен = "";
						СообщитьПодробно("CSRF токен не поддерживается. Для того, чтобы поддерживался - необходимо обновить БУС.", ПараметрыОбмена);
						СообщитьПодробно("Ответ сервера: " + ОтветСервера, ПараметрыОбмена);
					КонецПопытки;
			 */
			$this->setResponseLines(
				'success'
				,self::$SESSION_KEY
				,$this->sessionMagentoAPI()->getSessionId()
				,''
			);
		}
		catch (\Exception $e) {
			df_c1()->logRaw(df_ets($e));
			df_notify_exception($e);
			$this->response()->setHeader($name = 'HTTP/1.1', $value = '401 Unauthorized');
			throw $e;
		}
	}

	/**
	 * 2016-11-07
	 * @used-by \Df\C1\Cml2\Session\ByCookie\C1::getSessionIdCustom()
	 * @used-by \Df\C1\Cml2\Action\Front::checkLoggedIn()
	 * @return string|null
	 */
	public static function sessionId() {return df_cookie(self::$SESSION_KEY);}

	/**
	 * Имя (идентификатор) cookie, которая содержит идентификатор сессии.
	 * Это имя, а также идентификатор сессии, модуль передаёт в 1С на запрос «mode=checkauth»:
	 * A. Начало сеанса
	 * Выгрузка каталога начинается с того, что система "1С:Предприятие"
	 * отправляет http-запрос следующего вида:
	 * 		http://<сайт>/<путь> /1c_exchange.php?type=catalog&mode=checkauth.
	 * В ответ система управления сайтом передает системе «1С:Предприятие»
	 * три строки (используется разделитель строк "\n"):
		 слово "success";
		 имя Cookie;
		 значение Cookie.
	 * Примечание. Все последующие запросы к системе управления сайтом со стороны "1С:Предприятия"
	 * содержат в заголовке запроса имя и значение Cookie.
	 * Так вот «имя Cookie» — это как раз наш SESSION_ID,
	 * а «значение Cookie» — это идентификатор сессии
	 * (на запрос «mode=checkauth» создаётся модулем PHP session автоматически).
	 *
	 * Обратите внимание, что в имени сессии нельзя использовать символ-точку («.»).
	 *
	 * @used-by _process()
	 * @used-by sessionId()*
	 */
	private static $SESSION_KEY = 'df_c1_cml2_sessionId';
}