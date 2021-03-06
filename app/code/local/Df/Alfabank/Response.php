<?php
namespace Df\Alfabank;
abstract class Response extends \Df\Payment\Response {
	/** @return string */
	abstract protected function getKey_ErrorCode();

	/** @return string */
	abstract protected function getKey_ErrorMessage();

	/** @return int */
	public function getErrorCode() {return df_int($this->cfg($this->getKey_ErrorCode()));}

	/** @return string */
	public function getErrorCodeMeaning() {return
		dfa($this->getErrorCodeMap(), $this->getErrorCode(), 'Неизвестно')
	;}

	/**
	 * @override
	 * @return string
	 */
	protected function getErrorMessage() {return strval($this->cfg($this->getKey_ErrorMessage()));}

	/**
	 * @override
	 * @return bool
	 */
	protected function isSuccessful() {return 0 === $this->getErrorCode();}

	/** @return array(int => string) */
	protected function getErrorCodeMap() {return [
		0 => 'Обработка запроса прошла без системных ошибок'
		,5 => 'Ошибка значения параметра запроса'
		,6 => 'Незарегистрированный OrderId'
		,7 => 'Системная ошибка'
	];}
}