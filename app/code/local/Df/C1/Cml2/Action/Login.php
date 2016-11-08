<?php
namespace Df\C1\Cml2\Action;
use Df\C1\Cml2\Session\ByCookie\MagentoAPI;
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
			/** @var string $login */
			/** @var string $password */
			list($login, $password) = df_http_credentials();
			/** @var MagentoAPI $s */
			$s = MagentoAPI::s();
			$s->start($sessionName = null);
			/** @var \Mage_Api_Model_User $apiUser */
			try {
				$apiUser = $s->login($login, $password);
			}
			catch (\Exception $e) {
				df_error("Авторизация не удалась: неверно системное имя «{$login}» либо пароль к нему.");
			}
			$apiUser->getIsActive() ?: df_error(
				"Учётная запись «{$login}» зарегистрирована в интернет-магазине, "
				."но отключена там администратором интернет-магазина.");
			$s->isAllowed('rm/_1c') ?: df_error(
				"Учётная запись «{$login}» не обладает полномочиями для обмена данными."
				." Для наделения её этими полномочиями следуйте инструкции"
				." http://magento-forum.ru/topic/2755/"
			);
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
			 *
			 * 2016-09-11
			 * Указанного выше предупреждения не вижу.
			 */
			$this->setResponseLines('success', self::$SESSION_KEY, $s->getSessionId());
		}
		catch (\Exception $e) {
			df_c1()->logRaw(df_ets($e));
			df_notify_exception($e);
			$this->response()->setHeader('HTTP/1.1', '401 Unauthorized');
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
	 * @used-by sessionId()
	 */
	private static $SESSION_KEY = 'df_c1_cml2_sessionId';
}