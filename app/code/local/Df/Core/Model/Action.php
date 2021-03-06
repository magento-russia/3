<?php
use Mage_Core_Controller_Request_Http as Request;
abstract class Df_Core_Model_Action extends Df_Core_Model {
	/**
	 * @used-by process()
	 * @return void
	 */
	protected function _process() {$this->response()->setBody($this->responseBody());}

	/**
	 * @used-by processPrepare()
	 * @return void
	 */
	protected function checkAccessRights() {
		if (!$this->isModuleEnabledByAdmin()) {
			df_error(strtr(
				'Модуль «{название модуля}» отключен в административной части магазина.'
				, array('{название модуля}' => $this->moduleTitle())
			));
		}
	}

	/**
	 * @used-by getResponseLogFileExtension()
	 * @used-by processPrepare()
	 * @return string
	 */
	protected function contentType() {return 'text/plain; charset=UTF-8';}

	/**
	 * 2015-03-13
	 * Переадресовывает обработку запроса другому (дочернему) объекту.
	 * Это позволяет разбивать обработку сложных запросов на несколько классов-обработчиков.
	 * Широко используется модулем «1C:Управление торговлей».
	 * В качестве $class можно передавать как полный класс дочернего обработчика,
	 * так и суффикс класса.
	 * Например, если в качестве $class передан суффикс «Catalog\Deactivate»,
	 * а класс $this —  «Df\1C\Cml2\Action\Front»,
	 * то класс дочернего обработчика будет «Df\1C\Cml2\Action\Catalog\Deactivate»
	 * @param string $c
	 * @return void
	 */
	protected function delegate($c) {
		self::pc(df_class_my($c) ? $c : df_class_replace_last($this, $c), $this->controller());
	}

	/**
	 * @used-by responseBody()
	 * @return string
	 */
	protected function generateResponseBody() {return '';}

	/**
	 * @used-by responseBody()
	 * @return string
	 */
	protected function generateResponseBodyFake() {return '';}

	/**
	 * @used-by checkAccessRights()
	 * @return bool
	 */
	protected function isModuleEnabledByAdmin() {return true;}

	/**
	 * @used-by processException()
	 * @return bool
	 */
	protected function needAddExceptionToSession() {return !$this->needRethrowException();}

	/**
	 * @used-by benchmarkLog()
	 * @used-by benchmarkStart()
	 * @return bool
	 */
	protected function needBenchmark() {return false;}

	/**
	 * @used-by responseBody()
	 * @return bool
	 */
	protected function needGenerateFakeResponse() {return false;}

	/**
	 * @used-by process()
	 * @return bool
	 */
	protected function needLogRR() {return df_my();}

	/**
	 * @used-by needAddExceptionToSession()
	 * @used-by processException()
	 * @return bool
	 */
	protected function needRethrowException() {return true;}

	/**
	 * 2016-10-20
	 * @param string $key
	 * @param string|null $d [optional]
	 * @return string|null
	 */
	protected function param($key, $d = null) {return $this->request()->getParam($key, $d);}

	/**
	 * 2016-10-20
	 * @return array(string => string)
	 */
	protected function params() {return $this->request()->getParams();}

	/**
	 * 2016-10-21
	 * @used-by request()
	 * @return array(string => string)
	 */
	protected function paramsCustom() {return [];}

	/**
	 * 2016-10-21
	 * @used-by request()
	 * @return array(string => string)
	 */
	protected function paramsLocal() {return dfc($this, function() {
		/** @var array(string => string) $result */
		$result = [];
		if (df_my_local()) {
			/** @var string $basename */
			$basename = df_class_last($this);
			/** @var string $module */
			$module = df_module_name_short($this);
			/** @var string $file */
			$file = BP . "/_my/test/{$module}/{$basename}.json";
			if (file_exists($file)) {
				$result = df_json_decode(file_get_contents($file));
			}
		}
		return $result;
	});}

	/**
	 * @used-by process()
	 * @return void
	 */
	protected function processBeforeRedirect() {}

