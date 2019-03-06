<?php

namespace {
    require_once "vendor/autoload.php";
}

namespace bot {

    use Galantcev\Components\Bot;
    use Galantcev\Components\PhotoShelf;

    /**
     * Class PhotoShelfBot
     * @package bot
     */
    class PhotoShelfBot extends Bot
    {
        /**
         * Робот что-нибудь делает
         */
        public function go()
        {
            $photoShelf = new PhotoShelf($_SERVER['argv'][1]);

            $photoShelf->setOutCallback(function ($str) {
                $this->log->text($str);
            });

            $photoShelf->go();
        }
    }

    $bot = new PhotoShelfBot();

    $bot->log->text('Начинаем работу!');

    $bot->go();

    $bot->log->text('Завершаем работу.');
}
