<?php

namespace Core\Defaults\Controller;

use Core\Controller;
use Core\Exception;
use Core\File;
use Core\HttpException;
use Core\JResponse;
use Core\Model;

abstract class ModelEditor extends Controller
{
	/**
	 * Controller's model class name
	 * @var string
	 */
	protected $_modelClass = null;

	/**
	 * Controller's model instance
	 *
	 * @var Model
	 */
	protected $_modelInstance = null;

	// region MODEL

	/**
	 * Get controller's model class name
	 *
	 * @return string
	 */
	public function modelClass() :string
	{
		if ($this->_modelClass === null) {
			$this->_modelClass = get_class($this);
			$this->_modelClass = substr($this->_modelClass, 0, strlen('Controller') * -1);
		}

		if (!class_exists($this->_modelClass)) {
			throw new Exception('Class ' . $this->_modelClass . ' does not exist');
		}

		return $this->_modelClass;
	}

	/**
	 * Get controller's model instance
	 *
	 * @return Model
	 */
	public function model(): Model
	{
		if ($this->_modelInstance === null) {
			$cls = $this->modelClass();
			$this->_modelInstance = new $cls();
		}

		return $this->_modelInstance;
	}

	// endregion MODEL

	// region LISTING

	/**
	 * List page
	 */
	public function indexAction()
	{
	}

	// endregion LISTING

	// region EDIT

	/**
	 * Show edit object form
	 *
	 * @param int $id
	 */
	public function viewAction(int $id)
	{
		$cls = $this->modelClass();

		$this->view->modelItem = call_user_func([$cls, 'findFirst'], (int)$id);

		if (!$this->view->modelItem) {
			$this->triggerHttpError(404, 'Запись #' . $id . ' не найдена');
			return;
		}
	}

	/**
	 * Receive edit object form
	 */
	public function editAction()
	{
		if ($resp = $this->handleSaveRequest(false)) {
			$this->jSend($resp);
		}
	}

	// endregion EDIT

	// region CREATE

	/**
	 * Add item page
	 */
	public function addAction()
	{
	}

	/**
	 * Receive add item form
	 */
	public function createAction()
	{
		if ($resp = $this->handleSaveRequest(true)) {
			$this->jSend($resp);
		}
	}

	// endregion CREATE

	// region DELETE

	/**
	 * Delete item
	 */
	public function deleteAction()
	{
		if (!$this->request->isPost()) {
			$this->triggerHttpError(405);
			return;
		}

		$class = $this->modelClass();
		$id = (int)$this->request->getPost(call_user_func([$class, 'primary']));

		if (!$id) {
			$this->triggerHttpError(400, 'Не указан ID записи');
			return;
		}

		if (!($model = call_user_func([$this->modelClass(), 'findFirst'], (int)$id))) {
			$this->triggerHttpError(404, 'Запись #' . $id . ' не найдена');
			return;
		}

		$resp = new JResponse();

		try {
			if ($model->delete()) {
				$resp->setSuccess($model, 'Запись удалена');
			} else {
				$message = $model->getMessages();
				$message = (count($message) ? $message[0]->getMessage() : 'Ошибка');
				$resp->setRequestErr($message);
			}
		} catch (HttpException $e) {
			$resp->setErrFromException($e);
		}

		$this->jSend($resp);
	}

	// endregion DELETE

	// region EXPORT

	/**
	 * Export all model's data
	 */
	public function exportAction()
	{
		$cls = $this->modelClass();

		$path = call_user_func(array($cls, 'export'));

		File::download($path);
	}

	// endregion EXPORT

	// region SAVE HANDLER

	/**
	 * Handle save item request (with or without file upload)
	 *
	 * @param bool $isNew
	 * @return JResponse|null
	 */
	public function handleSaveRequest(bool $isNew) :?JResponse
	{
		$this->view->disable();

		if (!$this->request->isPost()) {
			$this->triggerHttpError(405);
			return null;
		}

		try {
			return $this->saveItem(null, $isNew);
		} catch (HttpException $e) {
			$this->triggerHttpErrorByException($e);
			return null;
		}
	}

	/**
	 * Save model item data
	 *
	 * @param array|null $data
	 * @param bool $isNew
	 * @return JResponse|null
	 */
	public function saveItem(array $data = null, bool $isNew = false) :?JResponse
	{
		if (!$this->request->isPost()) {
			$this->triggerHttpError(405);
			return null;
		}

		$class = $this->modelClass();
		$model = new $class();

		if ($data === null) {
			$data = $this->request->getPost();
		}

		$data = $this->prepareInputData($data);

		if ($isNew) {

			if (isset($data['primary'])) {
				unset($data['primary']);
			}

		} else {

			if (!($id = (int)$this->request->getPost($model->primary()))) {
				$this->triggerHttpError(400, 'Не указан ID записи');
				return null;
			}

			if (!($model = call_user_func([$this->modelClass(), 'findFirst'], (int)$id))) {
				$this->triggerHttpError(404, 'Запись #' . $id . ' не найдена');
				return null;
			}
		}

		$resp = new JResponse();
		$data = call_user_func([$this->modelClass(), 'filterInput'], $data);

		try {
			$method = ($isNew ? 'create' : 'update');
			if ($model->$method($data, $model->savable())) {
				$resp->setSuccess($model, 'Данные сохранены');
			} else {
				$message = $model->getMessages();
				$message = (count($message) ? $message[0]->getMessage() : 'Ошибка');
				$resp->setRequestErr($message);
			}
		} catch (HttpException $e) {
			$this->triggerHttpErrorByException($e);
		}

		return $resp;
	}

	/**
	 * Prepare input data array
	 *
	 * @param array $data
	 * @return array
	 */
	public function prepareInputData(array $data) :array
	{
		return call_user_func(
			[$this->modelClass(), 'filterInput'],
			$data
		);
	}

	// endregion SAVE HANDLER
}