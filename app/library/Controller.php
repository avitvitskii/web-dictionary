<?php

namespace Core;

/**
 * Controllers abstraction
 *
 * @package Core
 */
abstract class Controller extends \Phalcon\Mvc\Controller
{
	/**
	 * Send JSON response
	 *
	 * @param JResponse $response
	 * @return Controller
	 */
	public function jSend(JResponse $response) :self
	{
		$this->view->disable();
		$this->response->setJsonContent($response->toArray());
		$this->response->send();

		return $this;
	}

	/**
	 * Trigger HTTP error
	 *
	 * @param int $code
	 * @param string|null $responseText
	 * @param bool $forcePureResponse
	 * @return Controller
	 */
	public function triggerHttpError(int $code, string $responseText = null, bool $forcePureResponse = false) :self
	{
		$this->response->setStatusCode($code);

		$pureResponse = $forcePureResponse || $this->request->isAjax();

		if (!$pureResponse) {
			$this->dispatcher->forward(
				[
					'controller' => 'error',
					'action' => 'index',
					'params' => [
						$code, $responseText
					]
				]
			);
		} else {
			if ($responseText === null) {
				$responseText = Http::responseCodeText($code, false);
			}
			if ($this->request->isAjax()) {
				$resp = new JResponse();
				if ($code >= 500) {
					$resp->setServerErr($responseText);
				} else {
					$resp->setRequestErr($responseText);
				}
				$this->jSend($resp);
			} else {
				$this->response->setContent($code . ' ' . $responseText);
			}
		}

		return $this;
	}

	/**
	 * Trigger HTTP error by HttpException
	 *
	 * @param HttpException $e
	 * @param bool $forcePureResponse
	 * @param bool $fullExceptionOutput
	 * @return Controller
	 */
	public function triggerHttpErrorByException(HttpException $e, bool $forcePureResponse = false, bool $fullExceptionOutput = false) :self
	{
		$code = $e->getCode();

		$this->triggerHttpError(
			$code,
			(!$fullExceptionOutput ? $e->getMessage() : (string)$e),
			$forcePureResponse
		);

		return $this;
	}

	/**
	 * is triggered when invoking inaccessible methods in an object context.
	 *
	 * @param $name string
	 * @param $arguments array
	 * @return void
	 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
	 */
	public function __call($name, $arguments)
	{
		$this->triggerHttpError(404);
	}
}