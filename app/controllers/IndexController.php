<?php


class IndexController extends ControllerBase
{
    public function indexAction(string $word = "")
    {
        if ($this->request->isPost() && !!$this->request->getPost('search')) {
            $word = $this->request->getPost('word');
            $this->response->redirect("/$word");
        }
        if ($this->request->isPost() && !!$this->request->getPost('add')) {
            $word = $this->request->getPost('word');
            $description = $this->request->getPost('description');
            $model = new Words();
            $model->setWord($word);
            $model->setDescription($description);
            $model->save();
            $this->response->redirect("/$word");
        }
        if ($word === "") {
            $this->view->pick('index/index');
            return;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "dictionary:8080/$word");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $this->view->word = $word;
        if ($output === strtoupper($word) . " was not found") {
            $saved_word = Words::findFirst(
                [
                    'conditions' => 'word = :word:',
                    'bind' => [
                        'word' => $word,
                    ]
                ]
            );
            if (!$saved_word) {
                $this->view->output = $output;
                $this->view->pick('index/add_word');
                return;
            }
            $output = $saved_word->description;
        }
        $this->view->output = $output;
    }
}

