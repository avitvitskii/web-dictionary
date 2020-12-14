<?php


class IndexController extends ControllerBase
{
    public function indexAction(string $word = "")
    {
        if ($word === "") {
            return;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "dictionary:8080/$word");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output === strtoupper($word) . " was not found") {
            $model = new Words();
            $saved_word = Words::findFirst(
                [
                    'conditions' => 'word = :word:',
                    'bind' => [
                        'word' => $word,
                    ]
                ]
            );
            if (!$saved_word) {
                $model->setWord($word);
                $model->setDescription("testtttt");
                $model->save();
            } else {
                $output = $saved_word->description;
            }
        }
        $this->view->output = $output;
        $this->view->word = $word;
    }
}

