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
				, \Df\C1\Cml2\Cookie::SESSION_ID
				, $this->sessionMagentoAPI()->getSessionId()
				, ''
			);
		}
		catch (\Exception $e) {
			df_c1()->logRaw(df_ets($e));
			df_notify_exception($e);
			$this->response()->setHeader($name = 'HTTP/1.1', $value = '401 Unauthorized');
			throw $e;
		}
	}
}