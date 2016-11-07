<?php
namespace Df\C1\Cml2;
class Cookie {
	/** @return string|null */
	public function getSessionId() {return df_cookie(self::SESSION_ID);}

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
	 * @used-by getSessionId()
	 * @used-by \Df\C1\Cml2\Action\Login::_process()
	 */
	const SESSION_ID = 'df_c1_cml2_sessionId';

	/** @return self */
	public static function s() {static $r; return $r ? $r : $r = new self;}
}