<?php


class Words extends \Core\Model
{
    public $id;
    public $word;
    public $description;

    public function setWord($word)
    {
        $this->word = $word;

        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $description;
    }

}