	/**
	 * @used-by process()
	 * @param Exception $e
	 * @return void
	 */
	protected function processException(Exception $e) {
		/**
		 * Используем @, чтобы избежать сбоя «Failed to delete buffer zlib output compression».
		 * Такой сбой у меня возник на сервере moysklad.magento-demo.ru
		 * в другой точке программы при аналогичном вызове @uses ob_get_clean().
		 */
		/** @var string|bool $output */
		/** @noinspection PhpUsageOfSilenceOperatorInspection */
		$output = @ob_get_clean();
		if ($output) {
			Mage::log(df_sprintf("output buffer:\n«%s»", $output));
		}
		if ($this->needAddExceptionToSession()) {
			df_exception_to_session($e);
		}
		df_handle_entry_point_exception($e, $this->needRethrowException());
	}

	/**
	 * @used-by process()
	 * @return void
	 */
	protected function processFinish() {
		$this->benchmarkLog();
		/**
		 * Используем @, чтобы избежать сбоя «Failed to delete buffer zlib output compression».
		 * Такой сбой у меня возник на сервере moysklad.magento-demo.ru
		 * в другой точке программы при аналогичном вызове @uses ob_get_clean().
		 */
		/** @var string|bool $output */
		/** @noinspection PhpUsageOfSilenceOperatorInspection */
		$output = @ob_get_clean();
		if ($output) {
			ob_start();
			df_error($output);
		}
	}

	/**
	 * @used-by process()
	 * @return void
	 */
	protected function processPrepare() {
		ob_start();
		/**
		 * Обратите внимание, что в программном коде, к сожалению, нельзя
		 * дополнительно отменить ограничение на max_input_time
		 * http://www.php.net/manual/en/info.configuration.php
		 */
		set_time_limit(0);
		ini_set('memory_limit', -1);
		df_response_content_type($this->response(), $this->contentType());
		$this->benchmarkStart();
		$this->checkAccessRights();
	}

	/**
	 * @used-by process()
	 * @return void
	 */
	protected function processRedirect() {
		if ($this->redirectLocation()) {
			self::$REDIRECT_LOCATION__REFERER === $this->redirectLocation()
			? $this->redirectRaw(df_referer())
			: $this->redirect($this->redirectLocation());
		}
	}

	/**
	 * @used-by processRedirect()
	 * @return string
	 */
	protected function redirectLocation() {return '';}

	/**
	 * @used-by rmRequest()
	 * @used-by \Df\Alfabank\Action\CustomerReturn::getRequest()
	 * @return Request
	 */
	protected function request() {return dfc($this, function() {
		/** @var  $result */
		$result = $this->controller()->getRequest();
		/**
		 * 2016-10-21
		 * 1) @see Request::clearParams() намеренно не вызываем.
		 * 2) paramsLocal() сильнее и перетирает значения @see paramsCustom.
		 */
		$result->setParams($this->paramsLocal() + $this->paramsCustom());
		return $result;
	});}

	/**
	 * @used-by _process()
	 * @used-by processPrepare()
	 * @used-by redirectRaw()
	 * @return Mage_Core_Controller_Response_Http
	 */
	protected function response() {return $this->controller()->getResponse();}

	/**
	 * @used-by _process()
	 * @used-by \Df\IPay\Action::processBeforeRedirect()
	 * @param bool $real [optional]
	 * @return string
	 */
	protected function responseBody($real = null) {
		if (is_null($real)) {
			$real = !$this->needGenerateFakeResponse();
		}
		if (!isset($this->{__METHOD__}[$real])) {
			$this->{__METHOD__}[$real] =
				$real ? $this->generateResponseBody() : $this->generateResponseBodyFake()
			;
			df_result_string($this->{__METHOD__}[$real]);
		}
		return $this->{__METHOD__}[$real];
	}

	/**
	 * @used-by \Df\C1\Cml2\Action\Front::_process()
	 * @used-by \Df\YandexMarket\Action\Category_Suggest::getQuery()
	 * @return Df_Core_Model_InputRequest
	 */
	protected function rmRequest() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = Df_Core_Model_InputRequest::ic($this->rmRequestClass(), $this->request());
		}
		return $this->{__METHOD__};
	}

	/**
	 * @used-by rmRequest()
	 * @uses Df_Core_Model_InputRequest
	 * @return string
	 */
	protected function rmRequestClass() {return Df_Core_Model_InputRequest::class;}

	/**
	 * @used-by \Df\C1\Cml2\Action::getLastProcessedTimeAsString()
	 * @param string $key
	 * @return mixed
	 */
	protected function storeConfig($key) {return $this->store()->getConfig($key);}

	/**
	 * @used-by processRedirect()
	 * @used-by Df_Core_Model_Action_Admin_Entity_Save::processRedirect()
	 * @param string $path
	 * @param array(string => mixed) $arguments [optional]
	 * @return void
	 */
	protected function redirect($path, array $arguments = []) {
		$this->controller()->setRedirectWithCookieCheck($path, $arguments);
	}

	/**
	 * @used-by processRedirect()
	 * @used-by \Df\Payment\Action\Confirm::redirectToSuccess()
	 * @param string $path
	 * @return void
	 */
	protected function redirectRaw($path) {$this->response()->setRedirect($path);}

	/**
	 * @used-by checkAccessRights()
	 * @used-by getStoreConfig()
	 * @used-by Df_Admin_Action_DeleteDemoStore::_process()
	 * @return Df_Core_Model_StoreM
	 */
	protected function store() {return df_store();}

	/**
	 * @used-by \Df\C1\Cml2\Action\Orders\Export::getOrders()
	 * @return int
	 */
	protected function storeId() {return $this->store()->getId();}

	/**
	 * @used-by processFinish()
	 * @return void
	 */
	private function benchmarkLog() {
		if ($this->needBenchmark()) {
			/** @var float $timeSpent */
			$timeSpent = microtime($get_as_float = true) - $this->_benchmarkTimeStart;
			Mage::log(df_sprintf('%s: %.3fs', get_class($this), $timeSpent));
		}
	}

	/**
	 * @used-by processPrepare()
	 * @return void
	 */
	private function benchmarkStart() {
		/** @var float $timeStart */
		if ($this->needBenchmark()) {
			$this->_benchmarkTimeStart = microtime($get_as_float = true);
		}
	}

	/**
	 * @used-by getRequest()
	 * @used-by getResponse()
	 * @used-by rmRequest()
	 * @used-by redirect()
	 * @return Mage_Core_Controller_Varien_Action
	 */
	private function controller() {return $this[self::$P__CONTROLLER];}

	/**
	 * @used-by pc()
	 * @return void
	 */
	private function process() {
		/** @var string $logName */
		if ($this->needLogRR()) {
			$this->rrLog(df_json_encode_pretty($this->params()), 'json', 'request');
		}
		try {
			$this->processPrepare();
			$this->_process();
			$this->processFinish();
		}
		catch (Exception $e) {
			$this->processException($e);
		}
		$this->processBeforeRedirect();
		if ($this->needLogRR()) {
			$this->rrLog(
				$this->response()->getBody()
				,df_contains($this->contentType(), 'xml') ? 'xml' : (
					df_contains($this->contentType(), 'json') ? 'json' : 'txt'
				)
				,'response'
			);
		}
		$this->processRedirect();
	}

	/**
	 * @used-by process()
	 * @param string $data
	 * @param string $ext
	 * @param string $suffix
	 * @return string
	 */
	private function rrLog($data, $ext, $suffix) {return df_report(
		df_ccc('-', ['{date}-{time-full-ms}', df_cts_lc_camel($this, '.'), $suffix]) . ".{$ext}"
		,$data, 'action', '.'
	);}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__CONTROLLER, Mage_Core_Controller_Varien_Action::class);
	}

	/**
	 * @used-by \Df\C1\Cml2\Action\GenericExport::contentType()
	 * @used-by \Df\YandexMarket\Action\Front::contentType()
	 * @var string
	 */
	protected static $CONTENT_TYPE__XML__UTF_8 = 'application/xml; charset=utf-8';
	/**
	 * @used-by Df_Core_Model_Action_Admin::_construct()
	 * @var string
	 */
	protected static $P__CONTROLLER = 'controller';
	/** @var string */
	protected static $REDIRECT_LOCATION__REFERER = 'referer';

	/** @var float */
	private $_benchmarkTimeStart;

	/**
	 * @used-by delegate()
	 * @used-by df_action()
	 * @param string $class
	 * @param Mage_Core_Controller_Varien_Action $c
	 * @return void
	 */
	public static function pc($class, Mage_Core_Controller_Varien_Action $c) {
		df_ic($class, __CLASS__, [self::$P__CONTROLLER => $c])->process();
	}
}